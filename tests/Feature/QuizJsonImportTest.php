<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuizJsonImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Giảng viên có thể tạo quiz mới bằng cách dán chuỗi JSON đầy đủ (tiêu đề + câu hỏi)
     */
    public function test_teacher_can_create_quiz_by_pasting_json(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Laravel 11',
            'status' => 'approved',
        ]);

        $jsonPayload = [
            'title' => 'Đề thi trắc nghiệm Laravel nâng cao',
            'description' => 'Kiểm tra kiến thức Service Container và Middleware',
            'duration_minutes' => 25,
            'passing_score' => 7.0,
            'randomize_questions' => true,
            'questions' => [
                [
                    'question_text' => 'Service Container trong Laravel là gì?',
                    'explanation' => 'Công cụ quản lý dependency injection mạnh mẽ.',
                    'options' => [
                        'Công cụ quản lý phụ thuộc (Dependency Injection)',
                        'Một dạng cơ sở dữ liệu NoSQL',
                        'Trình render HTML',
                        'Công cụ biên dịch CSS',
                    ],
                    'correct_option' => 0,
                ],
                [
                    'question_text' => 'Middleware được thực thi tại thời điểm nào?',
                    'explanation' => 'Lọc trước hoặc sau khi request tới Controller.',
                    'options' => [
                        'Khi biên dịch mã PHP',
                        'Trước hoặc sau khi HTTP request đến Controller',
                        'Khi máy chủ khởi động lại',
                        'Chỉ khi người dùng đăng xuất',
                    ],
                    'correct_option' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'json_content' => json_encode($jsonPayload, JSON_UNESCAPED_UNICODE),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Đề thi trắc nghiệm Laravel nâng cao',
            'duration_minutes' => 25,
            'passing_score' => 7.0,
            'status' => 'pending',
        ]);

        $quiz = Quiz::where('course_id', $course->id)->first();
        $this->assertNotNull($quiz);
        $this->assertCount(2, $quiz->questions);

        $q1 = $quiz->questions()->where('order_number', 1)->first();
        $this->assertEquals('Service Container trong Laravel là gì?', $q1->question_text);
        $this->assertCount(4, $q1->options);
        $this->assertTrue((bool) $q1->options[0]->is_correct);
        $this->assertFalse((bool) $q1->options[1]->is_correct);

        $q2 = $quiz->questions()->where('order_number', 2)->first();
        $this->assertTrue((bool) $q2->options[1]->is_correct);
    }

    /**
     * Giảng viên có thể tạo quiz mới bằng cách tải lên tệp tin .json
     */
    public function test_teacher_can_create_quiz_by_uploading_json_file(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học VueJS',
            'status' => 'approved',
        ]);

        $quizData = [
            'title' => 'Bài kiểm tra Vue 3 Composition API',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'questions' => [
                [
                    'question_text' => 'Hook nào thay thế cho created/beforeCreate trong script setup?',
                    'options' => [
                        'onMounted',
                        'setup() chạy trực tiếp',
                        'onUpdated',
                        'watchEffect',
                    ],
                    'correct_option' => 1,
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('quiz_vue.json', json_encode($quizData));

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'json_file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => 'Bài kiểm tra Vue 3 Composition API',
        ]);

        $quiz = Quiz::where('title', 'Bài kiểm tra Vue 3 Composition API')->first();
        $this->assertCount(1, $quiz->questions);
        $this->assertEquals('setup() chạy trực tiếp', $quiz->questions[0]->options[1]->option_text);
        $this->assertTrue((bool) $quiz->questions[0]->options[1]->is_correct);
    }

    /**
     * Tạo quiz với JSON mảng câu hỏi và đáp án đúng dạng chữ cái ('A', 'B', 'C', 'D')
     */
    public function test_teacher_can_create_quiz_with_letter_correct_option(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Lập trình Web',
            'status' => 'approved',
        ]);

        $rawQuestions = [
            [
                'question_text' => 'Thẻ HTML nào dùng để tạo liên kết?',
                'options' => ['<link>', '<a>', '<p>', '<div>'],
                'correct_option' => 'B',
            ],
        ];

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Đề trắc nghiệm HTML căn bản',
            'duration_minutes' => 10,
            'passing_score' => 5.0,
            'json_content' => json_encode($rawQuestions),
        ]);

        $response->assertRedirect();
        $quiz = Quiz::where('title', 'Đề trắc nghiệm HTML căn bản')->first();
        $this->assertNotNull($quiz);

        $question = $quiz->questions()->first();
        $this->assertTrue((bool) $question->options[1]->is_correct); // 'B' -> index 1
        $this->assertFalse((bool) $question->options[0]->is_correct);
    }

    /**
     * Báo lỗi khi JSON không hợp lệ
     */
    public function test_invalid_json_returns_validation_error(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($teacher)->from(route('teacher.quizzes.create'))->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'json_content' => '{ "title": "Bị thiếu dấu đóng ngoặc nhọn...',
        ]);

        $response->assertRedirect(route('teacher.quizzes.create'));
        $response->assertSessionHasErrors(['json_content']);
    }

    /**
     * Giảng viên có thể import thêm câu hỏi vào quiz đã có từ JSON
     */
    public function test_teacher_can_import_questions_into_existing_quiz(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Đề thi có sẵn',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'pending',
        ]);

        $moreQuestions = [
            [
                'question_text' => 'Câu hỏi thêm 1?',
                'options' => ['Đáp án 1', 'Đáp án 2'],
                'correct_option' => 0,
            ],
            [
                'question_text' => 'Câu hỏi thêm 2?',
                'options' => ['Đáp án 1', 'Đáp án 2'],
                'correct_option' => 1,
            ],
        ];

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.questions.import', $quiz->id), [
            'json_content' => json_encode($moreQuestions),
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $quiz->questions()->count());
    }
}
