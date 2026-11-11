<?php

namespace Tests\Feature;

use App\Models\MinigameParticipant;
use App\Models\MinigameQuestion;
use App\Models\MinigameQuiz;
use App\Models\MinigameRoom;
use App\Models\User;
use App\Services\Ai\AiClientManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MinigameFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
        ]);

        $this->teacher = User::factory()->create([
            'role' => 'teacher',
            'name' => 'Giang Vien Test',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Hoc Vien Test',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_minigame(): void
    {
        $response = $this->get(route('minigame.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_student_can_access_minigame_dashboard(): void
    {
        $response = $this->actingAs($this->student)->get(route('minigame.index'));

        $response->assertOk();
        $response->assertSee('Minigame');
        $response->assertSee('Mã PIN Phòng');
        $response->assertSee('Tham Gia Trận Đấu');
    }

    public function test_header_navigation_contains_minigame_link(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(route('minigame.index'));
        $response->assertSee('Minigame');
    }

    public function test_students_cannot_access_quiz_creation_page(): void
    {
        $response = $this->actingAs($this->student)->get(route('minigame.quizzes.create'));

        $response->assertForbidden();
    }

    public function test_teacher_can_access_quiz_creation_page(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('minigame.quizzes.create'));

        $response->assertOk();
        $response->assertSee('Tạo Bộ Câu Hỏi Minigame Mới');
        $response->assertSee('Tạo Bằng AI');
        $response->assertSee('Import JSON');
        $response->assertSee('Tạo Thủ Công');
    }

    public function test_sample_json_can_be_downloaded(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('minigame.sample-json'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json; charset=utf-8');
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('question', $data[0]);
        $this->assertArrayHasKey('options', $data[0]);
        $this->assertArrayHasKey('correct_answer', $data[0]);
    }

    public function test_teacher_can_create_quiz_manually(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('minigame.quizzes.store'), [
            'creation_mode' => 'manual',
            'title' => 'Đề thi thủ công 1',
            'difficulty' => 'easy',
            'questions' => [
                [
                    'question' => '1 + 1 bằng mấy?',
                    'options' => [
                        'A' => '1',
                        'B' => '2',
                        'C' => '3',
                        'D' => '4',
                    ],
                    'correct_answer' => 'B',
                    'time_limit' => 15,
                ],
            ],
        ]);

        $response->assertRedirect(route('minigame.index'));
        $this->assertDatabaseHas('minigame_quizzes', [
            'title' => 'Đề thi thủ công 1',
            'user_id' => $this->teacher->id,
            'difficulty' => 'easy',
        ]);
        $this->assertDatabaseHas('minigame_questions', [
            'question' => '1 + 1 bằng mấy?',
            'correct_answer' => 'B',
        ]);
    }

    public function test_teacher_can_access_quiz_edit_page(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Đề thi cần sửa',
            'difficulty' => 'medium',
        ]);

        MinigameQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Câu hỏi mẫu?',
            'options' => ['A' => '1', 'B' => '2'],
            'correct_answer' => 'A',
            'time_limit' => 20,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('minigame.quizzes.edit', $quiz));

        $response->assertOk();
        $response->assertSee('Chỉnh Sửa Bộ Đề');
        $response->assertSee('Đề thi cần sửa');
        $response->assertSee('Nạp Gói Mẫu');
    }

    public function test_teacher_can_create_quiz_via_json_import(): void
    {
        $jsonPayload = json_encode([
            [
                'question' => 'Laravel là gì?',
                'options' => [
                    'A' => 'PHP Framework',
                    'B' => 'Database',
                    'C' => 'Web Browser',
                    'D' => 'Hệ điều hành',
                ],
                'correct_answer' => 'A',
                'time_limit' => 20,
            ],
        ]);

        $response = $this->actingAs($this->teacher)->post(route('minigame.quizzes.store'), [
            'creation_mode' => 'import_json',
            'title' => 'Đề thi import JSON',
            'difficulty' => 'medium',
            'json_text' => $jsonPayload,
        ]);

        $response->assertRedirect(route('minigame.index'));
        $this->assertDatabaseHas('minigame_quizzes', [
            'title' => 'Đề thi import JSON',
        ]);
        $this->assertDatabaseHas('minigame_questions', [
            'question' => 'Laravel là gì?',
            'correct_answer' => 'A',
        ]);
    }

    public function test_teacher_can_create_quiz_via_ai_generation(): void
    {
        $fakeAiJson = json_encode([
            [
                'question' => 'OOP viết tắt của từ gì?',
                'options' => [
                    'A' => 'Object Oriented Programming',
                    'B' => 'Only One Person',
                    'C' => 'Online Operating Process',
                    'D' => 'Open Outline Protocol',
                ],
                'correct_answer' => 'A',
                'time_limit' => 20,
            ],
        ]);

        $mockAiManager = Mockery::mock(AiClientManager::class);
        $mockAiManager->shouldReceive('chat')
            ->once()
            ->andReturn([
                'success' => true,
                'content' => $fakeAiJson,
                'error' => null,
                'model' => 'qwen-mock',
                'provider' => 'Mock AI',
                'provider_id' => 'mock_1',
            ]);

        $this->app->instance(AiClientManager::class, $mockAiManager);

        $response = $this->actingAs($this->teacher)->post(route('minigame.quizzes.store'), [
            'creation_mode' => 'ai_generate',
            'title' => 'Đề thi sinh từ AI',
            'topic' => 'Lập trình hướng đối tượng trong PHP',
            'question_count' => 1,
            'difficulty' => 'hard',
        ]);

        $response->assertRedirect(route('minigame.index'));
        $this->assertDatabaseHas('minigame_quizzes', [
            'title' => 'Đề thi sinh từ AI',
            'difficulty' => 'hard',
        ]);
        $this->assertDatabaseHas('minigame_questions', [
            'question' => 'OOP viết tắt của từ gì?',
            'correct_answer' => 'A',
        ]);
    }

    public function test_teacher_can_create_and_host_a_room(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz để mở phòng',
            'difficulty' => 'medium',
        ]);

        MinigameQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Thủ đô của Việt Nam là gì?',
            'options' => ['A' => 'Hà Nội', 'B' => 'TP.HCM', 'C' => 'Đà Nẵng', 'D' => 'Huế'],
            'correct_answer' => 'A',
            'time_limit' => 20,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('minigame.rooms.create'), [
            'quiz_id' => $quiz->id,
            'duration_minutes' => 5,
        ]);

        $room = MinigameRoom::where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($room);
        $this->assertEquals(6, strlen($room->pin_code));
        $this->assertEquals('waiting', $room->status);

        $response->assertRedirect(route('minigame.rooms.host', $room->pin_code));
    }

    public function test_student_can_join_room_using_pin_code(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Test Join',
            'difficulty' => 'medium',
        ]);

        MinigameQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => '2 x 3 = ?',
            'options' => ['A' => '5', 'B' => '6', 'C' => '7', 'D' => '8'],
            'correct_answer' => 'B',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '654321',
            'status' => 'waiting',
            'duration_minutes' => 5,
        ]);

        $response = $this->actingAs($this->student)->post(route('minigame.join'), [
            'pin_code' => '654321',
            'nickname' => 'GameMaster99',
        ]);

        $response->assertRedirect(route('minigame.rooms.lobby', '654321'));

        $this->assertDatabaseHas('minigame_participants', [
            'room_id' => $room->id,
            'user_id' => $this->student->id,
            'nickname' => 'GameMaster99',
        ]);
    }

    public function test_lobby_polling_returns_participant_data(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Poll Test',
            'difficulty' => 'easy',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '112233',
            'status' => 'waiting',
            'duration_minutes' => 5,
        ]);

        MinigameParticipant::create([
            'room_id' => $room->id,
            'user_id' => $this->student->id,
            'nickname' => 'HocVienA',
        ]);

        $response = $this->actingAs($this->student)->getJson(route('minigame.rooms.poll-lobby', '112233'));

        $response->assertOk();
        $response->assertJson([
            'status' => 'waiting',
            'count' => 1,
        ]);
        $response->assertJsonFragment([
            'nickname' => 'HocVienA',
        ]);
    }

    public function test_teacher_can_start_room(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Start Room Test',
            'difficulty' => 'medium',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '998877',
            'status' => 'waiting',
            'duration_minutes' => 5,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('minigame.rooms.start', '998877'));

        $response->assertRedirect(route('minigame.rooms.host.screen', '998877'));

        $room->refresh();
        $this->assertEquals('playing', $room->status);
        $this->assertNotNull($room->started_at);
    }

    public function test_student_gameplay_answer_wrong_gives_penalty(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Gameplay',
            'difficulty' => 'medium',
        ]);

        $question = MinigameQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Mặt trời mọc ở hướng nào?',
            'options' => ['A' => 'Tây', 'B' => 'Đông', 'C' => 'Nam', 'D' => 'Bắc'],
            'correct_answer' => 'B',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '334455',
            'status' => 'playing',
            'duration_minutes' => 5,
            'started_at' => now(),
        ]);

        $participant = MinigameParticipant::create([
            'room_id' => $room->id,
            'user_id' => $this->student->id,
            'nickname' => 'Tester',
            'gold' => 0,
        ]);

        $response = $this->actingAs($this->student)
            ->withSession(["minigame_participant_{$room->pin_code}" => $participant->id])
            ->postJson(route('minigame.rooms.answer', $room->pin_code), [
                'question_id' => $question->id,
                'answer' => 'A', // sai
            ]);

        $response->assertOk();
        $response->assertJson([
            'is_correct' => false,
            'penalty_seconds' => 3,
        ]);

        $participant->refresh();
        $this->assertEquals(1, $participant->wrong_count);
    }

    public function test_student_gameplay_answer_correct_and_open_chest(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Gameplay Correct',
            'difficulty' => 'medium',
        ]);

        $question = MinigameQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Mặt trời mọc ở hướng nào?',
            'options' => ['A' => 'Tây', 'B' => 'Đông', 'C' => 'Nam', 'D' => 'Bắc'],
            'correct_answer' => 'B',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '778899',
            'status' => 'playing',
            'duration_minutes' => 5,
            'started_at' => now(),
        ]);

        $participant = MinigameParticipant::create([
            'room_id' => $room->id,
            'user_id' => $this->student->id,
            'nickname' => 'Tester',
            'gold' => 0,
        ]);

        // Trả lời đúng
        $answerResponse = $this->actingAs($this->student)
            ->withSession(["minigame_participant_{$room->pin_code}" => $participant->id])
            ->postJson(route('minigame.rooms.answer', $room->pin_code), [
                'question_id' => $question->id,
                'answer' => 'B', // đúng
            ]);

        $answerResponse->assertOk();
        $answerResponse->assertJson([
            'is_correct' => true,
        ]);

        // Mở rương bí ẩn
        $chestResponse = $this->actingAs($this->student)
            ->withSession(["minigame_participant_{$room->pin_code}" => $participant->id])
            ->postJson(route('minigame.rooms.open-chest', $room->pin_code), [
                'chest_index' => 1,
            ]);

        $chestResponse->assertOk();
        $chestResponse->assertJsonStructure([
            'success',
            'result' => [
                'type',
                'title',
                'description',
                'new_gold',
                'diff',
                'icon',
            ],
        ]);

        $this->assertDatabaseHas('minigame_activity_logs', [
            'room_id' => $room->id,
            'participant_id' => $participant->id,
        ]);
    }

    public function test_host_screen_polling_returns_leaderboard_and_logs(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Host Screen',
            'difficulty' => 'medium',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '556677',
            'status' => 'playing',
            'duration_minutes' => 5,
            'started_at' => now(),
        ]);

        MinigameParticipant::create([
            'room_id' => $room->id,
            'user_id' => $this->student->id,
            'nickname' => 'TopPlayer',
            'gold' => 500,
            'correct_count' => 3,
        ]);

        $response = $this->actingAs($this->teacher)->getJson(route('minigame.rooms.poll-host', '556677'));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'is_finished',
            'remaining_seconds',
            'formatted_time',
            'leaderboard',
            'activities',
        ]);
        $response->assertJsonFragment([
            'nickname' => 'TopPlayer',
            'gold' => 500,
        ]);
    }

    public function test_podium_renders_top_participants(): void
    {
        $quiz = MinigameQuiz::create([
            'user_id' => $this->teacher->id,
            'title' => 'Quiz Podium',
            'difficulty' => 'medium',
        ]);

        $room = MinigameRoom::create([
            'host_id' => $this->teacher->id,
            'quiz_id' => $quiz->id,
            'pin_code' => '445566',
            'status' => 'finished',
            'duration_minutes' => 5,
            'started_at' => now()->subMinutes(10),
            'ended_at' => now(),
        ]);

        MinigameParticipant::create([
            'room_id' => $room->id,
            'nickname' => 'Vô Địch',
            'gold' => 1200,
            'correct_count' => 6,
        ]);

        MinigameParticipant::create([
            'room_id' => $room->id,
            'nickname' => 'Á Quân',
            'gold' => 800,
            'correct_count' => 4,
        ]);

        $response = $this->actingAs($this->student)->get(route('minigame.rooms.podium', '445566'));

        $response->assertOk();
        $response->assertSee('Bục Vinh Danh');
        $response->assertSee('Nhà Vô Địch');
        $response->assertSee('Vô Địch');
        $response->assertSee('Á Quân');
        $response->assertSee('1,200');
        $response->assertSee('800');
    }

    public function test_quiz_can_be_created_and_updated_without_course_id(): void
    {
        // 1. Tạo bộ đề độc lập (không cần course_id)
        $storeResponse = $this->actingAs($this->teacher)->post(route('minigame.quizzes.store'), [
            'creation_mode' => 'manual',
            'title' => 'Bộ đề độc lập không theo khóa học',
            'difficulty' => 'medium',
            'description' => 'Bộ đề tự do độc lập phong cách Blooket',
            'questions' => [
                [
                    'question' => 'Thủ đô của Nhật Bản là gì?',
                    'options' => ['A' => 'Tokyo', 'B' => 'Kyoto', 'C' => 'Osaka', 'D' => 'Nagoya'],
                    'correct_answer' => 'A',
                    'time_limit' => 20,
                ],
            ],
        ]);

        $storeResponse->assertRedirect(route('minigame.index'));

        $quiz = MinigameQuiz::where('title', 'Bộ đề độc lập không theo khóa học')->first();
        $this->assertNotNull($quiz);
        $this->assertNull($quiz->course_id);
        $this->assertEquals($this->teacher->id, $quiz->user_id);

        // 2. Chỉnh sửa thời gian câu hỏi và thông tin bộ đề
        $updateResponse = $this->actingAs($this->teacher)->put(route('minigame.quizzes.update', $quiz), [
            'title' => 'Bộ đề độc lập (Đã cập nhật thời gian)',
            'difficulty' => 'hard',
            'description' => 'Đã chỉnh sửa thời gian thành 45 giây',
            'questions' => [
                [
                    'question' => 'Thủ đô của Nhật Bản là gì?',
                    'options' => ['A' => 'Tokyo', 'B' => 'Kyoto', 'C' => 'Osaka', 'D' => 'Nagoya'],
                    'correct_answer' => 'A',
                    'time_limit' => 45, // đã chỉnh sửa thời gian
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('minigame.index'));

        $quiz->refresh();
        $this->assertEquals('Bộ đề độc lập (Đã cập nhật thời gian)', $quiz->title);
        $this->assertEquals('hard', $quiz->difficulty);
        $this->assertNull($quiz->course_id);

        $question = $quiz->questions()->first();
        $this->assertEquals(45, $question->time_limit);
    }

    public function test_guest_is_redirected_to_login_on_join(): void
    {
        $response = $this->post(route('minigame.join'), [
            'pin_code' => '123456',
            'nickname' => 'GuestUser',
        ]);

        $response->assertRedirect(route('login'));
    }
}
