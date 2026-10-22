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
            ->withCount(['lessons', 'reviews', 'quizzes', 'enrollments'])
            ->withAvg('reviews', 'rating')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Bóc tách số lượng khóa học mà người dùng yêu cầu (ví dụ: "1 khóa", "top 3", "duy nhất").
     */
    public function parseRequestedLimit(string $query): int
    {
        $lower = mb_strtolower(trim($query), 'UTF-8');
        $ascii = Str::ascii($lower);

        // Kiểm tra số lượng cụ thể dạng chữ hoặc cụm từ
        if (preg_match('/(duy nhat 1|chi 1|chi mot|1 khoa|1 khoá|mot khoa|một khóa|mot mon|một môn|1 mon|1 môn)/i', $ascii)) {
            return 1;
        }

        if (preg_match('/(2 khoa|2 khoá|hai khoa|hai khóa|top 2)/i', $ascii)) {
            return 2;
        }

        if (preg_match('/(3 khoa|3 khoá|ba khoa|ba khóa|top 3)/i', $ascii)) {
            return 3;
        }

        if (preg_match('/(4 khoa|4 khoá|bon khoa|bốn khóa|top 4)/i', $ascii)) {
            return 4;
        }

        if (preg_match('/(5 khoa|5 khoá|nam khoa|năm khóa|top 5)/i', $ascii)) {
            return 5;
        }

        // Bắt số dạng regex: ví dụ "cho tôi 2 khóa học", "lấy 3 khóa"
        if (preg_match('/(?:lay|cho|tim|chon|xem)?\s*(\d+)\s*(?:khoa|khoá|course|mon|môn)/i', $ascii, $matches)) {
            $num = (int) $matches[1];
            if ($num > 0 && $num <= 10) {
                return $num;
            }
        }

        // Nếu có từ so sánh nhất ("nhất", "top 1", "duy nhất") và không yêu cầu số nhiều -> mặc định 1
        if (preg_match('/(nhieu nhat|cao nhat|tot nhat|dong nhat|moi nhat|re nhat|dat nhat|top 1|so 1|duy nhat)/i', $ascii)) {
            return 1;
        }

        return 5;
    }

    /**
     * Nhận diện tiêu chí sắp xếp phân tích (Analytical Superlative Criteria).
     */
    public function detectSortingCriteria(string $query): ?string
    {
        $ascii = Str::ascii(mb_strtolower(trim($query), 'UTF-8'));

        if (preg_match('/(nhieu danh gia|nhieu review|dong review|danh gia nhieu|nhieu luot danh gia)/i', $ascii)) {
            return 'most_reviews';
        }

        if (preg_match('/(danh gia cao|sao cao|nhieu sao|tot nhat|rating cao|diem cao)/i', $ascii)) {
            return 'highest_rating';
        }

        if (preg_match('/(nhieu nguoi hoc|dong hoc vien|nhieu hoc vien|pho bien|hot nhat|nhieu dang ky)/i', $ascii)) {
            return 'most_enrolled';
        }

        if (preg_match('/(nhieu bai hoc|nhieu bai giang|dai nhat|chi tiet nhat|nhieu bai)/i', $ascii)) {
            return 'most_lessons';
        }

        if (preg_match('/(moi nhat|gan day nhat|vua ra mat)/i', $ascii)) {
            return 'newest';
        }

        if (preg_match('/(re nhat|gia thap nhat|tiet kiem nhat)/i', $ascii)) {
            return 'cheapest';
        }

        if (preg_match('/(dat nhat|gia cao nhat|vip nhat|cao cap nhat)/i', $ascii)) {
            return 'most_expensive';
        }

        return null;
    }

    /**
     * Tìm kiếm, phân tích số liệu và xếp hạng các khóa học phù hợp nhất dựa trên câu hỏi của người dùng.
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
     *     enrollments_count: int,
     *     average_rating: float,
     *     key_lessons: array<int, string>,
     *     match_score: int
     * }>
     */
    public function findRelevantCourses(string $query, ?User $user = null, ?int $explicitLimit = null): array
    {
        $courses = $this->getApprovedCourses();
        if ($courses->isEmpty()) {
            return [];
        }

        $limit = $explicitLimit ?? $this->parseRequestedLimit($query);
        $sortCriteria = $this->detectSortingCriteria($query);

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

        $items = [];

        foreach ($courses as $course) {
            $score = 0;
            $titleLower = mb_strtolower($course->title, 'UTF-8');
            $titleAscii = Str::ascii($titleLower);
            $descLower = mb_strtolower($course->description ?? '', 'UTF-8');
            $descAscii = Str::ascii($descLower);
            $teacherName = $course->teacher?->name ?? 'Ban Đào Tạo';
            $teacherAscii = Str::ascii(mb_strtolower($teacherName, 'UTF-8'));

            // Khớp trực tiếp cụm từ tìm kiếm
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

                foreach ($course->lessons as $lesson) {
                    $lessonAscii = Str::ascii(mb_strtolower($lesson->title, 'UTF-8'));
                    if (str_contains($lessonAscii, $kw)) {
                        $score += 6;
                        break;
                    }
                }
            }

            // Tiêu chí giá
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

            $avgRating = round((float) ($course->reviews_avg_rating ?? 0), 1);
            $reviewsCount = (int) ($course->reviews_count ?? $course->reviews()->count());
            $enrollmentsCount = (int) ($course->enrollments_count ?? $course->enrollments()->count());
            $lessonsCount = (int) ($course->lessons_count ?? $course->lessons()->count());

            $lessonTitles = $course->lessons->take(5)->pluck('title')->toArray();

            $items[] = [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug ?? (string) $course->id,
                'url' => $course->url,
                'price' => (int) $course->price,
                'formatted_price' => $course->formatted_price,
                'is_free' => (bool) $course->is_free,
                'teacher' => $teacherName,
                'description' => Str::limit(strip_tags($course->description ?? ''), 160),
                'lessons_count' => $lessonsCount,
                'reviews_count' => $reviewsCount,
                'enrollments_count' => $enrollmentsCount,
                'average_rating' => $avgRating,
                'key_lessons' => $lessonTitles,
                'match_score' => $score,
                'created_at' => $course->created_at?->toIso8601String(),
            ];
        }

        // Xử lý sắp xếp phân tích chính xác theo tiêu chí yêu cầu
        if ($sortCriteria !== null) {
            match ($sortCriteria) {
                'most_reviews' => usort($items, fn ($a, $b) => $b['reviews_count'] <=> $a['reviews_count']
                    ?: $b['average_rating'] <=> $a['average_rating']
                    ?: $b['enrollments_count'] <=> $a['enrollments_count']),

                'highest_rating' => usort($items, fn ($a, $b) => $b['average_rating'] <=> $a['average_rating']
                    ?: $b['reviews_count'] <=> $a['reviews_count']
                    ?: $b['enrollments_count'] <=> $a['enrollments_count']),

                'most_enrolled' => usort($items, fn ($a, $b) => $b['enrollments_count'] <=> $a['enrollments_count']
                    ?: $b['average_rating'] <=> $a['average_rating']
                    ?: $b['reviews_count'] <=> $a['reviews_count']),

                'most_lessons' => usort($items, fn ($a, $b) => $b['lessons_count'] <=> $a['lessons_count']
                    ?: $b['reviews_count'] <=> $a['reviews_count']),

                'newest' => usort($items, fn ($a, $b) => $b['id'] <=> $a['id']),

                'cheapest' => usort($items, fn ($a, $b) => $a['price'] <=> $b['price']
                    ?: $b['average_rating'] <=> $a['average_rating']),

                'most_expensive' => usort($items, fn ($a, $b) => $b['price'] <=> $a['price']),
            };

            return array_slice($items, 0, $limit);
        }

        // Mặc định: sắp xếp theo điểm khớp (Relevance Score)
        usort($items, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);

        if (! empty($items) && $items[0]['match_score'] > 0) {
            $filtered = array_filter($items, fn ($item) => $item['match_score'] > 0);

            return array_slice(array_values($filtered), 0, $limit);
        }

        return array_slice($items, 0, $limit);
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
     * Tạo tóm tắt tổng quan về toàn bộ thư viện khóa học trên hệ thống kèm chỉ số phân tích.
     *
     * @return array{
     *     total_courses: int,
     *     free_courses_count: int,
     *     paid_courses_count: int,
     *     courses_list: array<int, array{
     *         id: int,
     *         title: string,
     *         price: string,
     *         price_number: int,
     *         teacher: string,
     *         url: string,
     *         lessons_count: int,
     *         reviews_count: int,
     *         average_rating: float,
     *         enrollments_count: int
     *     }>
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

            $avgRating = round((float) ($c->reviews_avg_rating ?? 0), 1);
            $revCount = (int) ($c->reviews_count ?? $c->reviews()->count());
            $enrollCount = (int) ($c->enrollments_count ?? $c->enrollments()->count());
            $lesCount = (int) ($c->lessons_count ?? $c->lessons()->count());

            $list[] = [
                'id' => $c->id,
                'title' => $c->title,
                'price' => $c->formatted_price,
                'price_number' => (int) $c->price,
                'teacher' => $c->teacher?->name ?? 'Ban Đào Tạo',
                'url' => $c->url,
                'lessons_count' => $lesCount,
                'reviews_count' => $revCount,
                'average_rating' => $avgRating,
                'enrollments_count' => $enrollCount,
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
     * Xây dựng System Prompt chuẩn mực cho AI với đầy đủ quy tắc Scope Guardrails và Zero-Hallucination.
     */
    public function buildSystemPrompt(?User $user, array $relevantCourses, array $catalogSummary, array $userProgress): string
    {
        $coursesJson = json_encode($relevantCourses, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $catalogSummaryJson = json_encode($catalogSummary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $userProgressJson = json_encode($userProgress, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $refusalExact = AiGuardrailService::REFUSAL_MESSAGE;

        $prompt = <<<PROMPT
Bạn là Trợ lý AI Cố vấn Học tập của nền tảng trực tuyến "Ctrl C+V" (LMS).
Nhiệm vụ duy nhất của bạn là tư vấn khóa học, giải đáp thắc mắc về giảng viên, bài học, lộ trình học tập và tính năng trên trang web Ctrl C+V.

=== QUY TẮC PHẠM VI BẮT BUỘC (STRICT SCOPE GUARDRAILS) ===
1. GIỚI HẠN PHẠM VI (SCOPE BOUNDARY):
   - BẠN CHỈ ĐƯỢC TRẢ LỜI CÁC CÂU HỎI LIÊN QUAN ĐẾN:
     + Khóa học, bài giảng, giáo trình, học phí, đánh giá, giảng viên trên hệ thống Ctrl C+V.
     + Lộ trình học tập, tư vấn khóa học cho người mới bắt đầu.
     + Tiến độ học tập cá nhân, bài học tiếp theo, bài thi trắc nghiệm (Quizzes) trên Ctrl C+V.
     + Lời chào hỏi hoặc cảm ơn lịch sự.
   - TUYỆT ĐỐI TỪ CHỐI các chủ đề ngoài lề (Thời tiết, nấu ăn/công thức món ăn, chính trị, thể thao/showbiz, y tế, bói toán/tử vi, làm thơ, viết tiểu thuyết, giải bài tập toán/lý/hóa phổ thông, v.v.).
   - KHI GẶP CÂU HỎI NGOÀI LỀ, BẠN BẮT BUỘC PHẢI TỪ CHỐI VÀ CHỈ TRẢ LỜI ĐÚNG CÂU NÀY:
     "{$refusalExact}"

=== QUY TẮC DỮ LIỆU & PHÂN TÍCH (DATA & ANALYTICAL METRICS) ===
2. ZERO HALLUCINATION (CHỐNG BỊA ĐẶT TUYỆT ĐỐI):
   - Bạn CHỈ ĐƯỢC PHÉP gợi ý các khóa học THỰC SỰ TỒN TẠI trong cơ sở dữ liệu được cung cấp dưới đây.
   - BẮT BUỘC kèm theo đường dẫn Markdown chuẩn xác: [Tên khóa học](url) lấy đúng từ trường 'url'.

3. TUÂN THỦ CHÍNH XÁC SỐ LƯỢNG YÊU CẦU (EXACT QUANTITY COMPLIANCE):
   - Nếu người dùng yêu cầu 1 khóa học ("cho tôi 1 khóa...", "khóa học duy nhất", "1 khóa học có nhiều đánh giá nhất"), bạn CHỈ ĐƯỢC TRẢ VỀ ĐÚNG 1 KHÓA HỌC. Không được tự ý đưa ra 5 khóa học.
   - Nếu người dùng yêu cầu top 3, đưa ra đúng 3 khóa học.

4. SẮP XẾP PHÂN TÍCH CHÍNH XÁC THEO CHỈ SỐ:
   - "nhiều đánh giá nhất" / "nhiều review nhất": Dựa vào reviews_count cao nhất.
   - "đánh giá cao nhất" / "nhiều sao nhất": Dựa vào average_rating cao nhất.
   - "nhiều người học nhất" / "phổ biến nhất": Dựa vào enrollments_count cao nhất.
   - "nhiều bài học nhất": Dựa vào lessons_count cao nhất.
   - "miễn phí" / "rẻ nhất": Dựa vào price.
   - Khi giới thiệu khóa học, hãy nêu rõ thông số: Giảng viên, Đánh giá (⭐ rating/5.0 với reviews_count lượt đánh giá), Số học viên, Số bài giảng và Học phí.

5. PHONG CÁCH TRÌNH BÀY:
   - Trả lời ngắn gọn, súc tích, đi thẳng vào trọng tâm, không dùng emoji màu mè lòe loẹt.
   - In đậm thông tin quan trọng. Mỗi đoạn 1-2 câu.

=== DỮ LIỆU THỜI GIAN THỰC (REAL-TIME LMS DATA) ===
--- CÁC KHÓA HỌC PHÙ HỢP / ĐÃ SẮP XẾP ---
{$coursesJson}

--- TOÀN BỘ DANH MỤC KHÓA HỌC TRÊN HỆ THỐNG KÈM CHỈ SỐ ---
{$catalogSummaryJson}

--- DỮ LIỆU HỌC TẬP CỦA NGƯỜI DÙNG HIỆN TẠI ---
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
