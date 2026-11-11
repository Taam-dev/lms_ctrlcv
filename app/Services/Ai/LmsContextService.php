<?php

namespace App\Services\Ai;

use App\Models\Course;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\MinigameQuiz;
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

        return 3;
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
     * Kiểm tra xem câu hỏi có chứa từ khóa liên quan tới khóa học/học tập hay không.
     */
    public function hasCourseKeywords(string $ascii): bool
    {
        return (bool) preg_match('/(khoa\s*hoc|khoa\s*h[oọ]c|course|bai\s*hoc|bai\s*giang|lo\s*trinh|hoc\s*phi|hoc\s*lap\s*trinh|mon\s*hoc|giao\s*trinh|dang\s*ky|hoc\s*gi|tim\s*khoa)/i', $ascii);
    }

    /**
     * Kiểm tra xem tin nhắn có phải là lời chào hỏi, cảm ơn hoặc giao tiếp thông thường không tìm kiếm khóa học.
     */
    public function isGreetingOrCasual(string $query): bool
    {
        $clean = mb_strtolower(trim($query), 'UTF-8');
        $ascii = Str::ascii($clean);

        // Nếu có chứa từ khóa cụ thể về khóa học thì không phải câu chào đơn thuần
        if ($this->hasCourseKeywords($ascii)) {
            return false;
        }

        // Bắt các mẫu chào hỏi (bao gồm cả gõ nhầm như "xin chaos", "xin chaop", "helo", "alooo")
        if (preg_match('/^(xin\s*chao[sp]?|chao[sp]?|hello+|helo+|hi+|hey+|alo+|h[eê]\s*l[oô]|h[eê]\s*nh[oô]|good\s*(morning|afternoon|evening))/iu', $ascii)) {
            return true;
        }

        // Câu hỏi về bản thân bot
        if (preg_match('/^(ban\s+la\s+ai|ban\s+ten\s+gi|ai\s+day|who\s+are\s+you|tro\s+giup|giup\s+toi)$/iu', $ascii)) {
            return true;
        }

        // Cảm ơn / tạm biệt / xác nhận ngắn
        if (preg_match('/^(cam\s*on|thank\s*you|thanks|tks|tam\s*biet|bye+|goodbye|ok+|okie|oke|da|vang|uhm?)$/iu', $ascii)) {
            return true;
        }

        return false;
    }

    /**
     * Nhận diện người dùng có đang yêu cầu xem danh mục/danh sách khóa học chung không.
     */
    public function isGeneralCatalogInquiry(string $query): bool
    {
        $clean = mb_strtolower(trim($query), 'UTF-8');
        $ascii = Str::ascii($clean);

        // Nếu người dùng hỏi một khóa học cụ thể theo chủ đề/công nghệ thì không phải hỏi chung chung
        if (preg_match('/(laravel|php|react|javascript|js|python|html|css|sql|database|vue|node|java|flutter|golang)/i', $ascii)) {
            return false;
        }

        return (bool) (
            preg_match('/(tat\s*ca\s*(cac\s*)?khoa(\s*hoc)?|danh\s*sach\s*(cac\s*)?khoa(\s*hoc)?|toan\s*bo\s*(cac\s*)?khoa(\s*hoc)?|cac\s*khoa\s*hoc(\s*hien\s*co)?|co\s*nhung\s*khoa(\s*hoc)?\s*nao|danh\s*muc\s*(cac\s*)?khoa(\s*hoc)?)/i', $ascii)
            || str_contains($clean, 'tất cả khóa học')
            || str_contains($clean, 'danh sách khóa học')
            || str_contains($clean, 'toàn bộ khóa học')
            || str_contains($clean, 'các khóa học hiện có')
            || str_contains($clean, 'có những khóa học nào')
            || str_contains($clean, 'có những khóa nào')
        );
    }

    /**
     * Nhận diện người dùng đang hỏi về nghiệp vụ hoặc thao tác của giảng viên
     * (tạo bài giảng, tạo khóa học, tạo trắc nghiệm, truy cập panel GV).
     */
    public function isTeacherInquiry(string $query): bool
    {
        $clean = mb_strtolower(trim($query), 'UTF-8');
        $ascii = Str::ascii($clean);

        return (bool) (
            preg_match('/(tao|them|huong dan|cach|lam sao|xem)\s*(bai giang|bai hoc|khoa hoc|quiz|de thi|de trac nghiem)/i', $ascii)
            || preg_match('/(panel|bang dieu khien|trang|kenh|muc)\s*(giang vien|gv|instructor|teacher)/i', $ascii)
            || preg_match('/(vao|mo)\s*(gv|giang vien|instructor)/i', $ascii)
            || preg_match('/(tai khoan|vai tro)\s*(giang vien|gv)/i', $ascii)
            || str_contains($clean, 'tạo bài giảng')
            || str_contains($clean, 'tạo bài học')
            || str_contains($clean, 'thêm bài giảng')
            || str_contains($clean, 'thêm bài học')
            || str_contains($clean, 'tạo khóa học')
            || str_contains($clean, 'tạo đề thi')
            || str_contains($clean, 'tạo quiz')
            || str_contains($clean, 'tạo trắc nghiệm')
            || str_contains($clean, 'bảng điều khiển giảng viên')
            || str_contains($clean, 'panel gv')
            || str_contains($clean, 'panel giảng viên')
            || str_contains($clean, 'trang giảng viên')
        );
    }

    /**
     * Nhận diện người dùng đang tâm sự chuyện buồn, thất tình, người yêu chia tay, bồ đá, stress.
     */
    public function isHeartbreakOrVenting(string $query): bool
    {
        $clean = mb_strtolower(trim($query), 'UTF-8');
        $ascii = Str::ascii($clean);

        return (bool) (
            preg_match('/(chia tay|bo da|that tinh|nguoi yeu|crush|cam sung|nyc|nguoi yeu cu|yeu duong|tinh yeu|hen ho)/i', $ascii)
            || preg_match('/(buon qua|chan qua|chan doi|co don|tuyet vong|met moi qua|stress qua|dau long)/i', $ascii)
            || str_contains($clean, 'chia tay')
            || str_contains($clean, 'thất tình')
            || str_contains($clean, 'bồ đá')
            || str_contains($clean, 'người yêu')
            || str_contains($clean, 'buồn quá')
            || str_contains($clean, 'chán đời')
            || str_contains($clean, 'cô đơn')
            || str_contains($clean, 'đau lòng')
        );
    }

    /**
     * Nhận diện người dùng đang hỏi về Minigame Gold Quest (Blooket), cướp vàng, mở rương, mã PIN, luật chơi.
     */
    public function isMinigameInquiry(string $query): bool
    {
        $clean = mb_strtolower(trim($query), 'UTF-8');
        $ascii = Str::ascii($clean);

        return (bool) (
            preg_match('/(minigame|mini game|gold quest|blooket|cuop vang|mo ruong|ruong vang|swap|doi vang|ma pin|dau truong|phong choi|linh vat|blook)/i', $ascii)
            || str_contains($clean, 'minigame')
            || str_contains($clean, 'gold quest')
            || str_contains($clean, 'blooket')
            || str_contains($clean, 'cướp vàng')
            || str_contains($clean, 'mở rương')
            || str_contains($clean, 'rương vàng')
            || str_contains($clean, 'mã pin')
            || str_contains($clean, 'phòng chơi')
            || str_contains($clean, 'đấu trường')
            || str_contains($clean, 'linh vật')
        );
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
        // 1. Nếu là câu chào hỏi hoặc trò chuyện xã giao thuần túy, không trả về khóa học nào
        if ($this->isGreetingOrCasual($query)) {
            return [];
        }

        // 2. Nếu là câu hỏi hướng dẫn nghiệp vụ giảng viên (tạo bài giảng, tạo khóa...), không gắn khóa học
        if ($this->isTeacherInquiry($query)) {
            return [];
        }

        // 3. Nếu là câu hỏi chung chung về "tất cả khóa học", không gắn danh sách khóa học ngẫu nhiên
        if ($this->isGeneralCatalogInquiry($query)) {
            return [];
        }

        // 4. Nếu là câu hỏi về Minigame mà không hỏi về khóa học, không gắn khóa học ngẫu nhiên
        if ($this->isMinigameInquiry($query) && ! str_contains(mb_strtolower($query), 'khóa') && ! str_contains(mb_strtolower($query), 'course')) {
            return [];
        }

        $courses = $this->getApprovedCourses();
        if ($courses->isEmpty()) {
            return [];
        }

        // 4. Nếu là tâm sự chuyện tình cảm / thất tình, chọn 1 khóa học tiêu biểu nhất để khuyên học viên
        if ($this->isHeartbreakOrVenting($query)) {
            $first = $courses->first();
            if ($first) {
                return [[
                    'id' => $first->id,
                    'title' => $first->title,
                    'slug' => $first->slug ?? (string) $first->id,
                    'url' => $first->url,
                    'price' => (int) $first->price,
                    'formatted_price' => $first->formatted_price,
                    'is_free' => (bool) $first->is_free,
                    'teacher' => $first->teacher?->name ?? 'Ban Đào Tạo',
                    'description' => Str::limit(strip_tags($first->description ?? ''), 160),
                    'lessons_count' => (int) ($first->lessons_count ?? $first->lessons()->count()),
                    'reviews_count' => (int) ($first->reviews_count ?? $first->reviews()->count()),
                    'enrollments_count' => (int) ($first->enrollments_count ?? $first->enrollments()->count()),
                    'average_rating' => round((float) ($first->reviews_avg_rating ?? 5.0), 1),
                    'key_lessons' => $first->lessons->take(5)->pluck('title')->toArray(),
                    'match_score' => 100,
                    'created_at' => $first->created_at?->toIso8601String(),
                ]];
            }
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

        // Nếu không có điểm khớp, không hiển thị khóa học ngẫu nhiên
        return [];
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
     * Lấy dữ liệu tổng quan về các bộ đề Minigame Gold Quest công khai đang có trên hệ thống.
     *
     * @return array{
     *     total_quizzes: int,
     *     quizzes_list: array<int, array{
     *         id: int,
     *         title: string,
     *         questions_count: int,
     *         difficulty: string,
     *         creator: string
     *     }>,
     *     minigame_url: string
     * }
     */
    public function getMinigameSummary(): array
    {
        $quizzes = MinigameQuiz::where('is_public', true)
            ->with(['creator:id,name', 'questions:id,quiz_id'])
            ->withCount('questions')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $list = [];
        foreach ($quizzes as $q) {
            $list[] = [
                'id' => $q->id,
                'title' => $q->title,
                'questions_count' => (int) ($q->questions_count ?? $q->questions->count()),
                'difficulty' => match ($q->difficulty) {
                    'easy' => 'Dễ',
                    'hard' => 'Khó',
                    default => 'Trung bình',
                },
                'creator' => $q->creator?->name ?? 'Giảng viên',
            ];
        }

        return [
            'total_quizzes' => MinigameQuiz::where('is_public', true)->count(),
            'quizzes_list' => $list,
            'minigame_url' => route('minigame.index'),
        ];
    }

    /**
     * Lấy dữ liệu tổng quan về Kho Tài Liệu học tập công khai trên hệ thống.
     *
     * @return array{
     *     total_documents: int,
     *     documents_list: array<int, array{
     *         id: int,
     *         title: string,
     *         category: string,
     *         files_count: int,
     *         downloads: int,
     *         author: string,
     *         url: string
     *     }>,
     *     documents_url: string
     * }
     */
    public function getDocumentsSummary(): array
    {
        $docs = Document::with(['user:id,name', 'files'])
            ->withCount('files')
            ->orderBy('is_featured', 'desc')
            ->orderBy('download_count', 'desc')
            ->take(8)
            ->get();

        $list = [];
        foreach ($docs as $d) {
            $list[] = [
                'id' => $d->id,
                'title' => $d->title,
                'category' => $d->category,
                'files_count' => (int) ($d->files_count ?? $d->files->count()),
                'downloads' => (int) $d->download_count,
                'author' => $d->user?->name ?? 'Giảng viên',
                'url' => route('documents.show', $d->slug ?: $d->id),
            ];
        }

        return [
            'total_documents' => Document::count(),
            'documents_list' => $list,
            'documents_url' => route('documents.index'),
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

        $minigameSummary = $this->getMinigameSummary();
        $minigameSummaryJson = json_encode($minigameSummary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $minigameUrl = route('minigame.index');

        $documentsSummary = $this->getDocumentsSummary();
        $documentsSummaryJson = json_encode($documentsSummary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $documentsUrl = route('documents.index');
        $uploadDocumentUrl = route('documents.create');

        $refusalExact = AiGuardrailService::REFUSAL_MESSAGE;
        $instructorDashboardUrl = route('instructor.dashboard');
        $createCourseUrl = route('instructor.courses.create');
        $createQuizUrl = route('instructor.quizzes.create');
        $coursesCatalogUrl = route('courses.index');

        $prompt = <<<PROMPT
Bạn là Trợ lý AI Cố vấn Học tập của nền tảng trực tuyến "Ctrl C+V" (LMS).
Nhiệm vụ của bạn là tư vấn khóa học, giải đáp thắc mắc về giảng viên, bài học, lộ trình học tập và hướng dẫn thao tác trên trang web Ctrl C+V.

=== QUY TẮC BẮT BUỘC KHI NGƯỜI DÙNG HỎI "TẤT CẢ KHÓA HỌC" / "DANH SÁCH KHÓA HỌC" ===
Khi người dùng hỏi chung chung như: "tất cả khóa học", "danh sách khóa học", "các khóa học hiện có", "toàn bộ khóa học", "có những khóa học nào", v.v.:
- TUYỆT ĐỐI KHÔNG LIỆT KÊ TOÀN BỘ KHÓA HỌC RA (vì sau này hệ thống có hàng trăm khóa học, việc liệt kê hết sẽ rất dài và tốn thời gian).
- THAY VÀO ĐÓ, BẠN BẮT BUỘC PHẢI TRẢ LỜI RẰNG:
  "Hệ thống hiện có rất nhiều khóa học đa dạng. Như vậy sẽ có rất nhiều khóa học nếu liệt kê hết, bạn có thể cho tôi biết bạn quan tâm đến lĩnh vực hay công nghệ nào (ví dụ: Lập trình Web, Frontend & React, Backend & Laravel, Cơ sở dữ liệu...) không để tôi có thể dễ dàng tìm kiếm và tư vấn khóa học phù hợp hơn cho bạn?"
  Kèm theo liên kết tra cứu: [Xem danh mục khóa học trên hệ thống]({$coursesCatalogUrl}).

=== NGUYÊN TẮC "THÊM MUỐI" & HÀI HƯỚC KHI NGƯỜI DÙNG TÂM SỰ / THẤT TÌNH / BUỒN PHIỀN ===
Khi người dùng tâm sự về chuyện tình cảm, thất tình, người yêu chia tay, bồ đá, buồn chán, cô đơn (ví dụ: "người yêu tôi chia tay tôi rồi", "em bị bồ đá", "buồn quá", "thất tình rồi", "chán đời quá", v.v.):
- TUYỆT ĐỐI KHÔNG TRẢ LỜI MÁY MÓC / RẬP KHUÔN KIỂU ROBOT (như: "mình là AI cố vấn học tập nên không thể tham gia vào các vấn đề đời tư hay tình cảm cá nhân...").
- HÃY TRẢ LỜI "THÊM MUỐI", HÀI HƯỚC, DUYÊN DÁNG VÀ "BẺ LÁI" KHÉO LÉO SANG KHÓA HỌC ĐỂ GIẢI TỎA:
  1. Đồng cảm ngắn gọn và hài hước: "Nghe buồn vậy... 🥺 Nhưng mà nghĩ tích cực lên bạn ơi!"
  2. Gợi ý tham gia ngay khóa học (chọn 1 khóa học trong danh sách bên dưới kèm link Markdown chuẩn xác [Tên khóa học](url) và giá) để quên đi nỗi buồn.
  3. Giải thích dí dỏm: "Vì khi bắt tay vào học, tâm trí bạn sẽ dồn 100% vào bài học, tư duy logic và fix bug, bận rộn như vậy sẽ chẳng còn thời gian đâu mà bận tâm hay nhớ nhung người yêu cũ nữa!"
  4. Động viên sâu sắc: "Người yêu có thể rời bỏ bạn, chứ kiến thức, kỹ năng và mức lương nghìn đô thì sẽ luôn ở bên bạn 🚀."
  5. BẮT BUỘC chèn icon mắt kính + cười tươi: 😎😆 (hoặc 😎😁).

=== QUY TẮC BẮT BUỘC DÀNH CHO GIẢNG VIÊN (CÁCH TẠO BÀI GIẢNG / TẠO KHÓA HỌC / PANEL GV) ===
Khi người dùng hỏi về các nghiệp vụ hoặc thao tác của Giảng viên (ví dụ: "làm sao để tạo bài giảng", "cách tạo bài học", "thêm bài giảng thế nào", "tạo khóa học", "tạo đề thi quiz", "vào panel giảng viên ở đâu", v.v.):
BẠN BẮT BUỘC PHẢI HƯỚNG DẪN CHÍNH XÁC QUY TRÌNH HOẠT ĐỘNG THỰC TẾ TRÊN NỀN TẢNG CTRL C+V LMS THEO CÁC BƯỚC SAU, TUYỆT ĐỐI KHÔNG NÓI CHUNG CHUNG HOẶC BỊA ĐẶT CÁC MỤC KHÔNG TỒN TẠI:

1. BƯỚC 1: TRUY CẬP VÀO BẢNG ĐIỀU KHIỂN GIẢNG VIÊN (PANEL GV)
   - ĐẦU TIÊN: Nhấp vào biểu tượng ảnh đại diện/tên tài khoản (**Profile**) ở **góc trên bên phải** màn hình để mở ra panel menu các lựa chọn.
   - TIẾP THEO: Trong panel menu vừa hiện ra, nhấn chọn mục **"GV"** (dành cho tài khoản Giảng viên) để chuyển tới [Bảng điều khiển Giảng viên]({$instructorDashboardUrl}).

2. BƯỚC 2: THỰC HIỆN CÁC THAO TÁC TRONG PANEL GIẢNG VIÊN:
   - **Cách tạo / thêm bài giảng (lesson):**
     + Tại Bảng điều khiển Giảng viên, chọn tab **"Khóa học & Bài học"** (hoặc tab Khóa học).
     + Tìm khóa học bạn muốn thêm bài giảng, sau đó bấm vào nút **"+ Bài giảng"** ở cột thao tác bên phải khóa học đó (hoặc bấm trực tiếp vào dòng khóa học để mở rộng danh sách giáo trình, rồi bấm **"+ Thêm bài giảng vào khóa này"**).
     + Điền các thông tin bài giảng: **Tiêu đề bài học**, **Số thứ tự**, **Thời lượng (phút)**, chọn **Loại bài học** (Video bài giảng từ YouTube/Vimeo / Văn bản kiến thức / Bài tập trắc nghiệm Quiz) và bấm **"Lưu bài giảng"**.
   - **Cách tạo khóa học mới:**
     + Tại góc trên Bảng điều khiển Giảng viên, bấm vào nút **"+ Tạo khóa học mới"** (URL: [Tạo khóa học mới]({$createCourseUrl})).
     + Điền đầy đủ thông tin: Tiêu đề, Mô tả tóm tắt, Ảnh đại diện, Học phí (hoặc chọn Miễn phí) và gửi xét duyệt.
   - **Cách tạo đề kiểm tra trắc nghiệm (Quiz):**
     + Chuyển sang tab **"Bài kiểm tra"** trong Bảng điều khiển Giảng viên -> bấm nút **"+ Tạo bài kiểm tra (quiz)"** (URL: [Tạo bài kiểm tra]({$createQuizUrl})).
     + Bạn có thể tự soạn câu hỏi thủ công hoặc dùng tính năng **AI sinh đề thi tự động** siêu tốc.
   - **Cách xem kết quả làm bài của học viên:**
     + Chuyển sang tab **"Kết quả học viên"** để xem danh sách học viên đã nộp bài, điểm số và số câu đúng.

(Lưu ý: Nhắc nhở người dùng cần đăng nhập bằng tài khoản có vai trò Giảng viên mới thấy mục "GV" trong panel menu profile góc phải).

=== KIẾN THỨC VÀ QUY TẮC VỀ CHẾ ĐỘ MINIGAME TRẮC NGHIỆM: GOLD QUEST (ĐẠI CHIẾN CƯỚP VÀNG) ===
Nền tảng Ctrl C+V LMS có tính năng Minigame độc quyền thời gian thực "Gold Quest" (Đại chiến Cướp Vàng & Mở Rương) mô phỏng thể thức Blooket cực kỳ cuốn hút và sôi động.
Khi người dùng hỏi về minigame, trò chơi, cướp vàng, mở rương, mã PIN, cách tạo phòng, luật chơi, BẠN HÃY GIẢI THÍCH CHÍNH XÁC:

1. ĐƯỜNG DẪN TRUY CẬP: [Đấu Trường Minigame]({$minigameUrl})
2. CÁCH HỌC VIÊN THAM GIA:
   - Truy cập [Vào Chơi Minigame]({$minigameUrl}), nhập Mã PIN 6 chữ số do Giảng viên cung cấp.
   - Chọn Linh vật đại diện (Blook): Vua Vàng, Hải Tặc, Phù Thủy, Phi Hành Gia, hoặc Cáo Tinh Nghịch.
   - Đặt Tên hiển thị và chờ Giảng viên bấm Bắt đầu trận đấu.
3. LUẬT CHƠI & MỞ RƯƠNG MAY MẮN (GAME LOOP):
   - Học viên trả lời câu hỏi trắc nghiệm theo tốc độ cá nhân (asynchronous loop).
   - Nếu TRẢ LỜI SAI: Bị khóa màn hình 2 giây kèm đáp án đúng để học hỏi, sau đó tự nạp câu hỏi tiếp. Không bị trừ vàng.
   - Nếu TRẢ LỜI ĐÚNG: Được quyền mở 1 trong 3 chiếc rương may mắn bí ẩn:
     * Cộng vàng cố định: +20, +50, +100, hoặc rương vàng khủng +250 vàng.
     * Nhân hệ số vàng: Tăng thêm +10% hoặc +25% tổng số vàng đang có.
     * Rương rỗng hoặc Mất vàng: 0 vàng hoặc bị phạt -20% số vàng.
     * CƯỚP VÀNG (STEAL GOLD): Được chọn 1 người chơi bất kỳ trong phòng để CƯỚP TỪ 10% ĐẾN 30% số vàng của họ! (Người bị cướp sẽ có hiệu ứng rung lắc màn hình và thông báo cảnh báo tức thì).
     * TRÁO ĐỔI VỊ TRÍ (SWAP): Đổi toàn bộ số vàng và thứ hạng hiện tại của mình với 1 đối thủ bất kỳ trong phòng (lội ngược dòng ngoạn mục!).
4. BẢNG XẾP HẠNG & KẾT THÚC:
   - Điểm số và thứ hạng được xử lý siêu tốc qua Redis Sorted Set (ZSET).
   - Màn hình Giảng viên (chiếu máy chiếu) hiển thị đồng hồ đếm ngược, thanh chạy sự kiện (ai vừa cướp của ai) và Top 5-10 thay đổi liên tục.
   - Khi hết giờ (thường 3-10 phút): Hệ thống khóa tương tác và hiện Bục vinh quang (Top 1, Top 2, Top 3 Podium) kèm pháo hoa rực rỡ.
5. DÀNH CHO GIẢNG VIÊN (CÁCH TẠO BỘ ĐỀ & MỞ PHÒNG):
   - Vào tab "Quản Lý Phòng Chơi" tại trang Minigame.
   - Bấm "Tạo Bộ Câu Hỏi Mới": Soạn thủ công, dùng AI sinh tự động, hoặc dán file JSON copy từ Blooket.
   - Bấm "Mở Phòng Cho Lớp Chơi": Chọn bộ câu hỏi, đặt thời lượng (3-15 phút), bật/tắt quyền Cướp/Swap, lấy mã PIN 6 số và bắt đầu phòng đấu.

=== KIẾN THỨC VÀ QUY TẮC VỀ KHO TÀI LIỆU HỌC TẬP (DOCUMENT LIBRARY) ===
Nền tảng Ctrl C+V LMS có chuyên trang [Kho Tài Liệu]({$documentsUrl}) - nơi lưu trữ và chia sẻ giáo trình, slide bài giảng, đề thi ôn tập và mã nguồn đồ án mẫu.
Khi người dùng hỏi về tài liệu học tập, slide, giáo trình PDF, đề thi, tải file tài liệu hoặc cách đăng tài liệu, BẠN HÃY GIẢI THÍCH:

1. ĐƯỜNG DẪN TRUY CẬP: [Kho Tài Liệu Ctrl C+V]({$documentsUrl})
2. TẢI TÀI LIỆU (DÀNH CHO HỌC VIÊN & GIẢNG VIÊN):
   - Mọi học viên và giảng viên đều có thể tìm kiếm theo từ khóa, lọc theo chuyên mục (Lập trình Web, Cơ sở dữ liệu, AI, Khoa học máy tính, DevOps, An toàn thông tin, Di động, Slide tóm tắt, Đề thi, v.v.).
   - Mỗi tài liệu có thể chứa nhiều file đính kèm (PDF, DOCX, PPTX, XLSX, ZIP, RAR, MP4...). Bấm nút "Tải xuống" để tải trực tiếp về máy hoàn toàn miễn phí.
3. ĐĂNG TẢI TÀI LIỆU MỚI (DÀNH CHO GIẢNG VIÊN & ADMIN):
   - Giảng viên và Admin có nút **"+ Đăng tài liệu mới"** (URL: [Đăng tải tài liệu]({$uploadDocumentUrl})).
   - Hỗ trợ tải nhiều file cùng lúc (tối đa 50MB/file), chọn ảnh bìa hoặc dùng ảnh minh họa tự động theo chuyên mục, nhập tiêu đề và mô tả.
   - Giảng viên/Admin có thể tải xuống, chỉnh sửa hoặc xóa các tài liệu do chính mình đăng tải.

=== QUY TẮC PHẠM VI BẮT BUỘC (STRICT SCOPE GUARDRAILS) ===
1. CHÀO HỎI & GIAO TIẾP TỰ NHIÊN:
   - Khi người dùng gửi lời chào hỏi (ví dụ: "xin chào", "xin chao", "xin chaop", "xin chaos", "hello", "hi", "alo", "chào bạn", v.v.):
     + LUÔN LUÔN đáp lại một cách lịch sự, tự nhiên và thân thiện.
     + Chào bạn, giới thiệu ngắn gọn bạn là Trợ lý AI Cố vấn Học tập của Ctrl C+V LMS và hỏi xem người dùng cần hỗ trợ gì về các khóa học, bài giảng hoặc lộ trình học tập.
     + TUYỆT ĐỐI KHÔNG TỪ CHỐI các câu chào hỏi, cảm ơn hay lời mở đầu giao tiếp thông thường!

2. PHẠM VI HỖ TRỢ VÀ TỪ CHỐI CHỦ ĐỀ NGOÀI LỀ:
   - Bạn chỉ hỗ trợ thông tin liên quan đến nền tảng Ctrl C+V: Khóa học, bài giảng, giáo trình, học phí, đánh giá, giảng viên, lộ trình học và bài thi trắc nghiệm.
   - CHỈ TỪ CHỐI khi người dùng hỏi các chủ đề hoàn toàn ngoài lề không liên quan đến học tập hay trang web (Nấu ăn/ẩm thực, dự báo thời tiết, chính trị, thể thao/showbiz, y tế/khám bệnh, bói toán/tử vi, làm thơ, viết tiểu thuyết, giải bài tập toán/lý/hóa phổ thông, hỏi chuyện đời tư, v.v.).
   - Khi gặp các câu hỏi thực sự ngoài lề đó, bạn mới từ chối và trả lời:
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

--- CÁC BỘ ĐỀ MINIGAME CÔNG KHAI TRÊN HỆ THỐNG ---
{$minigameSummaryJson}

--- KHO TÀI LIỆU HỌC TẬP TRÊN HỆ THỐNG ---
{$documentsSummaryJson}
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
