<?php

namespace App\Services\Ai;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LmsContextService
{
    /**
     * Lấy toàn bộ danh sách khóa học đã duyệt với thông tin chi tiết phục vụ cho AI.
     *
     * @return Collection<int, Course>
     */
    public function getApprovedCourses(): Collection
    {
        return Course::where('status', 'approved')
            ->with([
                'teacher:id,name,avatar',
                'lessons' => fn ($q) => $q->select('id', 'course_id', 'title', 'order_number')->orderBy('order_number'),
            ])
            ->withCount(['lessons', 'reviews', 'quizzes'])
            ->withAvg('reviews', 'rating')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Tìm kiếm và xếp hạng các khóa học phù hợp nhất dựa trên từ khóa / câu hỏi của người dùng.
     *
     * @return array<int, array{
     *     id: int,
     *     title: string,
     *     slug: string,
     *     url: string,
     *     price: int,
     *     formatted_price: string,
     *     is_free: bool,
     *     teacher: string,
     *     description: string,
     *     lessons_count: int,
     *     reviews_count: int,
     *     average_rating: float,
     *     key_lessons: array<int, string>,
     *     match_score: int
     * }>
     */
    public function findRelevantCourses(string $query, ?User $user = null, int $limit = 5): array
    {
        $courses = $this->getApprovedCourses();
        if ($courses->isEmpty()) {
            return [];
        }

        $queryLower = mb_strtolower(trim($query), 'UTF-8');
        $queryAscii = Str::ascii($queryLower);
        $keywords = array_filter(explode(' ', $queryAscii), fn ($k) => strlen($k) > 1);

        $isLookingForFree = str_contains($queryLower, 'miễn phí')
            || str_contains($queryLower, 'free')
            || str_contains($queryLower, '0 đ')
            || str_contains($queryLower, 'không tốn tiền');

        $isLookingForPaid = str_contains($queryLower, 'có phí')
            || str_contains($queryLower, 'trả phí')
            || str_contains($queryLower, 'mua');

        $scored = [];

        foreach ($courses as $course) {
            $score = 0;
            $titleLower = mb_strtolower($course->title, 'UTF-8');
            $titleAscii = Str::ascii($titleLower);
            $descLower = mb_strtolower($course->description ?? '', 'UTF-8');
            $descAscii = Str::ascii($descLower);
            $teacherName = $course->teacher?->name ?? 'Ban Đào Tạo';
            $teacherAscii = Str::ascii(mb_strtolower($teacherName, 'UTF-8'));

            // Khớp chính xác cụm từ tìm kiếm
            if (str_contains($titleLower, $queryLower) || str_contains($titleAscii, $queryAscii)) {
                $score += 50;
            }
            if (str_contains($descLower, $queryLower) || str_contains($descAscii, $queryAscii)) {
                $score += 25;
            }

            // Khớp từng từ khóa
            foreach ($keywords as $kw) {
                if (str_contains($titleAscii, $kw)) {
                    $score += 15;
                }
                if (str_contains($descAscii, $kw)) {
                    $score += 5;
                }
                if (str_contains($teacherAscii, $kw)) {
                    $score += 10;
                }

                // Kiểm tra tên các bài giảng trong khóa học
                foreach ($course->lessons as $lesson) {
                    $lessonAscii = Str::ascii(mb_strtolower($lesson->title, 'UTF-8'));
                    if (str_contains($lessonAscii, $kw)) {
                        $score += 6;
                        break;
                    }
                }
            }

            // Tiêu chí lọc giá
            if ($isLookingForFree) {
                if ($course->is_free) {
                    $score += 30;
                } else {
                    $score -= 20;
                }
            } elseif ($isLookingForPaid) {
                if (! $course->is_free) {
                    $score += 20;
                }
            }

            // Ưu tiên khóa học có đánh giá cao
            $avgRating = round((float) ($course->reviews_avg_rating ?? 0), 1);
            if ($avgRating >= 4.0) {
                $score += 3;
            }

            $lessonTitles = $course->lessons->take(5)->pluck('title')->toArray();

            $scored[] = [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug ?? (string) $course->id,
                'url' => $course->url,
                'price' => (int) $course->price,
                'formatted_price' => $course->formatted_price,
                'is_free' => (bool) $course->is_free,
                'teacher' => $teacherName,
                'description' => Str::limit(strip_tags($course->description ?? ''), 160),
                'lessons_count' => (int) ($course->lessons_count ?? $course->lessons->count()),
                'reviews_count' => (int) ($course->reviews_count ?? 0),
                'average_rating' => $avgRating,
                'key_lessons' => $lessonTitles,
                'match_score' => $score,
            ];
        }

        // Sắp xếp theo điểm khớp giảm dần
        usort($scored, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);

        // Nếu điểm cao nhất > 0, chỉ lấy những kết quả có liên quan, ngược lại lấy các khóa học nổi bật
        if (! empty($scored) && $scored[0]['match_score'] > 0) {
            $filtered = array_filter($scored, fn ($item) => $item['match_score'] > 0);

            return array_slice(array_values($filtered), 0, $limit);
        }

        return array_slice($scored, 0, $limit);
    }

    /**
     * Lấy dữ liệu học tập cá nhân hóa của học viên đang đăng nhập.
     *
     * @return array{
     *     is_authenticated: bool,
     *     user_name?: string,
     *     user_role?: string,
     *     wallet_balance?: string,
     *     enrolled_count?: int,
     *     enrolled_courses?: array<int, array{
     *         id: int,
     *         title: string,
     *         url: string,
     *         progress_percent: int,
     *         completed_lessons: int,
     *         total_lessons: int,
     *         next_lesson: ?array{id: int, title: string, url: string}
     *     }>,
     *     quiz_attempts_count?: int
     * }
     */
    public function getUserLearningProgress(?User $user): array
    {
        if (! $user) {
            return [
                'is_authenticated' => false,
            ];
        }

        $enrollments = Enrollment::where('student_id', $user->id)
            ->with([
                'course.lessons' => fn ($q) => $q->orderBy('order_number'),
            ])
            ->latest()
            ->get();

        $completedLessonIds = LessonCompletion::where('student_id', $user->id)
            ->pluck('lesson_id')
            ->flip()
            ->toArray();

        $enrolledCoursesData = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (! $course) {
                continue;
            }

            $totalLessons = $course->lessons->count();
            $completedCount = 0;
            $nextLesson = null;

            foreach ($course->lessons as $lesson) {
                if (isset($completedLessonIds[$lesson->id])) {
                    $completedCount++;
                } elseif ($nextLesson === null) {
                    $nextLesson = [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'url' => route('student.lessons.friendly', ['courseId' => $course->id, 'lessonId' => $lesson->id]),
                    ];
                }
            }

            $progressPercent = $totalLessons > 0 ? (int) round(($completedCount / $totalLessons) * 100) : 0;

            $enrolledCoursesData[] = [
                'id' => $course->id,
                'title' => $course->title,
                'url' => $course->url,
                'progress_percent' => $progressPercent,
                'completed_lessons' => $completedCount,
                'total_lessons' => $totalLessons,
                'next_lesson' => $nextLesson,
            ];
        }

        $quizAttemptsCount = QuizAttempt::where('student_id', $user->id)->count();

        return [
            'is_authenticated' => true,
            'user_name' => $user->name,
            'user_role' => $user->role_label ?? 'Học viên',
            'wallet_balance' => $user->formatted_balance ?? '0 đ',
            'enrolled_count' => count($enrolledCoursesData),
            'enrolled_courses' => $enrolledCoursesData,
            'quiz_attempts_count' => $quizAttemptsCount,
        ];
    }

    /**
     * Tạo tóm tắt tổng quan về toàn bộ thư viện khóa học trên hệ thống.
     *
     * @return array{
     *     total_courses: int,
     *     free_courses_count: int,
     *     paid_courses_count: int,
     *     courses_list: array<int, array{id: int, title: string, price: string, teacher: string, url: string}>
     * }
     */
    public function getCatalogSummary(): array
    {
        $courses = $this->getApprovedCourses();

        $list = [];
        $freeCount = 0;
        $paidCount = 0;

        foreach ($courses as $c) {
            if ($c->is_free) {
                $freeCount++;
            } else {
                $paidCount++;
            }

            $list[] = [
                'id' => $c->id,
                'title' => $c->title,
                'price' => $c->formatted_price,
                'teacher' => $c->teacher?->name ?? 'Ban Đào Tạo',
                'url' => $c->url,
                'lessons_count' => (int) ($c->lessons_count ?? $c->lessons->count()),
            ];
        }

        return [
            'total_courses' => $courses->count(),
            'free_courses_count' => $freeCount,
            'paid_courses_count' => $paidCount,
            'courses_list' => $list,
        ];
    }

    /**
     * Xây dựng System Prompt chuẩn mực cho AI với toàn bộ dữ liệu RAG và quy tắc Zero-Hallucination.
     */
    public function buildSystemPrompt(?User $user, array $relevantCourses, array $catalogSummary, array $userProgress): string
    {
        $coursesJson = json_encode($relevantCourses, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $catalogSummaryJson = json_encode($catalogSummary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $userProgressJson = json_encode($userProgress, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Bạn là Trợ lý AI Cố vấn Học tập thông minh của nền tảng trực tuyến "Ctrl C+V" (LMS).
Nhiệm vụ của bạn là tư vấn khóa học, giải đáp thắc mắc, hướng dẫn lộ trình học tập và đồng hành cùng học viên.

=== QUY TẮC CỐT LÕI (BẮT BUỘC TUÂN THỦ NGHIÊM NGẶT) ===
1. ZERO HALLUCINATION (CHỐNG BỊA ĐẶT TUYỆT ĐỐI):
   - Bạn CHỈ ĐƯỢC PHÉP giới thiệu và gợi ý các khóa học THỰC SỰ TỒN TẠI trong cơ sở dữ liệu được cung cấp dưới đây.
   - KHÔNG ĐƯỢC tự bịa ra khóa học, tên giảng viên, hoặc giá tiền không có trong dữ liệu.
   - Nếu người dùng tìm kiếm chủ đề mà hệ thống chưa có khóa học, hãy nhã nhặn giải thích rằng Ctrl C+V hiện chưa có khóa học về chủ đề đó, sau đó gợi ý các khóa học gần gũi nhất hoặc giới thiệu các khóa học hiện có.

2. ĐÍNH KÈM LIÊN KẾT HÀNH ĐỘNG (ACTIONABLE LINKS):
   - Mỗi khi nhắc đến tên bất kỳ khóa học nào, BẮT BUỘC phải kèm theo đường dẫn Markdown chuẩn xác: [Tên khóa học](url) lấy đúng từ trường 'url' trong dữ liệu.
   - Nếu nhắc đến bài học tiếp theo của người dùng, gắn link: [Tên bài học](url bài học).
   - Nếu người dùng hỏi về thi thử hoặc làm quiz, hãy gắn link trang trắc nghiệm: [Thi Trắc Nghiệm / Quizzes]({$this->getQuizzesUrl()}).

3. NGÔN NGỮ & PHONG CÁCH:
   - Sử dụng tiếng Việt tự nhiên, thân thiện, lịch sự, nhiệt tình và mang tính sư phạm cao.
   - Trình bày mạch lạc, dễ đọc với gạch đầu dòng, in đậm tên khóa học, học phí, số bài giảng.
   - Luôn đưa ra lời khuyên học tập thực tế và lộ trình từng bước (bắt đầu từ cơ bản đến nâng cao).

4. THÔNG TIN CÁ NHÂN HÓA (AUTH-AWARE):
   - Xem mục "DỮ LIỆU HỌC TẬP CỦA NGƯỜI DÙNG" bên dưới.
   - Nếu người dùng đã đăng nhập, hãy chào họ bằng tên (nếu phù hợp), cập nhật tiến độ học tập các khóa họ đã mua/đăng ký, và khích lệ họ học tiếp bài học tiếp theo.
   - Nếu người dùng chưa đăng nhập, nhắc nhẹ rằng họ có thể đăng nhập để lưu vết tiến độ học tập.

=== DỮ LIỆU HỆ THỐNG HIỆN TẠI (REAL-TIME LMS DATA) ===
--- CÁC KHÓA HỌC PHÙ HỢP VỚI CÂU HỎI HIỆN TẠI ---
{$coursesJson}

--- TỔNG QUAN DANH MỤC KHÓA HỌC TOÀN HỆ THỐNG ---
{$catalogSummaryJson}

--- DỮ LIỆU HỌC TẬP CỦA NGƯỜI DÙNG ---
{$userProgressJson}
PROMPT;

        return $prompt;
    }

    /**
     * URL danh sách trắc nghiệm sinh viên
     */
    private function getQuizzesUrl(): string
    {
        return route('student.quizzes.index');
    }
}
