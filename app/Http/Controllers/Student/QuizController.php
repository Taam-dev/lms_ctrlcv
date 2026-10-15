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
    public function index(Request $request)
    {
        $studentId = auth()->id();
        $search = trim((string) $request->query('search', ''));
        $quizId = $request->query('quiz_id');
        $type = $request->query('type');
        $scope = $request->query('scope');

        // Lấy danh sách ID các khóa học học viên đã đăng ký
        $enrolledCourseIds = Enrollment::where('student_id', $studentId)->pluck('course_id');

        // Các bài quiz đã duyệt: thuộc khóa học học viên đã đăng ký HOẶC bài quiz tự do (course_id IS NULL)
        // Riêng Quản trị viên (Admin) có quyền xem toàn bộ bài kiểm tra đã duyệt trong hệ thống
        $query = Quiz::with(['course.teacher', 'teacher'])
            ->withCount('questions')
            ->where('status', 'approved');

        if (! auth()->user()->isAdmin() && ! auth()->user()->isTeacher()) {
            $query->where(function ($query) use ($enrolledCourseIds) {
                $query->whereIn('course_id', $enrolledCourseIds)
                    ->orWhereNull('course_id');
            });
        }

        // Lọc chính xác bài kiểm tra theo ID nếu được chuyển hướng từ thông báo (tránh trùng tên bài giữa các khóa)
        if (! empty($quizId) && is_numeric($quizId)) {
            $query->where('id', (int) $quizId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($cq) use ($search) {
                        $cq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($type && in_array($type, ['quiz', 'midterm', 'final'])) {
            $query->where('type', $type);
        }

        if ($scope === 'free') {
            $query->whereNull('course_id');
        } elseif ($scope === 'course') {
            $query->whereNotNull('course_id');
        }

        $enrolledQuizzes = $query->latest()->get();

        // Lịch sử điểm cao nhất của học viên trên từng bài quiz
        $userAttempts = QuizAttempt::where('student_id', $studentId)
            ->latest()
            ->get()
            ->groupBy('quiz_id');

        return view('student.quizzes.index', compact('enrolledQuizzes', 'userAttempts', 'search', 'type', 'scope', 'quizId'));
    }

    /**
     * Giao diện làm bài kiểm tra trắc nghiệm
     */
    public function take(Quiz $quiz)
    {
        $quiz->loadMissing(['teacher', 'course.teacher']);

        // Kiểm tra bài quiz đã được admin duyệt chưa
        if ($quiz->status !== 'approved') {
            $redirectRoute = $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index');

            return redirect($redirectRoute)
                ->with('error', 'Bài kiểm tra này chưa được phê duyệt hoặc đang tạm khóa.');
        }

        // Kiểm tra thời gian làm bài (nếu có cấu hình hạn)
        if ($quiz->isNotStarted()) {
            $redirectRoute = $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index');

            return redirect($redirectRoute)
                ->with('error', 'Bài kiểm tra này chưa đến thời gian mở (Bắt đầu từ: '.$quiz->starts_at->format('d/m/Y H:i').').');
        }

        if ($quiz->isExpired()) {
            $redirectRoute = $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index');

            return redirect($redirectRoute)
                ->with('error', 'Bài kiểm tra này đã quá hạn làm bài (Hạn chót: '.$quiz->ends_at->format('d/m/Y H:i').'). Bạn không thể vào làm được nữa.');
        }

        // Kiểm tra bảo mật: Nếu gắn với khóa học thì học viên phải đăng ký khóa học (Giảng viên & Admin được quyền làm bài trực tiếp)
        if ($quiz->course_id && ! auth()->user()->isAdmin() && ! auth()->user()->isTeacher()) {
            $isEnrolled = Enrollment::where('student_id', auth()->id())
                ->where('course_id', $quiz->course_id)
                ->exists();

            if (! $isEnrolled) {
                return redirect()->route('student.courses.show', $quiz->course_id)
                    ->with('error', 'Bạn cần đăng ký khóa học để có thể tham gia làm bài kiểm tra trắc nghiệm này!');
            }
        }

        // Kiểm tra số lần làm lại bài kiểm tra tối đa (học viên bị chặn, Admin có quyền kiểm tra hệ thống)
        if (! auth()->user()->isAdmin() && $quiz->hasReachedMaxAttempts(auth()->id())) {
            $redirectRoute = $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index');

            return redirect($redirectRoute)
                ->with('error', 'Bạn đã sử dụng hết số lần làm bài kiểm tra này (tối đa '.$quiz->max_attempts.' lần).');
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
            $query->inRandomOrder();
        } else {
            $query->orderBy('order_number');
        }

        // Áp dụng giới hạn số lượng câu hỏi hiển thị cho mỗi lượt thi nếu có cấu hình
        if ($quiz->question_limit && $quiz->question_limit > 0) {
            $query->limit($quiz->question_limit);
        }

        $questions = $query->get();

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

        // Kiểm tra quá hạn làm bài
        if ($quiz->isExpired()) {
            abort(403, 'Bài kiểm tra này đã quá hạn nộp bài.');
        }

        // Kiểm tra điều kiện đăng ký nếu quiz thuộc về khóa học (Giảng viên & Admin có quyền làm bài)
        if ($quiz->course_id && ! auth()->user()->isAdmin() && ! auth()->user()->isTeacher()) {
            $isEnrolled = Enrollment::where('student_id', auth()->id())
                ->where('course_id', $quiz->course_id)
                ->exists();

            if (! $isEnrolled) {
                abort(403, 'Bạn cần đăng ký khóa học để có thể thực hiện bài kiểm tra này.');
            }
        }

        // Kiểm tra số lần làm lại bài kiểm tra tối đa
        if (! auth()->user()->isAdmin() && $quiz->hasReachedMaxAttempts(auth()->id())) {
            abort(403, 'Bạn đã sử dụng hết số lần làm bài kiểm tra này (tối đa '.$quiz->max_attempts.' lần).');
        }

        // Lấy danh sách câu hỏi đã được giao cho học viên trong lượt làm bài này
        $assignedQuestionIds = $request->input('question_ids', []);
        $submittedAnswers = $request->input('answers', []); // [question_id => selected_option_id]

        if (! empty($assignedQuestionIds)) {
            $questions = $quiz->questions()->whereIn('id', $assignedQuestionIds)->with('options')->get();
        } elseif (! empty($submittedAnswers)) {
            $questions = $quiz->questions()->whereIn('id', array_keys($submittedAnswers))->with('options')->get();
        } else {
            $questions = $quiz->questions()->with('options')->get();
        }

        $totalQuestions = $questions->count();
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
            'warning_count' => max(0, (int) $request->input('warning_count', 0)),
            'completed_at' => now(),
        ]);

        $successMessage = $quiz->show_result_after_completion
            ? 'Nộp bài kiểm tra thành công! Xem ngay kết quả chi tiết bên dưới.'
            : 'Nộp bài kiểm tra thành công! Điểm số của bạn đã được ghi nhận.';

        return redirect()->route('student.quizzes.result', [$quiz->id, $attempt->id])
            ->with('success', $successMessage);
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

        $quiz->load(['course.teacher', 'teacher', 'questions.options']);
        $attempt->load('student');

        return view('student.quizzes.result', compact('quiz', 'attempt'));
    }
}
