<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Giảng viên có thể tạo quiz mới kèm cài đặt đảo câu hỏi
     */
    public function test_teacher_can_create_quiz_with_randomize_setting(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Laravel 11',
            'description' => 'Mô tả',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Kiểm tra trắc nghiệm chương 1',
            'description' => 'Bài test thử',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'randomize_questions' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Kiểm tra trắc nghiệm chương 1',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'randomize_questions' => 1,
            'status' => 'pending',
        ]);
    }

    /**
     * Giảng viên có thể thêm câu hỏi trắc nghiệm và 4 đáp án
     */
    public function test_teacher_can_add_question_and_options_to_quiz(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học PHP Cơ Bản',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz 1',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'randomize_questions' => true,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.questions.store', $quiz->id), [
            'question_text' => 'Laravel là gì?',
            'explanation' => 'Laravel là một PHP Framework mã nguồn mở phổ biến.',
            'options' => [
                'Một PHP Framework',
                'Một hệ điều hành',
                'Một trình duyệt web',
                'Một ngôn ngữ lập trình',
            ],
            'correct_option' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question_text' => 'Laravel là gì?',
        ]);

        $question = QuizQuestion::where('quiz_id', $quiz->id)->first();
        $this->assertCount(4, $question->options);
        $this->assertTrue((bool) $question->options[0]->is_correct);
        $this->assertFalse((bool) $question->options[1]->is_correct);
    }

    /**
     * Admin có thể duyệt bài kiểm tra
     */
    public function test_admin_can_approve_and_reject_quiz(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Vue',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Chờ Duyệt',
            'status' => 'pending',
        ]);

        // Duyệt
        $response = $this->actingAs($admin)->post(route('admin.quizzes.approve', $quiz->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'status' => 'approved',
        ]);

        // Từ chối
        $response = $this->actingAs($admin)->post(route('admin.quizzes.reject', $quiz->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Học viên không thể làm quiz nếu chưa đăng ký khóa học
     */
    public function test_student_cannot_take_quiz_without_enrollment(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Node.js',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Node.js',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $response->assertRedirect(route('student.courses.show', $course->id));
    }

    /**
     * Học viên đã đăng ký làm quiz, nộp bài và nhận điểm số tức thì
     */
    public function test_student_can_take_quiz_and_get_immediate_score(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học SQL',
            'status' => 'approved',
        ]);
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz SQL Cơ Bản',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'randomize_questions' => true,
            'status' => 'approved',
        ]);

        // Tạo 2 câu hỏi
        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'SQL là viết tắt của gì?',
            'order_number' => 1,
        ]);
        $q1OptCorrect = QuizOption::create([
            'quiz_question_id' => $q1->id,
            'option_text' => 'Structured Query Language',
            'is_correct' => true,
        ]);
        $q1OptWrong = QuizOption::create([
            'quiz_question_id' => $q1->id,
            'option_text' => 'Simple Query Language',
            'is_correct' => false,
        ]);

        $q2 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Lệnh nào để lấy dữ liệu?',
            'order_number' => 2,
        ]);
        $q2OptCorrect = QuizOption::create([
            'quiz_question_id' => $q2->id,
            'option_text' => 'SELECT',
            'is_correct' => true,
        ]);
        $q2OptWrong = QuizOption::create([
            'quiz_question_id' => $q2->id,
            'option_text' => 'INSERT',
            'is_correct' => false,
        ]);

        // Học viên vào trang làm bài
        $takeResponse = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertStatus(200);
        $takeResponse->assertSee('Quiz SQL Cơ Bản');
        $takeResponse->assertSee('Chưa hoàn thành bài thi');
        $takeResponse->assertSee('validateAndSubmitQuiz');
        $takeResponse->assertSee('unanswered-warning');

        // Học viên nộp bài: Đúng câu 1, sai câu 2 => 1/2 câu đúng = 5.0 điểm (Đạt)
        $submitResponse = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [
                $q1->id => $q1OptCorrect->id,
                $q2->id => $q2OptWrong->id,
            ],
        ]);

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $student->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals(2, $attempt->total_questions);
        $this->assertEquals(1, $attempt->correct_answers);
        $this->assertEquals(5.0, $attempt->score);
        $this->assertTrue((bool) $attempt->is_passed);

        $submitResponse->assertRedirect(route('student.quizzes.result', [$quiz->id, $attempt->id]));

        // Học viên xem trang kết quả
        $resultResponse = $this->actingAs($student)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $resultResponse->assertStatus(200);
        $resultResponse->assertSee('5.0');
        $resultResponse->assertSee('ĐẠT');
    }

    /**
     * Giảng viên có thể tạo đề thi với các loại: Kiểm tra, Giữa kỳ, Cuối kỳ
     */
    public function test_teacher_can_create_quiz_with_midterm_and_final_types(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test Types',
            'status' => 'approved',
        ]);

        // 1. Kiểm tra giữa kỳ
        $resMidterm = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'midterm',
            'title' => 'Bài kiểm tra giữa kỳ 1',
            'duration_minutes' => 45,
            'passing_score' => 5.0,
        ]);
        $resMidterm->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Bài kiểm tra giữa kỳ 1',
            'type' => 'midterm',
        ]);

        $midtermQuiz = Quiz::where('title', 'Bài kiểm tra giữa kỳ 1')->first();
        $this->assertEquals('Kiểm tra giữa kỳ', $midtermQuiz->type_name);

        // 2. Kiểm tra cuối kỳ
        $resFinal = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'final',
            'title' => 'Bài kiểm tra cuối kỳ',
            'duration_minutes' => 60,
            'passing_score' => 5.0,
        ]);
        $resFinal->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Bài kiểm tra cuối kỳ',
            'type' => 'final',
        ]);

        $finalQuiz = Quiz::where('title', 'Bài kiểm tra cuối kỳ')->first();
        $this->assertEquals('Kiểm tra cuối kỳ', $finalQuiz->type_name);

        // 3. Kiểm tra thông thường
        $resRegular = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'quiz',
            'title' => 'Kiểm tra 15 phút',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
        ]);
        $resRegular->assertRedirect();
        $regularQuiz = Quiz::where('title', 'Kiểm tra 15 phút')->first();
        $this->assertEquals('Kiểm tra', $regularQuiz->type_name);
    }

    /**
     * Có thể tạo quiz Tự do (không theo khóa học nào)
     */
    public function test_can_create_tu_do_quiz_without_course(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => '',
            'type' => 'quiz',
            'title' => 'Đề thi trắc nghiệm Tự Do',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => null,
            'teacher_id' => $teacher->id,
            'title' => 'Đề thi trắc nghiệm Tự Do',
        ]);
    }

    /**
     * Học viên có thể tham gia và nộp bài thi Tự do mà không cần đăng ký khóa học
     */
    public function test_student_can_take_and_submit_tu_do_quiz(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $quiz = Quiz::create([
            'course_id' => null, // Tự do
            'teacher_id' => $teacher->id,
            'type' => 'midterm',
            'title' => 'Kiểm tra giữa kỳ mở rộng',
            'duration_minutes' => 20,
            'passing_score' => 5.0,
            'status' => 'approved',
        ]);

        $q = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'order_number' => 1,
        ]);

        $optCorrect = QuizOption::create([
            'quiz_question_id' => $q->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);

        // Học viên truy cập làm bài (không cần đăng ký khóa học nào)
        $takeResponse = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertOk();
        $takeResponse->assertSee('Kiểm tra giữa kỳ mở rộng');
        $takeResponse->assertSee('Tự do');

        // Học viên nộp bài
        $submitResponse = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [
                $q->id => $optCorrect->id,
            ],
        ]);

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $student->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals(10.0, $attempt->score);
        $this->assertTrue((bool) $attempt->is_passed);

        $submitResponse->assertRedirect(route('student.quizzes.result', [$quiz->id, $attempt->id]));

        // Học viên xem kết quả
        $resultResponse = $this->actingAs($student)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $resultResponse->assertOk();
        $resultResponse->assertSee('10');
        $resultResponse->assertSee('ĐẠT');
    }

    /**
     * Giảng viên có thể cấu hình ngày bắt đầu và kết thúc (starts_at, ends_at)
     */
    public function test_teacher_can_create_quiz_with_date_range(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $startsAt = now()->addDay()->format('Y-m-d\TH:i');
        $endsAt = now()->addDays(5)->format('Y-m-d\TH:i');

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'title' => 'Kiểm tra có giới hạn thời gian',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Kiểm tra có giới hạn thời gian',
            'teacher_id' => $teacher->id,
        ]);

        $quiz = Quiz::where('title', 'Kiểm tra có giới hạn thời gian')->first();
        $this->assertNotNull($quiz->starts_at);
        $this->assertNotNull($quiz->ends_at);
        $this->assertTrue($quiz->isNotStarted());
        $this->assertFalse($quiz->isExpired());
    }

    /**
     * Học viên không thể làm bài hoặc nộp bài nếu bài kiểm tra đã quá hạn
     */
    public function test_student_cannot_take_or_submit_expired_quiz(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Bài thi đã quá hạn',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'approved',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDay(), // đã hết hạn ngày hôm qua
        ]);

        $q = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => '1 + 1 = ?',
            'order_number' => 1,
        ]);

        $takeResponse = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertRedirect(route('student.quizzes.index'));
        $takeResponse->assertSessionHas('error');

        $submitResponse = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [$q->id => 1],
        ]);
        $submitResponse->assertStatus(403);
    }

    /**
     * Học viên không thể làm bài nếu bài kiểm tra chưa đến thời gian mở
     */
    public function test_student_cannot_take_not_started_quiz(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Bài thi chưa mở',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'approved',
            'starts_at' => now()->addDay(), // ngày mai mới mở
            'ends_at' => now()->addDays(3),
        ]);

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Câu hỏi 1',
            'order_number' => 1,
        ]);

        $takeResponse = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertRedirect(route('student.quizzes.index'));
        $takeResponse->assertSessionHas('error');
    }

    /**
     * Giảng viên có thể tạo quiz với số lượng câu hỏi mỗi lượt làm (question_limit)
     */
    public function test_teacher_can_create_quiz_with_question_limit(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $sampleQuestions = [];
        for ($i = 1; $i <= 5; $i++) {
            $sampleQuestions[] = [
                'question_text' => "Câu hỏi số {$i}",
                'options' => ['A', 'B', 'C', 'D'],
                'correct_option' => 0,
            ];
        }

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'title' => 'Bài kiểm tra có giới hạn số câu',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'question_limit' => 3,
            'json_content' => json_encode($sampleQuestions),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'teacher_id' => $teacher->id,
            'title' => 'Bài kiểm tra có giới hạn số câu',
            'question_limit' => 3,
        ]);

        $quiz = Quiz::where('title', 'Bài kiểm tra có giới hạn số câu')->first();
        $this->assertCount(5, $quiz->questions);
        $this->assertEquals(3, $quiz->question_limit);
    }

    /**
     * Không thể lưu quiz nếu question_limit lớn hơn số lượng câu hỏi đang có
     */
    public function test_question_limit_cannot_exceed_total_questions(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $sampleQuestions = [
            [
                'question_text' => 'Câu 1',
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ],
            [
                'question_text' => 'Câu 2',
                'options' => ['A', 'B'],
                'correct_option' => 1,
            ],
        ];

        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'title' => 'Bài kiểm tra giới hạn câu hỏi sai',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'question_limit' => 5, // Lớn hơn 2 câu
            'json_content' => json_encode($sampleQuestions),
        ]);

        $response->assertSessionHasErrors('question_limit');
    }

    /**
     * Học viên làm bài có question_limit chỉ nhận đúng số lượng câu và tính điểm chuẩn xác
     */
    public function test_student_taking_quiz_with_question_limit_is_scored_against_assigned_questions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Đề thi 4 câu lấy từ 10 câu',
            'duration_minutes' => 20,
            'passing_score' => 5.0,
            'question_limit' => 4,
            'randomize_questions' => true,
            'status' => 'approved',
        ]);

        $createdQuestions = [];
        for ($i = 1; $i <= 10; $i++) {
            $q = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => "Câu hỏi {$i}",
                'order_number' => $i,
            ]);
            $optCorrect = QuizOption::create([
                'quiz_question_id' => $q->id,
                'option_text' => 'Đúng',
                'is_correct' => true,
            ]);
            $optWrong = QuizOption::create([
                'quiz_question_id' => $q->id,
                'option_text' => 'Sai',
                'is_correct' => false,
            ]);
            $createdQuestions[] = [
                'question' => $q,
                'correct_id' => $optCorrect->id,
                'wrong_id' => $optWrong->id,
            ];
        }

        // Vào trang làm bài
        $takeResponse = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertOk();
        $assignedQuestions = $takeResponse->viewData('questions');
        $this->assertCount(4, $assignedQuestions);

        // Giả sử học viên trả lời 3 câu đúng và 1 câu sai trong số 4 câu được giao
        $answers = [];
        $questionIds = [];
        foreach ($assignedQuestions as $index => $qItem) {
            $questionIds[] = $qItem->id;
            $matched = collect($createdQuestions)->firstWhere('question.id', $qItem->id);
            if ($index < 3) {
                // 3 câu đúng
                $answers[$qItem->id] = $matched['correct_id'];
            } else {
                // 1 câu sai
                $answers[$qItem->id] = $matched['wrong_id'];
            }
        }

        $submitResponse = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => $answers,
            'question_ids' => $questionIds,
        ]);

        $submitResponse->assertRedirect();

        $attempt = QuizAttempt::where('student_id', $student->id)->where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals(3, $attempt->correct_answers); // 3 câu đúng
        $this->assertEquals(4, $attempt->total_questions); // 4 câu được giao
        // Điểm theo thang 10: (3 / 4) * 10 = 7.5
        $this->assertEquals(7.5, (float) $attempt->score);
        $this->assertTrue((bool) $attempt->is_passed);
    }

    /**
     * Endpoint AI tạo câu hỏi kiểm tra validation dữ liệu gửi lên
     */
    public function test_ai_question_generation_endpoint_validation(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->postJson(route('teacher.quizzes.generate-ai-questions'), [
            // Không gửi api_key và content
            'provider' => 'gemini',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['api_key', 'content']);

        // Kiểm tra không chấp nhận số âm
        $negativeResponse = $this->actingAs($teacher)->postJson(route('teacher.quizzes.generate-ai-questions'), [
            'provider' => 'gemini',
            'api_key' => 'valid-api-key',
            'model' => 'gemini-2.0-flash',
            'content' => 'Nội dung kiến thức bài học để kiểm tra',
            'count' => -5,
        ]);
        $negativeResponse->assertStatus(422);
        $negativeResponse->assertJsonValidationErrors(['count']);

        // Kiểm tra không chấp nhận số float (1.2)
        $floatResponse = $this->actingAs($teacher)->postJson(route('teacher.quizzes.generate-ai-questions'), [
            'provider' => 'gemini',
            'api_key' => 'valid-api-key',
            'model' => 'gemini-2.0-flash',
            'content' => 'Nội dung kiến thức bài học để kiểm tra',
            'count' => 1.2,
        ]);
        $floatResponse->assertStatus(422);
        $floatResponse->assertJsonValidationErrors(['count']);
    }

    /**
     * Endpoint AI quét model kiểm tra validation
     */
    public function test_ai_fetch_models_endpoint_validation(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->postJson(route('teacher.quizzes.fetch-ai-models'), [
            'provider' => 'groq',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['api_key']);
    }

    /**
     * Endpoint AI quét model trả về danh sách model thành công từ Groq
     */
    public function test_ai_fetch_models_groq_success(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([
                'data' => [
                    ['id' => 'canopylabs/orpheus-v1-english', 'active' => true], // should be filtered out
                    ['id' => 'openai/gpt-oss-120b', 'active' => true],
                    ['id' => 'llama-3.1-8b-instant', 'active' => true],
                    ['id' => 'whisper-large-v3', 'active' => true], // should be filtered out
                ],
            ], 200),
        ]);

        $response = $this->actingAs($teacher)->postJson(route('teacher.quizzes.fetch-ai-models'), [
            'provider' => 'groq',
            'api_key' => 'gsk_test1234567890',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'count' => 2,
        ]);
        $response->assertJsonFragment(['id' => 'llama-3.1-8b-instant']);
        $response->assertJsonFragment(['id' => 'openai/gpt-oss-120b']);
        $response->assertJsonMissing(['id' => 'canopylabs/orpheus-v1-english']);
        $response->assertJsonMissing(['id' => 'whisper-large-v3']);

        // Assert priority order: llama-3.1-8b-instant must be first
        $models = $response->json('models');
        $this->assertEquals('llama-3.1-8b-instant', $models[0]['id']);
    }

    /**
     * Giảng viên có thể cập nhật câu hỏi và các đáp án của đề thi
     */
    public function test_teacher_can_update_quiz_question(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Bài kiểm tra sửa câu hỏi',
            'type' => 'quiz',
            'duration_minutes' => 30,
            'passing_score' => 5,
        ]);

        $question = $quiz->questions()->create([
            'question_text' => 'Câu hỏi ban đầu',
            'explanation' => 'Giải thích cũ',
            'order_number' => 1,
        ]);

        $question->options()->createMany([
            ['option_text' => 'Đáp án cũ 1', 'is_correct' => true],
            ['option_text' => 'Đáp án cũ 2', 'is_correct' => false],
        ]);

        $response = $this->actingAs($teacher)->put(route('teacher.quizzes.questions.update', [$quiz->id, $question->id]), [
            'question_text' => 'Câu hỏi đã được cập nhật',
            'explanation' => 'Giải thích mới',
            'options' => [
                'Đáp án mới A',
                'Đáp án mới B',
                'Đáp án mới C',
                'Đáp án mới D',
            ],
            'correct_option' => 2, // Đáp án C
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quiz_questions', [
            'id' => $question->id,
            'question_text' => 'Câu hỏi đã được cập nhật',
            'explanation' => 'Giải thích mới',
        ]);

        $this->assertEquals(4, $question->fresh()->options()->count());
        $this->assertDatabaseHas('quiz_options', [
            'quiz_question_id' => $question->id,
            'option_text' => 'Đáp án mới C',
            'is_correct' => true,
        ]);
        $this->assertDatabaseHas('quiz_options', [
            'quiz_question_id' => $question->id,
            'option_text' => 'Đáp án mới A',
            'is_correct' => false,
        ]);
    }

    /**
     * Endpoint AI gợi ý / tối ưu câu hỏi kiểm tra validation
     */
    public function test_ai_suggest_or_refine_question_validation(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->postJson(route('teacher.quizzes.ai-suggest-question'), [
            'provider' => 'gemini',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['api_key', 'question_text']);
    }

    /**
     * Endpoint AI gợi ý đáp án trả về câu hỏi và 4 đáp án thành công
     */
    public function test_ai_suggest_or_refine_question_success(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'question_text' => 'Hà Nội là thủ đô của quốc gia nào?',
                                        'explanation' => 'Hà Nội là thủ đô của nước CHXHCN Việt Nam.',
                                        'options' => [
                                            ['option_text' => 'Việt Nam', 'is_correct' => true],
                                            ['option_text' => 'Lào', 'is_correct' => false],
                                            ['option_text' => 'Campuchia', 'is_correct' => false],
                                            ['option_text' => 'Thái Lan', 'is_correct' => false],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($teacher)->postJson(route('teacher.quizzes.ai-suggest-question'), [
            'provider' => 'gemini',
            'api_key' => 'valid-api-key-12345',
            'model' => 'gemini-1.5-flash',
            'question_text' => 'Hà Nội là thủ đô của quốc gia nào?',
            'action' => 'suggest_options',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertEquals('Hà Nội là thủ đô của quốc gia nào?', $response->json('question.question_text'));
        $this->assertCount(4, $response->json('question.options'));
        $this->assertTrue($response->json('question.options.0.is_correct'));
    }

    /**
     * Học viên có thể tìm kiếm bài kiểm tra theo tên đề thi
     */
    public function test_student_can_search_quizzes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz1 = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Mrbeast 1 Thử Thách',
            'type' => 'quiz',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'approved',
        ]);

        $quiz2 = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Laravel Framework Cơ Bản',
            'type' => 'quiz',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($student)->get(route('student.quizzes.index', ['search' => 'Mrbeast']));
        $response->assertOk();
        $response->assertSee('Mrbeast 1 Thử Thách');
        $response->assertDontSee('Laravel Framework Cơ Bản');
    }

    /**
     * Bài kiểm tra có thời gian mở trong quá khứ hoặc hiện tại được mở thành công
     */
    public function test_quiz_with_past_or_current_starts_at_is_open(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Bài thi đã đến giờ mở',
            'type' => 'quiz',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'approved',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDay(),
        ]);

        $this->assertFalse($quiz->isNotStarted());
        $this->assertFalse($quiz->isExpired());
        $this->assertTrue($quiz->isAvailable());
    }

    /**
     * Giảng viên có thể thiết lập quyền xem kết quả sau khi hoàn thành
     */
    public function test_teacher_can_create_and_update_quiz_with_show_result_after_completion(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Vue 3',
            'description' => 'Mô tả',
            'status' => 'approved',
        ]);

        // Tạo với lựa chọn Không cho xem kết quả
        $response = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Kiểm tra không cho xem kết quả',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'show_result_after_completion' => '0',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Kiểm tra không cho xem kết quả',
            'show_result_after_completion' => 0,
        ]);

        $quiz = Quiz::where('title', 'Kiểm tra không cho xem kết quả')->first();

        // Cập nhật lại thành Có cho xem kết quả
        $updateResponse = $this->actingAs($teacher)->put(route('teacher.quizzes.update', $quiz->id), [
            'course_id' => $course->id,
            'title' => 'Kiểm tra đã cho xem kết quả',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'show_result_after_completion' => '1',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => 'Kiểm tra đã cho xem kết quả',
            'show_result_after_completion' => 1,
        ]);
    }

    /**
     * Học viên không thể xem chi tiết đáp án khi show_result_after_completion bị tắt
     */
    public function test_student_result_view_hides_detailed_questions_when_show_result_is_disabled(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Bài thi bảo mật đáp án',
            'type' => 'quiz',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'show_result_after_completion' => false,
            'status' => 'approved',
        ]);

        $q = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Câu hỏi bảo mật 1?',
            'explanation' => 'Giải thích bí mật',
            'order_number' => 1,
        ]);

        $optA = QuizOption::create([
            'quiz_question_id' => $q->id,
            'option_text' => 'Đáp án đúng',
            'is_correct' => true,
        ]);

        $optB = QuizOption::create([
            'quiz_question_id' => $q->id,
            'option_text' => 'Đáp án sai',
            'is_correct' => false,
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'total_questions' => 1,
            'correct_answers' => 1,
            'score' => 10.0,
            'is_passed' => true,
            'answers' => [$q->id => $optA->id],
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $response->assertOk();
        // Học viên vẫn xem được bảng điểm và điểm số tổng kết
        $response->assertSee('10.0');
        $response->assertSee('/ 10 điểm');
        $response->assertSee('KẾT QUẢ: ĐẠT YÊU CẦU');
        $response->assertSee('Câu hỏi &amp; đáp án chi tiết không hiển thị', false);
        // Nhưng KHÔNG hiển thị lại câu hỏi và đáp án chi tiết
        $response->assertDontSee('Đối chiếu đáp án chi tiết');
        $response->assertDontSee('Câu hỏi bảo mật 1?');
        $response->assertDontSee('Giải thích bí mật');

        // Giảng viên xem kết quả học viên vẫn thấy đầy đủ câu hỏi và lời giải thích
        $teacherResponse = $this->actingAs($teacher)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $teacherResponse->assertOk();
        $teacherResponse->assertSee('Đối chiếu đáp án chi tiết');
        $teacherResponse->assertSee('Câu hỏi bảo mật 1?');
        $teacherResponse->assertSee('Giải thích bí mật');
    }

    /**
     * kiem tra bao loi tieng viet khi ngay ket thuc truoc ngay bat dau cho giang vien
     */
    public function test_teacher_quiz_creation_fails_with_vietnamese_error_when_ends_at_before_starts_at(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test Ngày',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($teacher)->from(route('teacher.quizzes.create'))->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'quiz',
            'title' => 'Quiz kiem tra thoi gian',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'starts_at' => '2026-10-20T10:00',
            'ends_at' => '2026-10-20T08:00',
        ]);

        $response->assertRedirect(route('teacher.quizzes.create'));
        $response->assertSessionHasErrors([
            'ends_at' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
        ]);
    }

    /**
     * kiem tra bao loi tieng viet khi cap nhat ngay ket thuc truoc ngay bat dau cho admin
     */
    public function test_admin_quiz_update_fails_with_vietnamese_error_when_ends_at_before_starts_at(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $quiz = Quiz::create([
            'teacher_id' => $admin->id,
            'type' => 'quiz',
            'title' => 'Quiz Admin test',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.quizzes.edit', $quiz->id))->put(route('admin.quizzes.update', $quiz->id), [
            'type' => 'quiz',
            'title' => 'Quiz Admin test sua',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'status' => 'approved',
            'starts_at' => '2026-10-25T14:00',
            'ends_at' => '2026-10-25T12:00',
        ]);

        $response->assertRedirect(route('admin.quizzes.edit', $quiz->id));
        $response->assertSessionHasErrors([
            'ends_at' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
        ]);
    }

    /**
     * kiem tra bao loi khi thoi gian lam bai duoi 1 tieng hoac trung nhau cho giang vien
     */
    public function test_teacher_quiz_creation_fails_when_duration_is_less_than_one_hour_or_identical(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test Thoi Gian 1',
            'status' => 'approved',
        ]);

        // truong hop trung nhau 6:00 va 6:00
        $responseSameTime = $this->actingAs($teacher)->from(route('teacher.quizzes.create'))->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'quiz',
            'title' => 'Quiz test trung gio',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'starts_at' => '2026-10-20T06:00',
            'ends_at' => '2026-10-20T06:00',
        ]);

        $responseSameTime->assertRedirect(route('teacher.quizzes.create'));
        $responseSameTime->assertSessionHasErrors([
            'ends_at' => 'Thời hạn làm bài ít nhất là 1 tiếng.',
        ]);

        // truong hop cach nhau it hon 1 tieng 6:00 va 6:30
        $responseShort = $this->actingAs($teacher)->from(route('teacher.quizzes.create'))->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'quiz',
            'title' => 'Quiz test duoi 1 tieng',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'starts_at' => '2026-10-20T06:00',
            'ends_at' => '2026-10-20T06:30',
        ]);

        $responseShort->assertRedirect(route('teacher.quizzes.create'));
        $responseShort->assertSessionHasErrors([
            'ends_at' => 'Thời hạn làm bài ít nhất là 1 tiếng.',
        ]);
    }

    /**
     * kiem tra tao thanh cong khi thoi gian cach nhau tu 1 tieng tro len
     */
    public function test_teacher_quiz_creation_succeeds_when_duration_is_at_least_one_hour(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test Thoi Gian 2',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($teacher)->from(route('teacher.quizzes.create'))->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'type' => 'quiz',
            'title' => 'Quiz test dung 1 tieng',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'starts_at' => '2026-10-20T06:00',
            'ends_at' => '2026-10-20T07:00',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => 'Quiz test dung 1 tieng',
        ]);
    }

    /**
     * kiem tra bao loi khi admin cap nhat quiz co thoi gian duoi 1 tieng
     */
    public function test_admin_quiz_update_fails_when_duration_is_less_than_one_hour(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $quiz = Quiz::create([
            'teacher_id' => $admin->id,
            'type' => 'quiz',
            'title' => 'Quiz Admin time',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.quizzes.edit', $quiz->id))->put(route('admin.quizzes.update', $quiz->id), [
            'type' => 'quiz',
            'title' => 'Quiz Admin sua time',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'status' => 'approved',
            'starts_at' => '2026-10-25T06:00',
            'ends_at' => '2026-10-25T06:45',
        ]);

        $response->assertRedirect(route('admin.quizzes.edit', $quiz->id));
        $response->assertSessionHasErrors([
            'ends_at' => 'Thời hạn làm bài ít nhất là 1 tiếng.',
        ]);
    }

    /**
     * Kiem tra luu so lan hoc vien bi canh cao vao quiz_attempt khi nop bai
     */
    public function test_student_quiz_submission_records_warning_count(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'type' => 'quiz',
            'title' => 'Bài thi kiểm tra cảnh cáo',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'status' => 'approved',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Câu hỏi 1?',
            'order_number' => 1,
        ]);

        $option = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Đáp án đúng',
            'is_correct' => true,
        ]);

        $response = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'question_ids' => [$question->id],
            'answers' => [$question->id => $option->id],
            'warning_count' => 3,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'warning_count' => 3,
        ]);
    }

    /**
     * Kiem tra thong tin canh cao bi an voi hoc vien nhung hien thi cho giang vien va admin
     */
    public function test_warning_count_is_hidden_from_student_but_visible_to_teacher_and_admin(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'type' => 'quiz',
            'title' => 'Bài thi quy chế bảo mật cảnh cáo',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'status' => 'approved',
            'show_result_after_completion' => true,
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Câu hỏi quy chế?',
            'order_number' => 1,
        ]);

        $option = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Đáp án A',
            'is_correct' => true,
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'total_questions' => 1,
            'correct_answers' => 1,
            'score' => 10.0,
            'is_passed' => true,
            'warning_count' => 5,
            'completed_at' => now(),
        ]);

        // 1. Hoc vien xem ket qua cua chinh minh -> KHONG duoc thay thong tin canh cao
        $studentResponse = $this->actingAs($student)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $studentResponse->assertOk();
        $studentResponse->assertDontSee('GIÁM SÁT VI PHẠM (ADMIN & GIẢNG VIÊN)');
        $studentResponse->assertDontSee('Số lần học viên bị cảnh cáo chuyển tab:');

        // 2. Giang vien xem ket qua hoc vien -> THAY thong tin canh cao :5
        $teacherResponse = $this->actingAs($teacher)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $teacherResponse->assertOk();
        $teacherResponse->assertSee('GIÁM SÁT VI PHẠM (ADMIN &amp; GIẢNG VIÊN)', false);
        $teacherResponse->assertSee(':5');

        // 3. Admin xem ket qua hoc vien -> THAY thong tin canh cao :5
        $adminResponse = $this->actingAs($admin)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $adminResponse->assertOk();
        $adminResponse->assertSee('GIÁM SÁT VI PHẠM (ADMIN &amp; GIẢNG VIÊN)', false);
        $adminResponse->assertSee(':5');

        // 4. Giang vien xem trang danh sach results -> Co cot thao tac va nut Xem
        $teacherResultsResponse = $this->actingAs($teacher)->get(route('instructor.quizzes.results', $quiz->id));
        $teacherResultsResponse->assertOk();
        $teacherResultsResponse->assertSee('Thao tác');
        $teacherResultsResponse->assertSee('Xem');
        $teacherResultsResponse->assertSee(':5');

        // 5. Admin xem trang danh sach results -> Co cot thao tac va nut Xem
        $adminResultsResponse = $this->actingAs($admin)->get(route('admin.quizzes.results', $quiz->id));
        $adminResultsResponse->assertOk();
        $adminResultsResponse->assertSee('Thao tác');
        $adminResultsResponse->assertSee('Xem');
        $adminResultsResponse->assertSee(':5');
    }

    /**
     * Admin và giảng viên có thể tạo và cập nhật quiz với giới hạn số lần làm bài (max_attempts)
     */
    public function test_admin_and_teacher_can_create_and_update_quiz_with_max_attempts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test Max Attempts',
            'status' => 'approved',
        ]);

        // 1. Admin tạo quiz với max_attempts = 3
        $adminResponse = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Admin Quiz with Max Attempts',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'max_attempts' => 3,
        ]);
        $adminResponse->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Admin Quiz with Max Attempts',
            'max_attempts' => 3,
        ]);

        // 2. Admin tạo quiz để trống max_attempts -> mặc định null (thoải mái)
        $adminResponse2 = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Admin Quiz Unlimited Attempts',
            'duration_minutes' => 30,
            'passing_score' => 5.0,
            'max_attempts' => '',
        ]);
        $adminResponse2->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Admin Quiz Unlimited Attempts',
            'max_attempts' => null,
        ]);

        // 3. Teacher tạo quiz với max_attempts = 2
        $teacherResponse = $this->actingAs($teacher)->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Teacher Quiz with Max Attempts',
            'duration_minutes' => 15,
            'passing_score' => 6.0,
            'max_attempts' => 2,
        ]);
        $teacherResponse->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'title' => 'Teacher Quiz with Max Attempts',
            'max_attempts' => 2,
        ]);

        $createdQuiz = Quiz::where('title', 'Teacher Quiz with Max Attempts')->first();

        // 4. Teacher cập nhật max_attempts lên 5
        $updateResponse = $this->actingAs($teacher)->put(route('teacher.quizzes.update', $createdQuiz->id), [
            'title' => 'Teacher Quiz with Max Attempts Updated',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'max_attempts' => 5,
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $createdQuiz->id,
            'title' => 'Teacher Quiz with Max Attempts Updated',
            'max_attempts' => 5,
        ]);
    }

    /**
     * Học viên bị chặn làm bài và nộp bài khi đã đạt số lần làm lại tối đa
     */
    public function test_student_cannot_take_or_submit_quiz_when_max_attempts_reached(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Python',
            'status' => 'approved',
        ]);
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz giới hạn 2 lần làm',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'max_attempts' => 2,
            'status' => 'approved',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Câu hỏi 1?',
            'order' => 1,
        ]);
        QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Đáp án đúng',
            'is_correct' => true,
        ]);

        // Lần 1: Học viên vào thi thành công
        $take1 = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $take1->assertOk();

        // Học viên nộp bài lần 1
        $submit1 = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [$question->id => 0],
        ]);
        $submit1->assertRedirect();
        $this->assertEquals(1, $quiz->attempts()->where('student_id', $student->id)->count());

        // Lần 2: Học viên vào thi lần 2 thành công
        $take2 = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $take2->assertOk();

        // Học viên nộp bài lần 2
        $submit2 = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [$question->id => 0],
        ]);
        $submit2->assertRedirect();
        $this->assertEquals(2, $quiz->attempts()->where('student_id', $student->id)->count());

        // Đã đạt giới hạn tối đa 2 lần
        $this->assertTrue($quiz->hasReachedMaxAttempts($student));
        $this->assertEquals(0, $quiz->getRemainingAttempts($student));

        // Lần 3: Học viên cố tình vào link làm bài -> bị redirect và thông báo lỗi
        $take3 = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $take3->assertRedirect();
        $take3->assertSessionHas('error');

        // Học viên cố tình gửi request POST submit -> bị abort 403
        $submit3 = $this->actingAs($student)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [$question->id => 0],
        ]);
        $submit3->assertStatus(403);
    }

    /**
     * Học viên có thể làm bài nhiều lần nếu không cài đặt max_attempts (mặc định thoải mái)
     */
    public function test_student_can_take_quiz_unlimited_times_when_max_attempts_is_null(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học JavaScript',
            'status' => 'approved',
        ]);
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz không giới hạn lần làm',
            'duration_minutes' => 20,
            'passing_score' => 5.0,
            'max_attempts' => null,
            'status' => 'approved',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'JavaScript là gì?',
            'order' => 1,
        ]);
        QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => 'Ngôn ngữ lập trình web',
            'is_correct' => true,
        ]);

        // Tạo sẵn 5 lượt làm bài của học viên này
        for ($i = 0; $i < 5; $i++) {
            QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'total_questions' => 1,
                'correct_answers' => 1,
                'score' => 10.0,
                'is_passed' => true,
                'completed_at' => now(),
            ]);
        }

        $this->assertFalse($quiz->hasReachedMaxAttempts($student));
        $this->assertNull($quiz->getRemainingAttempts($student));

        // Học viên vẫn có thể vào làm bài lần thứ 6 bình thường
        $takeResponse = $this->actingAs($student)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertOk();
    }

    /**
     * Admin có thể truy cập danh sách và làm bài quiz thuộc khóa học mà không cần đăng ký khóa học
     */
    public function test_admin_can_access_and_take_course_quiz_without_enrollment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Node.js Nâng Cao',
            'status' => 'approved',
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Event Loop & Buffer',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'approved',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Event Loop có mấy phase chính?',
            'order_number' => 1,
        ]);
        QuizOption::create(['quiz_question_id' => $question->id, 'option_text' => '6 phases', 'is_correct' => true, 'order_number' => 1]);
        QuizOption::create(['quiz_question_id' => $question->id, 'option_text' => '2 phases', 'is_correct' => false, 'order_number' => 2]);

        // 1. Admin truy cập danh sách quizzes thấy bài kiểm tra của khóa học dù không enroll
        $indexResponse = $this->actingAs($admin)->get(route('student.quizzes.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Quiz Event Loop & Buffer');

        // 2. Admin vào trang làm bài trực tiếp mà không bị redirect bắt đăng ký
        $takeResponse = $this->actingAs($admin)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertOk();
        $takeResponse->assertSee('Quiz Event Loop & Buffer');

        // 3. Admin nộp bài thi thành công không bị 403
        $submitResponse = $this->actingAs($admin)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [
                $question->id => $question->options()->where('is_correct', true)->first()->id,
            ],
            'question_ids' => [$question->id],
        ]);
        $submitResponse->assertRedirect();
    }

    /**
     * Admin có thể làm lại bài kiểm tra không giới hạn ngay cả khi đã vượt quá max_attempts
     */
    public function test_admin_can_retake_quiz_even_when_max_attempts_is_reached(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Test Limits Admin',
            'duration_minutes' => 10,
            'passing_score' => 5.0,
            'status' => 'approved',
            'max_attempts' => 2,
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => '1 + 1 = ?',
            'order_number' => 1,
        ]);
        QuizOption::create(['quiz_question_id' => $question->id, 'option_text' => '2', 'is_correct' => true, 'order_number' => 1]);
        QuizOption::create(['quiz_question_id' => $question->id, 'option_text' => '3', 'is_correct' => false, 'order_number' => 2]);

        // Tạo 2 lần làm bài đã hoàn thành cho Admin
        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $admin->id,
            'total_questions' => 1,
            'correct_answers' => 1,
            'score' => 10.0,
            'is_passed' => true,
            'completed_at' => now(),
        ]);
        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $admin->id,
            'total_questions' => 1,
            'correct_answers' => 1,
            'score' => 10.0,
            'is_passed' => true,
            'completed_at' => now(),
        ]);

        // Admin vào làm bài lần thứ 3 vẫn OK (không bị chặn max_attempts)
        $takeResponse = $this->actingAs($admin)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertOk();

        // Admin nộp bài thi lần 3 thành công
        $submitResponse = $this->actingAs($admin)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [
                $question->id => $question->options()->where('is_correct', true)->first()->id,
            ],
            'question_ids' => [$question->id],
        ]);
        $submitResponse->assertRedirect();
    }

    /**
     * Giảng viên cũng có thể làm bài kiểm tra và cũng có thể bị ghi nhận gian lận (chuyển tab)
     */
    public function test_teacher_can_take_quiz_and_be_recorded_for_cheat_tab_switch_warning(): void
    {
        $authorTeacher = User::factory()->create(['role' => 'teacher']);
        $takingTeacher = User::factory()->create(['role' => 'teacher', 'name' => 'Thầy Giáo Test']);

        $course = Course::create([
            'teacher_id' => $authorTeacher->id,
            'title' => 'Khóa Học Lập Trình Python',
            'status' => 'approved',
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $authorTeacher->id,
            'title' => 'Bài Kiểm Tra Python Căn Bản',
            'status' => 'approved',
            'duration_minutes' => 30,
            'passing_score' => 6.0,
            'max_attempts' => 2,
        ]);

        $question = $quiz->questions()->create([
            'question_text' => 'Python là ngôn ngữ thông dịch hay biên dịch?',
            'order_number' => 1,
        ]);

        $correctOpt = $question->options()->create([
            'option_text' => 'Thông dịch',
            'is_correct' => true,
        ]);

        $question->options()->create([
            'option_text' => 'Chỉ biên dịch',
            'is_correct' => false,
        ]);

        // 1. Giảng viên vào làm bài kiểm tra thành công (không bị bắt buộc đăng ký khóa học)
        $takeResponse = $this->actingAs($takingTeacher)->get(route('student.quizzes.take', $quiz->id));
        $takeResponse->assertOk();
        $takeResponse->assertSee('Bài Kiểm Tra Python Căn Bản');

        // 2. Giảng viên nộp bài thi có kèm cảnh báo gian lận (chuyển tab 4 lần)
        $submitResponse = $this->actingAs($takingTeacher)->post(route('student.quizzes.submit', $quiz->id), [
            'answers' => [
                $question->id => $correctOpt->id,
            ],
            'question_ids' => [$question->id],
            'warning_count' => 4,
        ]);
        $submitResponse->assertRedirect();

        // 3. Hệ thống lưu lại kết quả và ghi nhận đúng 4 lần gian lận cho giảng viên
        $attempt = QuizAttempt::where('student_id', $takingTeacher->id)->where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals(10.0, (float) $attempt->score);
        $this->assertEquals(4, $attempt->warning_count);

        // 4. Giảng viên xem trang kết quả chi tiết
        $resultResponse = $this->actingAs($takingTeacher)->get(route('student.quizzes.result', [$quiz->id, $attempt->id]));
        $resultResponse->assertOk();
        $resultResponse->assertSee(':4');
        $resultResponse->assertSee('Thầy Giáo Test');

        // 5. Giảng viên tạo đề xem kết quả cũng thấy lượt thi của giảng viên này kèm cảnh báo gian lận
        $instructorResultsResponse = $this->actingAs($authorTeacher)->get(route('instructor.quizzes.results', $quiz->id));
        $instructorResultsResponse->assertOk();
        $instructorResultsResponse->assertSee('Thầy Giáo Test');
        $instructorResultsResponse->assertSee(':4');
    }

    /**
     * Kiểm tra khi thí sinh (học viên hoặc giảng viên) làm bài:
     * - Nếu show_result_after_completion = false: sau khi hoàn thành chỉ hiển thị điểm số, KHÔNG hiển thị đối chiếu kết quả/đáp án.
     * - Nếu show_result_after_completion = true: sau khi hoàn thành hiển thị đầy đủ điểm và đối chiếu kết quả/đáp án chi tiết.
     */
    public function test_examinee_only_sees_score_without_review_when_show_result_after_completion_is_disabled_and_vice_versa(): void
    {
        $author = User::factory()->create(['role' => 'teacher']);
        $takingUser = User::factory()->create(['role' => 'teacher', 'name' => 'Thí Sinh Khảo Sát']);

        // 1. Tạo bài quiz với show_result_after_completion = false (Chọn "Không")
        $quizHidden = Quiz::create([
            'teacher_id' => $author->id,
            'title' => 'Đề thi ẩn đối chiếu kết quả',
            'type' => 'quiz',
            'duration_minutes' => 20,
            'passing_score' => 5.0,
            'show_result_after_completion' => false,
            'status' => 'approved',
        ]);

        $qHidden = QuizQuestion::create([
            'quiz_id' => $quizHidden->id,
            'question_text' => 'Nội dung câu hỏi ẩn kết quả 123?',
            'explanation' => 'Giải thích bí mật không được lộ',
            'order_number' => 1,
        ]);

        $optHiddenA = QuizOption::create([
            'quiz_question_id' => $qHidden->id,
            'option_text' => 'Phương án đúng ẩn',
            'is_correct' => true,
        ]);

        // Thí sinh nộp bài quizHidden
        $submitHiddenResponse = $this->actingAs($takingUser)->post(route('student.quizzes.submit', $quizHidden->id), [
            'answers' => [$qHidden->id => $optHiddenA->id],
        ]);
        $attemptHidden = QuizAttempt::where('quiz_id', $quizHidden->id)->where('student_id', $takingUser->id)->first();
        $this->assertNotNull($attemptHidden);
        $submitHiddenResponse->assertRedirect(route('student.quizzes.result', [$quizHidden->id, $attemptHidden->id]));

        // Kiểm tra trang kết quả: CHỈ hiển thị điểm, KHÔNG hiển thị đối chiếu kết quả
        $resultHiddenResponse = $this->actingAs($takingUser)->get(route('student.quizzes.result', [$quizHidden->id, $attemptHidden->id]));
        $resultHiddenResponse->assertOk();
        $resultHiddenResponse->assertSee('10.0');
        $resultHiddenResponse->assertSee('/ 10 điểm');
        $resultHiddenResponse->assertSee('KẾT QUẢ: ĐẠT YÊU CẦU');
        $resultHiddenResponse->assertSee('Câu hỏi &amp; đáp án chi tiết không hiển thị', false);
        $resultHiddenResponse->assertDontSee('Đối chiếu đáp án chi tiết');
        $resultHiddenResponse->assertDontSee('Nội dung câu hỏi ẩn kết quả 123?');
        $resultHiddenResponse->assertDontSee('Giải thích bí mật không được lộ');

        // 2. Ngược lại: Tạo bài quiz với show_result_after_completion = true (Chọn "Có")
        $quizVisible = Quiz::create([
            'teacher_id' => $author->id,
            'title' => 'Đề thi hiển thị đối chiếu kết quả',
            'type' => 'quiz',
            'duration_minutes' => 20,
            'passing_score' => 5.0,
            'show_result_after_completion' => true,
            'status' => 'approved',
        ]);

        $qVisible = QuizQuestion::create([
            'quiz_id' => $quizVisible->id,
            'question_text' => 'Nội dung câu hỏi công khai 456?',
            'explanation' => 'Giải thích công khai cho thí sinh đối chiếu',
            'order_number' => 1,
        ]);

        $optVisibleA = QuizOption::create([
            'quiz_question_id' => $qVisible->id,
            'option_text' => 'Phương án đúng công khai',
            'is_correct' => true,
        ]);

        // Thí sinh nộp bài quizVisible
        $submitVisibleResponse = $this->actingAs($takingUser)->post(route('student.quizzes.submit', $quizVisible->id), [
            'answers' => [$qVisible->id => $optVisibleA->id],
        ]);
        $attemptVisible = QuizAttempt::where('quiz_id', $quizVisible->id)->where('student_id', $takingUser->id)->first();
        $this->assertNotNull($attemptVisible);
        $submitVisibleResponse->assertRedirect(route('student.quizzes.result', [$quizVisible->id, $attemptVisible->id]));

        // Kiểm tra trang kết quả: Hiển thị điểm VÀ hiển thị đầy đủ đối chiếu kết quả
        $resultVisibleResponse = $this->actingAs($takingUser)->get(route('student.quizzes.result', [$quizVisible->id, $attemptVisible->id]));
        $resultVisibleResponse->assertOk();
        $resultVisibleResponse->assertSee('10.0');
        $resultVisibleResponse->assertSee('/ 10 điểm');
        $resultVisibleResponse->assertSee('KẾT QUẢ: ĐẠT YÊU CẦU');
        $resultVisibleResponse->assertSee('Đối chiếu đáp án chi tiết');
        $resultVisibleResponse->assertSee('Nội dung câu hỏi công khai 456?');
        $resultVisibleResponse->assertSee('Giải thích công khai cho thí sinh đối chiếu');
    }
}
