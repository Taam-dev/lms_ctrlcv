<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
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

    public function test_chat_returns_smart_fallback_when_local_ai_is_offline(): void
    {
        // Khi không có kết nối tới Local AI server, endpoint không được trả về lỗi 500
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
            'message' => 'Các khóa học hiện có',
        ]);

        $response->assertOk();
        $message = $response->json('message');

        // Phải chứa khóa học đã duyệt và link của nó
        $this->assertStringContainsString('Khoa hoc Lap trinh Laravel 11 Chuyen Sau', $message);
        $this->assertStringContainsString($approvedCourse->url, $message);

        // Không được chứa khóa học đang chờ duyệt (zero-hallucination)
        $this->assertStringNotContainsString('Khoa hoc Bi Mat Chua Duyet', $message);
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

    public function test_chat_successfully_communicates_with_local_ai_when_online(): void
    {
        $mockedAnswer = 'Chào bạn! Đây là câu trả lời trực tiếp từ mô hình Local AI Qwen 2.5 trên máy chủ.';

        Http::fake([
            '*/chat/completions' => Http::response([
                'id' => 'chatcmpl-mock-123',
                'object' => 'chat.completion',
                'model' => 'qwen2.5:latest',
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
            'source' => 'local_ai',
            'model' => 'qwen2.5:latest',
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
}
