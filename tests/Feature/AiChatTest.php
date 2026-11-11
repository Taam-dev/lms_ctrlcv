<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use App\Services\Ai\AiClientManager;
use App\Services\Ai\AiModelTracker;
use App\Services\Ai\LmsContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ai_chat.providers' => [],
            'ai_chat.api_keys' => [],
        ]);

        $this->teacher = User::factory()->create([
            'role' => 'teacher',
            'name' => 'Giang Vien Mau',
        ]);
    }

    public function test_ai_chatbox_widget_is_mounted_on_home_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="btn-ai-chat-toggle"', false);
        $response->assertSee('Ctrl C+V AI');
        $response->assertSee('lmsAiChatbox');
    }

    public function test_ai_status_endpoint_returns_json_configuration(): void
    {
        $response = $this->getJson(route('ai.status'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'enabled',
            'provider',
            'model',
            'base_url',
            'ai_health',
            'catalog_summary' => [
                'total_courses',
                'free_courses',
                'paid_courses',
            ],
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_chat_endpoint_validates_required_message(): void
    {
        $response = $this->postJson(route('ai.chat'), [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_chat_returns_smart_fallback_when_all_ai_providers_are_offline(): void
    {
        // Khi không có kết nối tới AI Provider server, endpoint kích hoạt Smart Fallback không lỗi 500
        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Xin chào, bạn có thể giúp gì cho tôi?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'source' => 'smart_fallback',
        ]);
        $this->assertNotEmpty($response->json('message'));
        $this->assertNotEmpty($response->json('suggested_chips'));
    }

    public function test_chat_answers_courses_list_query_with_live_database_data(): void
    {
        $approvedCourse = Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khoa hoc Lap trinh Laravel 11 Chuyen Sau',
            'slug' => 'khoa-hoc-lap-trinh-laravel-11-chuyen-sau',
            'description' => 'Huong dan tu co ban den nang cao xay dung web app',
            'price' => 299000,
            'status' => 'approved',
        ]);

        $pendingCourse = Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khoa hoc Bi Mat Chua Duyet',
            'slug' => 'khoa-hoc-bi-mat-chua-duyet',
            'description' => 'Khoa hoc chua duyet',
            'price' => 500000,
            'status' => 'pending',
        ]);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Khóa học Laravel 11',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        // Phải chứa khóa học đã duyệt và link của nó
        $this->assertStringContainsString('Khoa hoc Lap trinh Laravel 11 Chuyen Sau', $message);
        $this->assertStringContainsString($approvedCourse->url, $message);

        // Không được chứa khóa học đang chờ duyệt (zero-hallucination)
        $this->assertStringNotContainsString('Khoa hoc Bi Mat Chua Duyet', $message);
    }

    public function test_chat_does_not_dump_all_courses_on_general_catalog_query_and_asks_for_field_of_interest(): void
    {
        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Tất cả khóa học',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        // Phải trả lời rằng sẽ có rất nhiều khóa học và hỏi lĩnh vực quan tâm
        $this->assertStringContainsString('rất nhiều khóa học', $message);
        $this->assertStringContainsString('quan tâm', $message);
        $this->assertStringContainsString('lĩnh vực', $message);

        // Không gợi ý nút "Tất cả khóa học" nữa
        $this->assertNotContains('Tất cả khóa học', $response->json('suggested_chips'));
        $this->assertContains('Lập trình Web', $response->json('suggested_chips'));
    }

    public function test_chat_filters_free_courses_accurately(): void
    {
        Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khoa hoc Git va Github Co Ban',
            'slug' => 'khoa-hoc-git-va-github-co-ban',
            'description' => 'Khoa hoc mien phi danh cho lap trinh vien',
            'price' => 0,
            'status' => 'approved',
        ]);

        Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khoa hoc ReactJS Masterclass',
            'slug' => 'khoa-hoc-reactjs-masterclass',
            'description' => 'Khoa hoc co phi',
            'price' => 350000,
            'status' => 'approved',
        ]);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Tìm khóa học miễn phí',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        $this->assertStringContainsString('Khoa hoc Git va Github Co Ban', $message);
    }

    public function test_chat_successfully_communicates_with_ai_provider_when_online(): void
    {
        config([
            'ai_chat.providers' => [
                [
                    'id' => 'provider_1',
                    'name' => 'Provider 1 (Chính)',
                    'base_url' => 'https://api.openai.com/v1',
                    'api_key' => 'mock-api-key',
                    'model' => 'gpt-4o-mini',
                ],
            ],
        ]);

        $mockedAnswer = 'Chào bạn! Đây là câu trả lời trực tiếp từ mô hình AI Provider gpt-4o-mini.';

        Http::fake([
            'https://api.openai.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'gpt-4o-mini', 'created' => 1720000000],
                ],
            ], 200),
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-mock-123',
                'object' => 'chat.completion',
                'model' => 'gpt-4o-mini',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => $mockedAnswer,
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Tư vấn cho tôi lộ trình học',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'source' => 'provider_1',
            'model' => 'gpt-4o-mini',
            'provider' => 'Provider 1 (Chính)',
            'message' => $mockedAnswer,
        ]);
    }

    public function test_chat_tracks_student_progress_when_authenticated(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Nguyen Van Hoc Vien',
        ]);

        $course = Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khoa hoc JavaScript ES6',
            'slug' => 'khoa-hoc-javascript-es6',
            'description' => 'Hoc lap trinh JS hien dai',
            'price' => 100000,
            'status' => 'approved',
        ]);

        $lesson1 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bai 1: Bien va Ham',
            'content_type' => 'text',
            'content' => 'Noi dung bai 1',
            'order_number' => 1,
        ]);

        $lesson2 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bai 2: Promise va Async Await',
            'content_type' => 'text',
            'content' => 'Noi dung bai 2',
            'order_number' => 2,
        ]);

        // Đăng ký khóa học
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'price_paid' => 100000,
        ]);

        // Hoàn thành bài 1
        LessonCompletion::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson1->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson(route('ai.chat'), [
            'message' => 'Khóa học của tôi và tiến độ',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        // Phải nhận diện được học viên và tiến độ
        $this->assertStringContainsString('Nguyen Van Hoc Vien', $message);
        $this->assertStringContainsString('Khoa hoc JavaScript ES6', $message);
        $this->assertStringContainsString('50%', $message);
        $this->assertStringContainsString('Bai 2: Promise va Async Await', $message);
    }

    public function test_off_topic_queries_are_refused_strictly_by_guardrails(): void
    {
        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Cách nấu phở bò Hà Nội thơm ngon',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'source' => 'guardrail',
            'message' => 'Nội dung bạn hỏi không thuộc phạm vi của trang web Ctrl C+V LMS. Mình chỉ hỗ trợ giải đáp thông tin về các khóa học, giảng viên, lộ trình học tập và các tính năng trên hệ thống thôi nhé!',
        ]);
    }

    public function test_analytical_query_superlative_returns_exact_count_and_sorting(): void
    {
        $courseFewReviews = Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khóa học ít đánh giá',
            'slug' => 'khoa-hoc-it-danh-gia',
            'description' => 'Mô tả',
            'price' => 100000,
            'status' => 'approved',
        ]);

        $courseManyReviews = Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khóa học nhiều đánh giá vượt trội',
            'slug' => 'khoa-hoc-nhieu-danh-gia-vuot-troi',
            'description' => 'Mô tả',
            'price' => 200000,
            'status' => 'approved',
        ]);

        $student = User::factory()->create();

        // 1 review cho courseFew
        CourseReview::create([
            'course_id' => $courseFewReviews->id,
            'user_id' => $student->id,
            'rating' => 4,
            'comment' => 'Khá ổn',
        ]);

        // 3 reviews cho courseMany
        for ($i = 0; $i < 3; $i++) {
            $u = User::factory()->create();
            CourseReview::create([
                'course_id' => $courseManyReviews->id,
                'user_id' => $u->id,
                'rating' => 5,
                'comment' => 'Tuyệt vời '.$i,
            ]);
        }

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'cho tôi 1 khóa học có nhiều đánh giá nhất đi',
        ]);

        $response->assertOk();
        $message = $response->json('message');
        $courses = $response->json('relevant_courses');

        // Phải trả về đúng 1 khóa học
        $this->assertCount(1, $courses);
        $this->assertEquals($courseManyReviews->id, $courses[0]['id']);
        $this->assertStringContainsString('Khóa học nhiều đánh giá vượt trội', $message);
        $this->assertStringNotContainsString('Khóa học ít đánh giá', $message);
        $this->assertStringNotContainsString('Tìm thấy 5 khóa học', $message);
    }

    public function test_multi_api_provider_failover_rotates_when_primary_fails(): void
    {
        config([
            'ai_chat.providers' => [
                [
                    'id' => 'provider_1',
                    'name' => 'Provider 1',
                    'url' => 'https://api.openai.com/v1',
                    'key' => 'key-1',
                    'model' => 'gpt-4o',
                    'timeout' => 5,
                ],
                [
                    'id' => 'provider_2',
                    'name' => 'Provider 2',
                    'url' => 'https://api.groq.com/openai/v1',
                    'key' => 'key-2',
                    'model' => 'llama-3.3-70b-versatile',
                    'timeout' => 5,
                ],
            ],
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response(['error' => 'Rate limit exceeded'], 429),
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-backup',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Câu trả lời từ Backup Provider 2 Groq',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Tư vấn khóa học lập trình phù hợp cho người mới bắt đầu',
        ]);

        $response->assertOk();
        $this->assertEquals('Câu trả lời từ Backup Provider 2 Groq', $response->json('message'));
        $this->assertEquals('provider_2', $response->json('source'));
        $this->assertEquals('llama-3.3-70b-versatile', $response->json('model'));
    }

    public function test_ai_model_tracker_discovers_and_filters_non_chat_models(): void
    {
        $tracker = app(AiModelTracker::class);

        Http::fake([
            'https://api.openai.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'text-embedding-3-small', 'created' => 1710000000],
                    ['id' => 'whisper-1', 'created' => 1700000000],
                    ['id' => 'dall-e-3', 'created' => 1715000000],
                    ['id' => 'gpt-3.5-turbo', 'created' => 1705000000],
                    ['id' => 'gpt-4o-mini', 'created' => 1721000000],
                ],
            ], 200),
        ]);

        $activeModels = $tracker->fetchActiveModels('https://api.openai.com/v1', 'mock-key', true);

        $ids = array_column($activeModels, 'id');
        $this->assertContains('gpt-4o-mini', $ids);
        $this->assertContains('gpt-3.5-turbo', $ids);
        $this->assertNotContains('text-embedding-3-small', $ids);
        $this->assertNotContains('whisper-1', $ids);
        $this->assertNotContains('dall-e-3', $ids);
    }

    public function test_ai_model_tracker_automatically_picks_newest_and_best_model_when_auto(): void
    {
        $tracker = app(AiModelTracker::class);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([
                'data' => [
                    ['id' => 'gemma2-9b-it', 'created' => 1719000000],
                    ['id' => 'llama-3.1-8b-instant', 'created' => 1721000000],
                    ['id' => 'llama-3.3-70b-versatile', 'created' => 1733000000],
                ],
            ], 200),
        ]);

        $bestModel = $tracker->pickBestActiveModel('https://api.groq.com/openai/v1', 'mock-key', 'auto', true);

        // Mô hình Llama 3.3 thuộc tier 5 mới nhất sẽ được tự động chọn
        $this->assertEquals('llama-3.3-70b-versatile', $bestModel);
    }

    public function test_primary_provider_is_put_in_cooldown_when_out_of_tokens(): void
    {
        $manager = app(AiClientManager::class);

        config([
            'ai_chat.providers' => [
                [
                    'id' => 'provider_1',
                    'name' => 'Provider 1',
                    'url' => 'https://api.openai.com/v1',
                    'key' => 'key-1',
                    'model' => 'gpt-4o-mini',
                ],
                [
                    'id' => 'provider_2',
                    'name' => 'Provider 2',
                    'url' => 'https://api.backup.com/v1',
                    'key' => 'key-2',
                    'model' => 'backup-model',
                ],
            ],
        ]);

        $this->assertFalse($manager->isTargetInCooldown('provider_1'));

        // Giả lập provider 1 hết token
        $manager->markTargetExhausted('provider_1', 'Quota exceeded');

        $this->assertTrue($manager->isTargetInCooldown('provider_1'));

        $manager->buildTargetQueue();
        $targets = $manager->getTargets();

        // Provider 2 sẵn sàng sẽ được đưa lên đầu hàng đợi
        $this->assertEquals('provider_2', $targets[0]['id']);
        $this->assertEquals('provider_1', $targets[1]['id']);
    }

    public function test_artisan_ai_track_models_command_executes_successfully(): void
    {
        config([
            'ai_chat.providers' => [
                [
                    'id' => 'provider_1',
                    'name' => 'Provider 1 (Chính)',
                    'url' => 'https://api.openai.com/v1',
                    'key' => 'sk-test1234567890',
                    'model' => 'auto',
                ],
            ],
        ]);

        Http::fake([
            'https://api.openai.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'gpt-4o-mini', 'created' => 1720000000],
                ],
            ], 200),
        ]);

        $this->artisan('ai:track-models --refresh')
            ->assertSuccessful();
    }

    public function test_chat_does_not_attach_relevant_courses_on_greetings_and_typos(): void
    {
        Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Cơ Sở Dữ Liệu SQL & Thiết Kế Hệ Thống',
            'slug' => 'co-so-du-lieu-sql',
            'description' => 'Khóa học SQL cơ bản',
            'price' => 0,
            'status' => 'approved',
        ]);

        // Thử với "xin chaop" (gõ nhầm)
        $response = $this->postJson(route('ai.chat'), [
            'message' => 'xin chaop',
        ]);

        $response->assertOk();
        $this->assertEmpty($response->json('relevant_courses'));
        $this->assertStringNotContainsString('không thuộc phạm vi', $response->json('message'));

        // Thử với "xin chaos" (gõ nhầm phím s telex)
        $responseChaos = $this->postJson(route('ai.chat'), [
            'message' => 'xin chaos',
        ]);

        $responseChaos->assertOk();
        $this->assertEmpty($responseChaos->json('relevant_courses'));
        $this->assertStringNotContainsString('không thuộc phạm vi', $responseChaos->json('message'));

        // Thử với "xin chào"
        $responseHello = $this->postJson(route('ai.chat'), [
            'message' => 'xin chào',
        ]);

        $responseHello->assertOk();
        $this->assertEmpty($responseHello->json('relevant_courses'));
        $this->assertStringNotContainsString('không thuộc phạm vi', $responseHello->json('message'));
    }

    public function test_chat_provides_exact_teacher_panel_navigation_when_asking_how_to_create_lesson(): void
    {
        Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khóa Học Lập Trình PHP',
            'slug' => 'khoa-hoc-lap-trinh-php',
            'description' => 'Học PHP từ cơ bản',
            'price' => 100000,
            'status' => 'approved',
        ]);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'làm sao để tạo bài giảng',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        // Phải hướng dẫn chính xác: Profile góc trên bên phải -> chọn mục GV -> Bảng điều khiển Giảng viên
        $this->assertStringContainsString('góc trên bên phải', $message);
        $this->assertStringContainsString('Profile', $message);
        $this->assertStringContainsString('GV', $message);
        $this->assertStringContainsString('Bảng điều khiển Giảng viên', $message);
        $this->assertStringContainsString('Bài giảng', $message);

        // Không gắn các khóa học ngẫu nhiên vào câu trả lời nghiệp vụ
        $this->assertEmpty($response->json('relevant_courses'));

        // Gợi ý các nút câu hỏi liên quan đến giảng viên
        $this->assertContains('Bảng điều khiển Giảng viên', $response->json('suggested_chips'));
    }

    public function test_system_prompt_includes_teacher_workflow_rules(): void
    {
        $contextService = app(LmsContextService::class);
        $prompt = $contextService->buildSystemPrompt(null, [], [], []);

        $this->assertStringContainsString('QUY TẮC BẮT BUỘC DÀNH CHO GIẢNG VIÊN', $prompt);
        $this->assertStringContainsString('góc trên bên phải', $prompt);
        $this->assertStringContainsString('"GV"', $prompt);
        $this->assertStringContainsString(route('instructor.dashboard'), $prompt);
        $this->assertStringContainsString('+ Bài giảng', $prompt);
    }

    public function test_chat_responds_wittily_with_course_suggestion_when_user_vents_about_breakup(): void
    {
        Course::create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Khóa Học Fullstack Web Master',
            'slug' => 'khoa-hoc-fullstack-web-master',
            'description' => 'Học lập trình chuyên sâu',
            'price' => 199000,
            'status' => 'approved',
        ]);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'người yêu tôi chia tay tôi rồi',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        // Phải trả lời hóm hỉnh "thêm muối", gợi ý khóa học và icon kính cười
        $this->assertStringContainsString('Nghe buồn vậy', $message);
        $this->assertStringContainsString('quên đi nỗi buồn', $message);
        $this->assertStringContainsString('tình yêu', $message);
        $this->assertStringContainsString('😎😆', $message);
        $this->assertStringContainsString('Khóa Học Fullstack Web Master', $message);

        // Không từ chối kiểu robot "không thuộc phạm vi"
        $this->assertStringNotContainsString('không thuộc phạm vi', $message);
    }

    public function test_system_prompt_includes_witty_heartbreak_rule(): void
    {
        $contextService = app(LmsContextService::class);
        $prompt = $contextService->buildSystemPrompt(null, [], [], []);

        $this->assertStringContainsString('THÊM MUỐI', $prompt);
        $this->assertStringContainsString('THẤT TÌNH', $prompt);
        $this->assertStringContainsString('😎😆', $prompt);
    }

    public function test_system_prompt_includes_gold_quest_minigame_knowledge(): void
    {
        $contextService = app(LmsContextService::class);
        $prompt = $contextService->buildSystemPrompt(null, [], [], []);

        $this->assertStringContainsString('GOLD QUEST', $prompt);
        $this->assertStringContainsString('CƯỚP VÀNG', $prompt);
        $this->assertStringContainsStringIgnoringCase('mở 1 trong 3 chiếc rương', $prompt);
        $this->assertStringContainsString('SWAP', $prompt);
        $this->assertStringContainsString(route('minigame.index'), $prompt);
    }

    public function test_chat_handles_minigame_query_gracefully_without_off_topic_refusal(): void
    {
        $response = $this->postJson(route('ai.chat'), [
            'message' => 'minigame Gold Quest cướp vàng là gì và chơi như thế nào?',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $message = $response->json('message');

        $this->assertStringContainsString('Gold Quest', $message);
        $this->assertStringContainsString('rương', $message);
        $this->assertStringContainsString('vàng', $message);
        $this->assertStringNotContainsString('không thuộc phạm vi', $message);
    }
}
