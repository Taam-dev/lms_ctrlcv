<?php

namespace App\Services\GoldQuest;

use App\Events\GoldQuest\GameEnded;
use App\Events\GoldQuest\GameStarted;
use App\Events\GoldQuest\RoomJoined;
use App\Models\MinigameActivityLog;
use App\Models\MinigameParticipant;
use App\Models\MinigameQuestion;
use App\Models\MinigameRoom;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoldQuestService
{
    public function __construct(
        protected GoldQuestEngine $engine
    ) {}

    public function engine(): GoldQuestEngine
    {
        return $this->engine;
    }

    /**
     * Sinh mã PIN 6 số ngẫu nhiên duy nhất trong các phòng đang active
     */
    public function generateUniquePin(): string
    {
        do {
            $pin = (string) random_int(100000, 999999);
            $exists = MinigameRoom::where('pin_code', $pin)
                ->whereIn('status', ['waiting', 'playing'])
                ->exists();
        } while ($exists);

        return $pin;
    }

    /**
     * Host tạo phòng chơi Gold Quest
     */
    public function createRoom(
        int $hostId,
        int $quizId,
        int $durationMinutes = 6,
        array $settings = []
    ): MinigameRoom {
        $pin = $this->generateUniquePin();

        $defaultSettings = [
            'allow_steal' => true,
            'allow_swap' => true,
            'min_steal_percent' => 0.10,
            'max_steal_percent' => 0.30,
        ];

        return MinigameRoom::create([
            'host_id' => $hostId,
            'quiz_id' => $quizId,
            'pin_code' => $pin,
            'game_type' => 'gold_quest',
            'status' => 'waiting',
            'duration_minutes' => max(1, min(60, $durationMinutes)),
            'settings' => array_merge($defaultSettings, $settings),
        ]);
    }

    /**
     * Học viên gia nhập sảnh chờ (Lobby Join)
     *
     * @return array{player_id: string, participant_id: int, room_pin: string, nickname: string, blook: string}
     */
    public function joinRoom(string $pin, string $nickname, string $blook = 'dragon', ?int $userId = null): array
    {
        $room = MinigameRoom::where('pin_code', $pin)
            ->whereIn('status', ['waiting', 'playing'])
            ->firstOrFail();
        $playerId = (string) Str::uuid();

        // Đảm bảo nickname không trùng lặp trong phòng
        $existingCount = MinigameParticipant::where('room_id', $room->id)->where('nickname', $nickname)->count();
        if ($existingCount > 0) {
            $nickname .= ' ('.($existingCount + 1).')';
        }

        // Tạo bản ghi DB (để giữ quan hệ và audit)
        $participant = MinigameParticipant::create([
            'room_id' => $room->id,
            'user_id' => $userId,
            'session_id' => $playerId,
            'nickname' => $nickname,
            'blook' => $blook,
            'gold' => 0,
            'last_active_at' => Carbon::now(),
        ]);

        // Đăng ký tức thời vào Redis Sorted Set và Player Metadata
        $this->engine->registerPlayer($pin, $playerId, [
            'player_id' => $playerId,
            'participant_id' => (string) $participant->id,
            'user_id' => (string) ($userId ?? ''),
            'nickname' => $nickname,
            'blook' => $blook,
            'correct' => 0,
            'wrong' => 0,
            'chests' => 0,
        ]);

        // Broadcast event thời gian thực cho Host & các học viên khác
        broadcast(new RoomJoined($pin, [
            'player_id' => $playerId,
            'participant_id' => $participant->id,
            'nickname' => $nickname,
            'blook' => $blook,
        ]));

        return [
            'player_id' => $playerId,
            'participant_id' => $participant->id,
            'room_pin' => $pin,
            'nickname' => $nickname,
            'blook' => $blook,
        ];
    }

    /**
     * Host bắt đầu trận đấu
     */
    public function startGame(string $pin, int $hostId): bool
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        if ($room->host_id !== $hostId) {
            abort(403, 'Chỉ chủ phòng mới có thể bắt đầu trận đấu.');
        }

        if ($room->status !== 'waiting') {
            return false;
        }

        $room->update([
            'status' => 'playing',
            'started_at' => Carbon::now(),
        ]);

        $durationSeconds = $room->duration_minutes * 60;
        broadcast(new GameStarted($pin, $durationSeconds));

        return true;
    }

    /**
     * Xác thực câu trả lời trắc nghiệm (Server-side Authority)
     *
     * @return array{is_correct: bool, can_open_chest: bool, chest_token?: string, freeze_seconds?: int, correct_answer?: string, correct_text?: string}
     */
    public function submitAnswer(string $pin, string $playerId, int $questionId, string $selectedOption): array
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        if ($room->status !== 'playing') {
            abort(400, 'Trận đấu chưa bắt đầu hoặc đã kết thúc.');
        }

        $question = MinigameQuestion::where('quiz_id', $room->quiz_id)->findOrFail($questionId);
        $isCorrect = $question->isCorrect($selectedOption);

        if ($isCorrect) {
            $this->engine->incrementPlayerStat($pin, $playerId, 'correct', 1);

            // Cấp Single-Use Chest Grant Token
            $token = Str::random(32);
            $this->engine->issueChestGrant($pin, $playerId, $token, 45);

            return [
                'is_correct' => true,
                'can_open_chest' => true,
                'chest_token' => $token,
            ];
        }

        // Trả lời sai: Phạt 2 giây và gửi đáp án đúng để học viên tiếp thu
        $this->engine->incrementPlayerStat($pin, $playerId, 'wrong', 1);

        $options = $question->options ?? [];
        $correctKey = $question->correct_answer;
        $correctText = $options[$correctKey] ?? $correctKey;

        return [
            'is_correct' => false,
            'can_open_chest' => false,
            'freeze_seconds' => 2,
            'correct_answer' => $correctKey,
            'correct_text' => $correctText,
        ];
    }

    /**
     * Mở rương vàng (Server tính toán Loot Table có trọng số)
     *
     * @return array{outcome: string, loot: array, delta_gold?: int, total_gold?: int, action_type?: string, targets?: array, expires_in?: int}
     */
    public function openChest(string $pin, string $playerId, string $token): array
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        // 1. Xác thực và tiêu thụ Token chống gian lận
        if (! $this->engine->consumeChestGrant($pin, $playerId, $token)) {
            abort(403, 'Yêu cầu mở rương không hợp lệ hoặc token đã hết hạn!');
        }

        $this->engine->incrementPlayerStat($pin, $playerId, 'chests', 1);

        $allowSteal = $room->settings['allow_steal'] ?? true;
        $allowSwap = $room->settings['allow_swap'] ?? true;

        $loot = LootTableService::roll($allowSteal, $allowSwap);
        $currentGold = $this->engine->getGold($pin, $playerId);
        $playerInfo = $this->engine->getPlayerInfo($pin, $playerId);
        $nickname = $playerInfo['nickname'] ?? 'Player';

        switch ($loot['type']) {
            case 'GOLD_FLAT':
                $delta = (int) $loot['value'];
                $newGold = $this->engine->adjustGold($pin, $playerId, $delta);

                $this->logActivity($room->id, $playerInfo['participant_id'] ?? null, 'gold', "{$nickname} mở rương nhận được +{$delta} vàng!");

                return [
                    'outcome' => 'SUCCESS',
                    'loot' => $loot,
                    'delta_gold' => $delta,
                    'total_gold' => $newGold,
                ];

            case 'PERCENT_ADD':
                $delta = (int) round($currentGold * (float) $loot['value']);
                if ($delta < 10) {
                    $delta = 25; // Thưởng tối thiểu nếu vàng đang thấp
                }
                $newGold = $this->engine->adjustGold($pin, $playerId, $delta);

                $this->logActivity($room->id, $playerInfo['participant_id'] ?? null, 'multiplier', "{$nickname} mở rương tăng +{$loot['label']} (+{$delta} vàng)!");

                return [
                    'outcome' => 'SUCCESS',
                    'loot' => $loot,
                    'delta_gold' => $delta,
                    'total_gold' => $newGold,
                ];

            case 'LOSE_PERCENT':
                $delta = (int) round($currentGold * (float) $loot['value']);
                $newGold = $this->engine->adjustGold($pin, $playerId, $delta);

                $this->logActivity($room->id, $playerInfo['participant_id'] ?? null, 'trap', "⚠️ {$nickname} dẫm phải bẫy gai và mất {$delta} vàng!");

                return [
                    'outcome' => 'LOSE',
                    'loot' => $loot,
                    'delta_gold' => $delta,
                    'total_gold' => $newGold,
                ];

            case 'STEAL':
            case 'SWAP':
                $this->engine->setPendingAction($pin, $playerId, $loot['type'], 15);
                $targets = $this->getAvailableTargets($pin, $playerId);

                return [
                    'outcome' => 'CHOOSE_TARGET',
                    'action_type' => $loot['type'],
                    'loot' => $loot,
                    'targets' => $targets,
                    'expires_in' => 15,
                ];

            case 'NOTHING':
            default:
                $this->logActivity($room->id, $playerInfo['participant_id'] ?? null, 'nothing', "💨 {$nickname} mở phải rương rỗng!");

                return [
                    'outcome' => 'NOTHING',
                    'loot' => $loot,
                    'delta_gold' => 0,
                    'total_gold' => $currentGold,
                ];
        }
    }

    /**
     * Lấy danh sách đối thủ khả dụng để Cướp / Tráo
     *
     * @return array<int, array{player_id: string, nickname: string, blook: string, gold: int}>
     */
    public function getAvailableTargets(string $pin, string $currentPlayerId): array
    {
        $all = $this->engine->getAllScores($pin);
        $targets = [];

        foreach ($all as $pid => $gold) {
            if ((string) $pid === (string) $currentPlayerId) {
                continue;
            }

            $info = $this->engine->getPlayerInfo($pin, (string) $pid);
            $targets[] = [
                'player_id' => (string) $pid,
                'nickname' => $info['nickname'] ?? 'Đối thủ',
                'blook' => $info['blook'] ?? 'dragon',
                'gold' => (int) $gold,
            ];
        }

        return $targets;
    }

    /**
     * Kết thúc Game: Lưu toàn bộ Redis ZSET sang MySQL Database
     *
     * @return array<int, array{rank: int, nickname: string, blook: string, gold: int}>
     */
    public function finalizeAndEndGame(string $pin): array
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        $room->update([
            'status' => 'finished',
            'ended_at' => Carbon::now(),
        ]);

        $finalScores = $this->engine->getAllScores($pin);
        $podium = [];
        $rank = 1;

        DB::transaction(function () use ($room, $finalScores, $pin, &$podium, &$rank) {
            foreach ($finalScores as $playerId => $gold) {
                $info = $this->engine->getPlayerInfo($pin, (string) $playerId);
                $participantId = $info['participant_id'] ?? null;

                $participant = null;
                if ($participantId) {
                    $participant = MinigameParticipant::find($participantId);
                }

                if (! $participant) {
                    $participant = MinigameParticipant::where('room_id', $room->id)
                        ->where('session_id', $playerId)
                        ->first();
                }

                if ($participant) {
                    $participant->update([
                        'gold' => (int) $gold,
                        'final_rank' => $rank,
                        'correct_count' => (int) ($info['correct'] ?? $participant->correct_count),
                        'wrong_count' => (int) ($info['wrong'] ?? $participant->wrong_count),
                        'chests_opened' => (int) ($info['chests'] ?? 0),
                    ]);

                    if ($rank <= 3) {
                        $podium[] = [
                            'rank' => $rank,
                            'nickname' => $participant->nickname,
                            'blook' => $participant->blook ?? 'dragon',
                            'gold' => (int) $gold,
                        ];
                    }
                }

                $rank++;
            }
        });

        // Bắn Socket Event vinh danh
        broadcast(new GameEnded($pin, $podium));

        return $podium;
    }

    protected function logActivity(int $roomId, ?int $participantId, string $type, string $message): void
    {
        MinigameActivityLog::create([
            'room_id' => $roomId,
            'participant_id' => $participantId,
            'action_type' => $type,
            'message' => $message,
        ]);
    }
}
