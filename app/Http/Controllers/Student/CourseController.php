<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    // trang chủ chỉ hiển thị hero banner và 3 khóa học nổi bật được nhiều lượt đánh giá nhất
    public function index()
    {
        $approvedCourses = Course::where('status', 'approved');

        $totalCourses = (clone $approvedCourses)->count();
        $courses = (clone $approvedCourses)->with('teacher')
            ->withCount(['lessons', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_count')
            ->orderByDesc('reviews_avg_rating')
            ->latest('id')
            ->take(3)
            ->get();

        $this->attachStudentProgress($courses);

        return view('welcome', compact('courses', 'totalCourses'));
    }

    // trang riêng liệt kê tất cả khóa học đã được admin duyệt
    public function list(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Course::where('status', 'approved')
            ->with('teacher')
            ->withCount(['lessons', 'reviews'])
            ->withAvg('reviews', 'rating');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $courses = $query->latest()
            ->latest('id')
            ->get();

        $this->attachStudentProgress($courses);

        return view('student.courses.index', compact('courses', 'search'));
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

        // Tải danh sách đánh giá & bình luận của khóa học
        $reviews = $course->reviews()
            ->with('user')
            ->latest()
            ->get();

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? round((float) $reviews->avg('rating'), 1) : 0.0;

        $ratingCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        $userReview = auth()->check() ? $reviews->firstWhere('user_id', auth()->id()) : null;

        return view('student.courses.show', compact(
            'course',
            'isEnrolled',
            'studentAttempts',
            'completedLessonIds',
            'unlockedLessonIds',
            'progressPercent',
            'reviews',
            'totalReviews',
            'averageRating',
            'ratingCounts',
            'userReview'
        ));
    }

    // đăng ký (enroll) hoặc mua khóa học bằng số dư ví
    public function enroll($id)
    {
        $user = auth()->user();
        $course = is_numeric($id)
            ? Course::where('id', $id)->first()
            : Course::where('slug', $id)->first();

        if (! $course) {
            $course = Course::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        }

        $isEnrolled = Enrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($isEnrolled) {
            return back()->with('info', 'Bạn đã đăng ký khóa học này rồi!');
        }

        // Nếu khóa học có phí
        if ($course->price > 0) {
            $currentBalance = $user->balance ?? 0;

            if ($currentBalance < $course->price) {
                $shortage = $course->price - $currentBalance;

                return back()
                    ->with('error', 'Số dư ví không đủ để mua khóa học này! Khóa học yêu cầu '.number_format($course->price, 0, ',', '.').' đ, số dư hiện tại của bạn là '.number_format($currentBalance, 0, ',', '.').' đ.')
                    ->with('need_deposit', true)
                    ->with('deposit_amount', $shortage);
            }

            DB::transaction(function () use ($user, $course) {
                $balanceBefore = $user->balance;
                $user->decrement('balance', $course->price);
                $balanceAfter = $user->fresh()->balance;

                $code = 'BUY'.$user->id.strtoupper(Str::random(4));

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'code' => $code,
                    'type' => 'course_purchase',
                    'amount' => $course->price,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => 'completed',
                    'description' => "Mua khóa học: {$course->title}",
                    'reference_id' => $course->id,
                    'payment_method' => 'wallet',
                ]);

                Enrollment::firstOrCreate([
                    'student_id' => $user->id,
                    'course_id' => $course->id,
                ]);
            });

            return back()->with('success', 'Mua và đăng ký khóa học thành công! Đã trừ '.number_format($course->price, 0, ',', '.').' đ từ ví của bạn.');
        }

        // Khóa học miễn phí
        Enrollment::firstOrCreate([
            'student_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return back()->with('success', 'Đã đăng ký khóa học miễn phí thành công!');
    }

    // xem bài giảng (chỉ cho ai đã đăng ký xem, theo thứ tự từ dưới lên, hỗ trợ cả slug chuẩn SEO và ID)
    public function viewLesson($courseId, $lessonId)
    {
        $userId = auth()->id();

        $course = is_numeric($courseId)
            ? Course::with('teacher')->where('id', $courseId)->first()
            : Course::with('teacher')->where('slug', $courseId)->first();

        if (! $course) {
            $course = Course::with('teacher')->where('id', $courseId)->orWhere('slug', $courseId)->firstOrFail();
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

    /**
     * Gửi hoặc cập nhật đánh giá & bình luận khóa học của học viên
     */
    public function review(Request $request, $idOrSlug)
    {
        $userId = auth()->id();

        $course = is_numeric($idOrSlug)
            ? Course::where('id', $idOrSlug)->first()
            : Course::where('slug', $idOrSlug)->first();

        if (! $course) {
            $course = Course::where('slug', $idOrSlug)->orWhere('id', $idOrSlug)->firstOrFail();
        }

        $isEnrolled = Enrollment::where('student_id', $userId)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            return back()->with('error', 'Bạn cần đăng ký khóa học trước khi gửi đánh giá và bình luận!');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá (từ 1 đến 5 sao).',
            'rating.integer' => 'Số sao đánh giá không hợp lệ.',
            'rating.min' => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Đánh giá tối đa là 5 sao.',
            'comment.max' => 'Nội dung bình luận không được vượt quá 2000 ký tự.',
        ]);

        CourseReview::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $userId,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => ! empty($validated['comment']) ? trim($validated['comment']) : null,
            ]
        );

        return back()->with('success', 'Cảm ơn bạn! Đánh giá khóa học đã được lưu thành công.');
    }

    /**
     * Xóa đánh giá khóa học (học viên sở hữu hoặc quản trị viên)
     */
    public function destroyReview($courseId, $reviewId)
    {
        $review = CourseReview::findOrFail($reviewId);

        if ($review->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác xóa đánh giá này.');
        }

        $review->delete();

        return back()->with('success', 'Đã xóa đánh giá thành công.');
    }
}
