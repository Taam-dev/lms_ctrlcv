<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Gắn thông tin đăng ký và phần trăm tiến độ của học viên vào danh sách khóa học
     */
    private function attachStudentProgress($courses): void
    {
        if (! auth()->check()) {
            return;
        }

        $userId = auth()->id();
        $enrolledCourseIds = Enrollment::where('student_id', $userId)
            ->pluck('course_id')
            ->flip();

        $completedCounts = LessonCompletion::where('student_id', $userId)
            ->groupBy('course_id')
            ->selectRaw('course_id, count(*) as total_completed')
            ->pluck('total_completed', 'course_id');

        foreach ($courses as $course) {
            $isEnrolled = isset($enrolledCourseIds[$course->id]);
            $course->is_enrolled = $isEnrolled;

            $totalLessons = $course->lessons_count ?? $course->lessons->count();
            $completed = (int) ($completedCounts[$course->id] ?? 0);

            $course->completed_lessons_count = $completed;
            $course->progress_percent = ($totalLessons > 0)
                ? (int) round(($completed / $totalLessons) * 100)
                : 0;
        }
    }

    // trang chủ chỉ hiển thị hero banner và 3 khóa học nổi bật mới nhất
    public function index()
    {
        $approvedCourses = Course::where('status', 'approved');

        $totalCourses = (clone $approvedCourses)->count();
        $courses = (clone $approvedCourses)->with('teacher')->withCount('lessons')->latest()->latest('id')->take(3)->get();

        $this->attachStudentProgress($courses);

        return view('welcome', compact('courses', 'totalCourses'));
    }

    // trang riêng liệt kê tất cả khóa học đã được admin duyệt
    public function list()
    {
        $courses = Course::where('status', 'approved')
            ->with('teacher')
            ->withCount('lessons')
            ->latest()
            ->latest('id')
            ->get();

        $this->attachStudentProgress($courses);

        return view('student.courses.index', compact('courses'));
    }

    /**
     * Xác định danh sách ID các bài học đã được mở khóa theo thứ tự từ dưới lên (bài 1 trở đi)
     */
    private function getUnlockedLessonIds($allLessons, array $completedLessonIds): array
    {
        $unlockedIds = [];

        foreach ($allLessons as $index => $item) {
            // Bài học đầu tiên luôn được mở khóa
            if ($index === 0) {
                $unlockedIds[] = $item->id;

                continue;
            }

            // Bài học đã hoàn thành thì luôn được mở khóa để ôn tập
            if (in_array($item->id, $completedLessonIds)) {
                $unlockedIds[] = $item->id;

                continue;
            }

            // Bài học được mở khóa nếu bài học liền trước đã được hoàn thành
            $prevLesson = $allLessons[$index - 1];
            if (in_array($prevLesson->id, $completedLessonIds)) {
                $unlockedIds[] = $item->id;
            }
        }

        return $unlockedIds;
    }

    // xem chi tiết khóa học (hỗ trợ cả slug chuẩn SEO và ID)
    public function show($idOrSlug)
    {
        $query = Course::with(['teacher', 'lessons' => function ($q) {
            $q->orderBy('order_number');
        }, 'approvedQuizzes.questions']);

        if (is_numeric($idOrSlug)) {
            $course = (clone $query)->where('id', $idOrSlug)->first();
        } else {
            $course = (clone $query)->where('slug', $idOrSlug)->first();
        }

        if (! $course) {
            $course = (clone $query)->where('slug', $idOrSlug)->orWhere('id', $idOrSlug)->firstOrFail();
        }

        // kiểm tra học viên đã đăng ký khóa này chưa
        $isEnrolled = false;
        $studentAttempts = collect();
        $completedLessonIds = [];
        $unlockedLessonIds = [];
        $progressPercent = 0;

        if (auth()->check()) {
            $userId = auth()->id();
            $isEnrolled = Enrollment::where('student_id', $userId)
                ->where('course_id', $course->id)
                ->exists();

            $studentAttempts = QuizAttempt::where('student_id', $userId)
                ->whereIn('quiz_id', $course->approvedQuizzes->pluck('id'))
                ->latest()
                ->get()
                ->groupBy('quiz_id');

            if ($isEnrolled) {
                $completedLessonIds = LessonCompletion::where('student_id', $userId)
                    ->where('course_id', $course->id)
                    ->pluck('lesson_id')
                    ->toArray();

                $unlockedLessonIds = $this->getUnlockedLessonIds($course->lessons, $completedLessonIds);

                $totalLessons = $course->lessons->count();
                $progressPercent = ($totalLessons > 0)
                    ? (int) round((count($completedLessonIds) / $totalLessons) * 100)
                    : 0;
            }
        }

        return view('student.courses.show', compact(
            'course',
            'isEnrolled',
            'studentAttempts',
            'completedLessonIds',
            'unlockedLessonIds',
            'progressPercent'
        ));
    }

    // đăng ký (enroll) khóa học
    public function enroll($id)
    {
        Enrollment::firstOrCreate([
            'student_id' => auth()->id(),
            'course_id' => $id,
        ]);

        return back()->with('success', 'đã đăng ký khóa học thành công!');
    }

    // xem bài giảng (chỉ cho ai đã đăng ký xem, theo thứ tự từ dưới lên, hỗ trợ cả slug chuẩn SEO và ID)
    public function viewLesson($courseId, $lessonId)
    {
        $userId = auth()->id();

        $course = is_numeric($courseId)
            ? Course::where('id', $courseId)->first()
            : Course::where('slug', $courseId)->first();

        if (! $course) {
            $course = Course::where('id', $courseId)->orWhere('slug', $courseId)->firstOrFail();
        }

        // check bảo mật: phải đăng ký rồi mới cho vào xem
        $isEnrolled = Enrollment::where('student_id', $userId)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            $redirectRoute = is_numeric($courseId) ? route('student.courses.show', $courseId) : route('courses.show', $course->slug ?: $course->id);

            return redirect()->to($redirectRoute)->with('error', 'bạn phải đăng ký khóa học mới xem được bài giảng nha!');
        }

        $allLessons = Lesson::where('course_id', $course->id)->orderBy('order_number')->get();
        $lesson = is_numeric($lessonId)
            ? $allLessons->firstWhere('id', (int) $lessonId)
            : $allLessons->firstWhere('slug', $lessonId);

        if (! $lesson) {
            $lesson = $allLessons->first(fn ($l) => (string) $l->id === (string) $lessonId || $l->slug === $lessonId);
        }

        if (! $lesson) {
            abort(404);
        }

        $completedLessonIds = LessonCompletion::where('student_id', $userId)
            ->where('course_id', $course->id)
            ->pluck('lesson_id')
            ->toArray();

        $unlockedLessonIds = $this->getUnlockedLessonIds($allLessons, $completedLessonIds);

        // Kiểm tra học viên có được phép vào bài này theo thứ tự tuần tự không
        if (! in_array($lesson->id, $unlockedLessonIds)) {
            // Chuyển hướng tới bài chưa hoàn thành gần nhất mà học viên được phép học
            $nextAvailableLesson = $allLessons->first(fn ($l) => ! in_array($l->id, $completedLessonIds) && in_array($l->id, $unlockedLessonIds));
            $targetLesson = $nextAvailableLesson ?: $allLessons->first();
            $targetParam = is_numeric($lessonId) ? $targetLesson->id : ($targetLesson->slug ?: $targetLesson->id);
            $courseParam = is_numeric($courseId) ? $course->id : ($course->slug ?: $course->id);

            return redirect()->route('student.lessons.show', [$courseParam, $targetParam])
                ->with('error', 'Bạn phải học theo thứ tự từ bài trước lên bài sau! Vui lòng hoàn thành bài học trước đó.');
        }

        $isCompleted = in_array($lesson->id, $completedLessonIds);
        $totalLessons = $allLessons->count();
        $completedCount = count($completedLessonIds);
        $progressPercent = ($totalLessons > 0)
            ? (int) round(($completedCount / $totalLessons) * 100)
            : 0;

        return view('student.lessons.show', compact(
            'course',
            'lesson',
            'allLessons',
            'completedLessonIds',
            'unlockedLessonIds',
            'isCompleted',
            'progressPercent',
            'completedCount',
            'totalLessons'
        ));
    }

    // đánh dấu hoàn thành bài giảng (không cho phép hủy hoàn thành)
    public function completeLesson(Request $request, $courseId, $lessonId)
    {
        $userId = auth()->id();

        $course = is_numeric($courseId)
            ? Course::where('id', $courseId)->first()
            : Course::where('slug', $courseId)->first();

        if (! $course) {
            $course = Course::where('id', $courseId)->orWhere('slug', $courseId)->firstOrFail();
        }

        $isEnrolled = Enrollment::where('student_id', $userId)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            $redirectRoute = is_numeric($courseId) ? route('student.courses.show', $courseId) : route('courses.show', $course->slug ?: $course->id);

            return redirect()->to($redirectRoute)->with('error', 'bạn phải đăng ký khóa học mới thực hiện được thao tác này!');
        }

        $allLessons = Lesson::where('course_id', $course->id)->orderBy('order_number')->get();
        $lesson = is_numeric($lessonId)
            ? $allLessons->firstWhere('id', (int) $lessonId)
            : $allLessons->firstWhere('slug', $lessonId);

        if (! $lesson) {
            $lesson = $allLessons->first(fn ($l) => (string) $l->id === (string) $lessonId || $l->slug === $lessonId);
        }

        if (! $lesson) {
            abort(404);
        }

        $completedLessonIds = LessonCompletion::where('student_id', $userId)
            ->where('course_id', $course->id)
            ->pluck('lesson_id')
            ->toArray();

        $unlockedLessonIds = $this->getUnlockedLessonIds($allLessons, $completedLessonIds);

        // Chỉ cho phép hoàn thành bài học đã được mở khóa theo thứ tự
        if (! in_array($lesson->id, $unlockedLessonIds)) {
            return back()->with('error', 'Bạn không thể hoàn thành bài này vì chưa hoàn thành bài học trước đó!');
        }

        // Không cho phép hủy hoàn thành bài học
        $completion = LessonCompletion::where('student_id', $userId)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $completion) {
            LessonCompletion::create([
                'student_id' => $userId,
                'lesson_id' => $lesson->id,
                'course_id' => $course->id,
                'completed_at' => now(),
            ]);
            $message = 'Chúc mừng! Bạn đã hoàn thành bài học này.';
        } else {
            $message = 'Bài học này đã được hoàn thành trước đó.';
        }

        if ($request->filled('next_lesson_id')) {
            $nextLesson = $allLessons->firstWhere('id', (int) $request->input('next_lesson_id'));
            $nextTarget = is_numeric($lessonId) ? ($nextLesson?->id ?? $request->input('next_lesson_id')) : ($nextLesson?->slug ?? $nextLesson?->id ?? $request->input('next_lesson_id'));
            $courseTarget = is_numeric($courseId) ? $course->id : ($course->slug ?: $course->id);

            return redirect()->route('student.lessons.show', [$courseTarget, $nextTarget])
                ->with('success', $message);
        }

        return back()->with('success', $message);
    }
}
