<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Bảng điều khiển dành riêng cho Giảng viên (Teacher Portal / Panel)
     * Quản lý các khóa học, danh sách bài giảng theo khóa học, và bài kiểm tra (quizzes) do giảng viên tạo.
     */
    public function index(Request $request)
    {
        $teacherId = auth()->id();
        $currentTab = $request->query('tab', 'courses');
        $statusFilter = $request->query('status');
        $selectedCourseId = $request->query('course_id');
        $search = trim((string) $request->query('search', ''));

        // Lấy danh sách ID các khóa học của giảng viên
        $teacherCourseIds = Course::where('teacher_id', $teacherId)->pluck('id');
        $teacherQuizIds = Quiz::where('teacher_id', $teacherId)->pluck('id');

        // Thống kê số liệu riêng cho Giảng viên
        $coursesQuery = Course::where('teacher_id', $teacherId);
        $quizzesQuery = Quiz::where('teacher_id', $teacherId);

        $stats = [
            'total_courses' => (clone $coursesQuery)->count(),
            'pending_courses' => (clone $coursesQuery)->where('status', 'pending')->count(),
            'approved_courses' => (clone $coursesQuery)->where('status', 'approved')->count(),
            'rejected_courses' => (clone $coursesQuery)->where('status', 'rejected')->count(),

            'total_lessons' => Lesson::whereIn('course_id', $teacherCourseIds)->count(),

            'total_quizzes' => (clone $quizzesQuery)->count(),
            'pending_quizzes' => (clone $quizzesQuery)->where('status', 'pending')->count(),
            'approved_quizzes' => (clone $quizzesQuery)->where('status', 'approved')->count(),
            'rejected_quizzes' => (clone $quizzesQuery)->where('status', 'rejected')->count(),

            'total_enrollments' => Enrollment::whereIn('course_id', $teacherCourseIds)->count(),
            'total_attempts' => QuizAttempt::whereIn('quiz_id', $teacherQuizIds)->count(),
        ];

        // Lấy danh sách Khóa học kèm Bài giảng được sắp xếp theo thứ tự
        $coursesBuilder = Course::with([
            'lessons' => function ($query) {
                $query->orderBy('order_number', 'asc');
            },
            'quizzes',
        ])
            ->withCount(['lessons', 'quizzes', 'enrollments'])
            ->where('teacher_id', $teacherId)
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $coursesBuilder->where('status', $statusFilter);
        }

        if ($search !== '') {
            $coursesBuilder->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('lessons', function ($l) use ($search) {
                        $l->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
            });
        }

        $courses = $coursesBuilder->get();

        // Lấy danh sách Bài kiểm tra (Quizzes)
        $quizzesBuilder = Quiz::with('course')
            ->withCount(['questions', 'attempts'])
            ->where('teacher_id', $teacherId)
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $quizzesBuilder->where('status', $statusFilter);
        }

        if ($search !== '') {
            $quizzesBuilder->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($c) use ($search) {
                        $c->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $quizzes = $quizzesBuilder->get();

        // Lấy danh sách Bài giảng của các khóa học thuộc giảng viên
        $lessonsQuery = Lesson::with('course')
            ->whereIn('course_id', $teacherCourseIds)
            ->orderBy('course_id')
            ->orderBy('order_number', 'asc');

        if ($selectedCourseId && $teacherCourseIds->contains($selectedCourseId)) {
            $lessonsQuery->where('course_id', $selectedCourseId);
        }

        if ($search !== '') {
            $lessonsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($c) use ($search) {
                        $c->where('title', 'like', "%{$search}%");
                    });
            });
        }
        $lessons = $lessonsQuery->get();

        // Lấy danh sách kết quả học viên làm bài trắc nghiệm
        $attemptsQuery = QuizAttempt::with(['student', 'quiz.course'])
            ->whereIn('quiz_id', $teacherQuizIds)
            ->latest();

        if ($search !== '') {
            $lowerSearch = mb_strtolower($search);

            // Xử lý tìm kiếm theo điểm số:
            $scoreOperator = null;
            $scoreValue = null;
            $cleanScoreSearch = trim(preg_replace('/^(?:điểm|diem)\s*:?\s*/iu', '', $search));

            // 1. Dạng so sánh: ">=8", ">= 8", "<= 5", "> 7.5", "< 5", "= 9"
            if (preg_match('/^(>=|<=|>|<|=)\s*([0-9]+(?:\.[0-9]+)?)(?:\s*\/\s*10)?$/', $cleanScoreSearch, $matches)) {
                $scoreOperator = $matches[1];
                $scoreValue = (float) $matches[2];
            }
            // 2. Dạng điểm số đơn hoặc phân số: "5", "5.0", "8.5", "5/10", "8.5/10"
            elseif (preg_match('/^([0-9]+(?:\.[0-9]+)?)(?:\s*\/\s*10)?$/', $cleanScoreSearch, $matches)) {
                $scoreOperator = '=';
                $scoreValue = (float) $matches[1];
            }

            // 3. Dạng số câu đúng: "5 câu", "8 câu"
            $correctAnswersCount = null;
            if (preg_match('/^([0-9]+)\s*(?:câu|cau)$/iu', $cleanScoreSearch, $matches)) {
                $correctAnswersCount = (int) $matches[1];
            }

            // 4. Dạng kết quả Đạt / Không đạt
            $statusPassed = null;
            if (str_contains($lowerSearch, 'không đạt') || str_contains($lowerSearch, 'chưa đạt') || $lowerSearch === 'fail' || $lowerSearch === 'truot') {
                $statusPassed = false;
            } elseif (str_contains($lowerSearch, 'đạt') || $lowerSearch === 'dat' || $lowerSearch === 'pass') {
                $statusPassed = true;
            }

            $attemptsQuery->where(function ($q) use ($search, $scoreOperator, $scoreValue, $correctAnswersCount, $statusPassed) {
                // Tìm theo học viên
                $q->whereHas('student', function ($s) use ($search) {
                    $s->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                // Tìm theo bài kiểm tra và khóa học
                    ->orWhereHas('quiz', function ($qz) use ($search) {
                        $qz->where('title', 'like', "%{$search}%")
                            ->orWhereHas('course', function ($c) use ($search) {
                                $c->where('title', 'like', "%{$search}%");
                            });
                    });

                // Tìm theo điểm số (chính xác hoặc so sánh)
                if ($scoreOperator !== null && $scoreValue !== null) {
                    $q->orWhere('score', $scoreOperator, $scoreValue);
                }

                // Tìm theo số câu đúng
                if ($correctAnswersCount !== null) {
                    $q->orWhere('correct_answers', $correctAnswersCount);
                }

                // Tìm theo trạng thái Đạt / Không đạt
                if ($statusPassed !== null) {
                    $q->orWhere('is_passed', $statusPassed);
                }
            });
        }

        $attempts = $attemptsQuery->paginate(15);

        return view('teacher.dashboard', compact(
            'stats',
            'courses',
            'quizzes',
            'lessons',
            'attempts',
            'currentTab',
            'statusFilter',
            'selectedCourseId',
            'search'
        ));
    }
}
