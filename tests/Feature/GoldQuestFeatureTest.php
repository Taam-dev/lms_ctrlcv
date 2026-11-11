<?php

namespace Tests\Feature;

use App\Models\MinigameQuestion;
use App\Models\MinigameQuiz;
use App\Models\User;
use App\Services\GoldQuest\GoldQuestEngine;
use App\Services\GoldQuest\GoldQuestService;
use App\Services\GoldQuest\LootTableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GoldQuestFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected MinigameQuiz $quiz;

    protected MinigameQuestion $question;

    protected GoldQuestService $service;

    protected GoldQuestEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create(['role' => 'teacher']);
        $this->quiz = MinigameQuiz::create([
            'user_id' => $this->host->id,
            'title' => 'Đại Chiến Đố Vui Vàng',
            'difficulty' => 'medium',
            'is_public' => true,
        ]);

        $this->question = MinigameQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question' => 'Thủ đô của Việt Nam là gì?',
            'options' => [
                'A' => 'Hà Nội',
                'B' => 'Hồ Chí Minh',
                'C' => 'Đà Nẵng',
                'D' => 'Huế',
            ],
            'correct_answer' => 'A',
            'time_limit' => 20,
        ]);

        $this->engine = app(GoldQuestEngine::class);
        $this->service = app(GoldQuestService::class);
    }

    public function test_can_generate_unique_pin_and_create_room(): void
    {
        $room = $this->service->createRoom(
            hostId: $this->host->id,
            quizId: $this->quiz->id,
            durationMinutes: 5,
            settings: ['allow_steal' => true, 'allow_swap' => true]
        );

        $this->assertDatabaseHas('minigame_rooms', [
            'id' => $room->id,
            'pin_code' => $room->pin_code,
            'status' => 'waiting',
        ]);
        $this->assertEquals(6, strlen($room->pin_code));
        $this->assertTrue($room->settings['allow_steal']);
    }

    public function test_players_can_join_lobby_with_blooks(): void
    {
        $room = $this->service->createRoom(
            hostId: $this->host->id,
            quizId: $this->quiz->id
        );

        $p1 = $this->service->joinRoom($room->pin_code, 'DragonMaster', 'dragon');
        $p2 = $this->service->joinRoom($room->pin_code, 'WizardPro', 'wizard');

        $this->assertNotEmpty($p1['player_id']);
        $this->assertNotEmpty($p2['player_id']);
        $this->assertEquals(0, $this->engine->getGold($room->pin_code, $p1['player_id']));
        $this->assertEquals(0, $this->engine->getGold($room->pin_code, $p2['player_id']));
    }

    public function test_host_can_start_game(): void
    {
        $room = $this->service->createRoom(
            hostId: $this->host->id,
            quizId: $this->quiz->id
        );

        $started = $this->service->startGame($room->pin_code, $this->host->id);
        $this->assertTrue($started);

        $room->refresh();
        $this->assertEquals('playing', $room->status);
        $this->assertNotNull($room->started_at);
    }

    public function test_answer_validation_grants_chest_token_only_when_correct(): void
    {
        $room = $this->service->createRoom(
            hostId: $this->host->id,
            quizId: $this->quiz->id
        );
        $this->service->startGame($room->pin_code, $this->host->id);
        $player = $this->service->joinRoom($room->pin_code, 'Ninja', 'ninja');

        // Trả lời sai
        $wrongResult = $this->service->submitAnswer(
            $room->pin_code,
            $player['player_id'],
            $this->question->id,
            'B'
        );
        $this->assertFalse($wrongResult['is_correct']);
        $this->assertFalse($wrongResult['can_open_chest']);
        $this->assertEquals('A', $wrongResult['correct_answer']);

        // Trả lời đúng
        $correctResult = $this->service->submitAnswer(
            $room->pin_code,
            $player['player_id'],
            $this->question->id,
            'A'
        );
        $this->assertTrue($correctResult['is_correct']);
        $this->assertTrue($correctResult['can_open_chest']);
        $this->assertNotEmpty($correctResult['chest_token']);
    }

    public function test_single_use_chest_token_cannot_be_replayed(): void
    {
        $room = $this->service->createRoom(
            hostId: $this->host->id,
            quizId: $this->quiz->id
        );
        $this->service->startGame($room->pin_code, $this->host->id);
        $player = $this->service->joinRoom($room->pin_code, 'Unicorn', 'unicorn');

        $ans = $this->service->submitAnswer($room->pin_code, $player['player_id'], $this->question->id, 'A');
        $token = $ans['chest_token'];

        // Mở lần 1: thành công
        $lootResult = $this->service->openChest($room->pin_code, $player['player_id'], $token);
        $this->assertArrayHasKey('outcome', $lootResult);

        // Mở lần 2 với cùng token: phải bị từ chối 403
        $this->expectException(HttpException::class);
        $this->service->openChest($room->pin_code, $player['player_id'], $token);
    }

    public function test_loot_table_weights_distribution(): void
    {
        $loot = LootTableService::roll(allowSteal: true, allowSwap: true);
        $this->assertArrayHasKey('type', $loot);
        $this->assertArrayHasKey('label', $loot);
        $this->assertArrayHasKey('weight', $loot);
    }

    public function test_steal_and_swap_mechanics_on_engine(): void
    {
        $pin = '999888';
        $thiefId = 'thief-1';
        $victimId = 'victim-1';

        $this->engine->registerPlayer($pin, $thiefId, ['nickname' => 'RobinHood', 'blook' => 'fox']);
        $this->engine->registerPlayer($pin, $victimId, ['nickname' => 'Sheriff', 'blook' => 'bear']);

        // Cho victim 1000 vàng
        $this->engine->adjustGold($pin, $victimId, 1000);
        $this->assertEquals(1000, $this->engine->getGold($pin, $victimId));
        $this->assertEquals(0, $this->engine->getGold($pin, $thiefId));

        // Test CƯỚP (Steal 20%)
        $stealRes = $this->engine->executeSteal($pin, $thiefId, $victimId, 0.20);
        $this->assertEquals(200, $stealRes['stolen_amount']);
        $this->assertEquals(200, $this->engine->getGold($pin, $thiefId));
        $this->assertEquals(800, $this->engine->getGold($pin, $victimId));

        // Test HOÁN ĐỔI (Swap)
        $swapRes = $this->engine->executeSwap($pin, $thiefId, $victimId);
        $this->assertEquals(800, $swapRes['my_new_gold']);
        $this->assertEquals(200, $swapRes['victim_new_gold']);
        $this->assertEquals(800, $this->engine->getGold($pin, $thiefId));
        $this->assertEquals(200, $this->engine->getGold($pin, $victimId));
    }

    public function test_finalize_and_sync_from_redis_to_database(): void
    {
        $room = $this->service->createRoom(
            hostId: $this->host->id,
            quizId: $this->quiz->id
        );
        $this->service->startGame($room->pin_code, $this->host->id);

        $p1 = $this->service->joinRoom($room->pin_code, 'Winner', 'dragon');
        $p2 = $this->service->joinRoom($room->pin_code, 'RunnerUp', 'wizard');

        $this->engine->adjustGold($room->pin_code, $p1['player_id'], 1500);
        $this->engine->adjustGold($room->pin_code, $p2['player_id'], 750);

        $podium = $this->service->finalizeAndEndGame($room->pin_code);

        $this->assertCount(2, $podium);
        $this->assertEquals('Winner', $podium[0]['nickname']);
        $this->assertEquals(1500, $podium[0]['gold']);
        $this->assertEquals(1, $podium[0]['rank']);

        $this->assertEquals('RunnerUp', $podium[1]['nickname']);
        $this->assertEquals(750, $podium[1]['gold']);
        $this->assertEquals(2, $podium[1]['rank']);

        // Xác nhận đã lưu bền vững vào bảng MySQL minigame_participants
        $this->assertDatabaseHas('minigame_participants', [
            'room_id' => $room->id,
            'nickname' => 'Winner',
            'gold' => 1500,
            'final_rank' => 1,
        ]);
    }
}
