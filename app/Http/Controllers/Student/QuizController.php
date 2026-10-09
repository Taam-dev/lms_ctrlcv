<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Danh sách các bài kiểm tra trắc nghiệm dành cho học viên
     */
    public function index()
    {
        $studentId = auth()->id();

        // Lấy danh sách ID các khóa học học viên đã đăng ký
        $enrolledCourseIds = Enrollment::where('student_id', $studentId)->pluck('course_id');

        // Các bài quiz đã duyệt: thuộc khóa học học viên đã đăng ký HOẶC bài quiz tự do (course_id IS NULL)
        $enrolledQuizzes = Quiz::with(['course.teacher', 'teacher'])
            ->withCount('questions')
            ->where('status', 'approved')
            ->where(function ($query) use ($enrolledCourseIds) {
                $query->whereIn('course_id', $enrolledCourseIds)
                    ->orWhereNull('course_id');
            })
            ->latest()
            ->get();

        // Lịch sử điểm cao nhất của học viên trên từng bài quiz
        $userAttempts = QuizAttempt::where('student_id', $studentId)
            ->latest()
            ->get()
            ->groupBy('quiz_id');

        return view('student.quizzes.index', compact('enrolledQuizzes', 'userAttempts'));
    }

    /**
     * Giao diện làm bài kiểm tra trắc nghiệm
     */
    public function take(Quiz $quiz)
    {
        // Kiểm tra bài quiz đã được admin duyệt chưa
        if ($quiz->status !== 'approved') {
            $redirectRoute = $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index');

            return redirect($redirectRoute)
                ->with('error', 'Bài kiểm tra này chưa được phê duyệt hoặc đang tạm khóa.');
        }

        // Kiểm tra bảo mật: Nếu gắn với khóa học thì học viên phải đăng ký khóa học mới được làm quiz
        if ($quiz->course_id) {
            $isEnrolled = Enrollment::where('student_id', auth()->id())
                ->where('course_id', $quiz->course_id)
                ->exists();

            if (! $isEnrolled) {
                return redirect()->route('student.courses.show', $quiz->course_id)
                    ->with('error', 'Bạn cần đăng ký khóa học để có thể tham gia làm bài kiểm tra trắc nghiệm này!');
            }
        }

        // Kiểm tra bài quiz đã có câu hỏi chưa
        $questionCount = $quiz->questions()->count();
        if ($questionCount === 0) {
            $redirectRoute = $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index');

            return redirect($redirectRoute)
                ->with('error', 'Bài kiểm tra này hiện chưa có câu hỏi nào. Vui lòng quay lại sau.');
        }

        // Tải câu hỏi: Tùy chỉnh đảo ngẫu nhiên thứ tự câu hỏi nếu giảng viên bật randomize_questions
        $query = $quiz->questions()->with('options');
        if ($quiz->randomize_questions) {
            $questions = $query->inRandomOrder()->get();
        } else {
            $questions = $query->orderBy('order_number')->get();
        }

        return view('student.quizzes.take', compact('quiz', 'questions'));
    }

    /**
     * Nộp bài kiểm tra, chấm điểm tự động và lưu kết quả
     */
    public function submit(Request $request, Quiz $quiz)
    {
        if ($quiz->status !== 'approved') {
            abort(403, 'Bài kiểm tra này chưa được phê duyệt hoặc đang tạm khóa.');
        }

        // Kiểm tra điều kiện đăng ký nếu quiz thuộc về khóa học
        if ($quiz->course_id) {
            $isEnrolled = Enrollment::where('student_id', auth()->id())
                ->where('course_id', $quiz->course_id)
                ->exists();

            if (! $isEnrolled) {
                abort(403, 'Bạn cần đăng ký khóa học để có thể thực hiện bài kiểm tra này.');
            }
        }

        // Lấy tất cả câu hỏi và đáp án đúng
        $questions = $quiz->questions()->with('options')->get();
        $totalQuestions = $questions->count();
        $submittedAnswers = $request->input('answers', []); // [question_id => selected_option_id]

        $correctAnswers = 0;

        foreach ($questions as $question) {
            $selectedOptionId = $submittedAnswers[$question->id] ?? null;

            if ($selectedOptionId) {
                // Kiểm tra xem đáp án đã chọn có phải là đáp án đúng không
                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($correctOption && (int) $correctOption->id === (int) $selectedOptionId) {
                    $correctAnswers++;
                }
            }
        }

        // Tính điểm theo thang điểm 10
        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 10, 2) : 0;
        $isPassed = $score >= $quiz->passing_score;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => auth()->id(),
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score' => $score,
            'is_passed' => $isPassed,
            'answers' => $submittedAnswers,
            'completed_at' => now(),
        ]);

        return redirect()->route('student.quizzes.result', [$quiz->id, $attempt->id])
            ->with('success', 'Nộp bài kiểm tra thành công! Xem ngay kết quả chi tiết bên dưới.');
    }

    /**
     * Xem kết quả chi tiết bài làm kèm lời giải thích
     */
    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        // Kiểm tra quyền xem kết quả: Học viên làm bài hoặc Giảng viên/Admin
        if ($attempt->student_id !== auth()->id() && auth()->user()->role === 'student') {
            abort(403, 'Bạn không có quyền xem kết quả bài làm này.');
        }

        $quiz->load(['course', 'questions.options']);

        return view('student.quizzes.result', compact('quiz', 'attempt'));
    }
}
