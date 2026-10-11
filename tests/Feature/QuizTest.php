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
}
