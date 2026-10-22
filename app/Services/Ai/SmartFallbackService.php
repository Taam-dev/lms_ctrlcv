<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Str;

class SmartFallbackService
{
    public function __construct(
        protected ?AiGuardrailService $guardrailService = null,
        protected ?LmsContextService $contextService = null
    ) {
        $this->guardrailService = $guardrailService ?? app(AiGuardrailService::class);
        $this->contextService = $contextService ?? app(LmsContextService::class);
    }

    /**
     * Sinh câu trả lời thông minh, đúng phạm vi và phân tích dữ liệu database.
     *
     * @param  array<int, mixed>  $relevantCourses
     * @param  array<string, mixed>  $catalogSummary
     * @param  array<string, mixed>  $userProgress
     * @return array{
     *     message: string,
     *     intent: string,
     *     suggested_chips: array<int, string>
     * }
     */
    public function generateResponse(
        string $query,
        ?User $user,
        array $relevantCourses,
        array $catalogSummary,
        array $userProgress
    ): array {
        // Tầng 1: Kiểm tra Guardrail phạm vi website
        if ($this->guardrailService->isOffTopic($query)) {
            return $this->guardrailService->getRefusalResponse();
        }

        $queryLower = mb_strtolower(trim($query), 'UTF-8');
        $queryAscii = Str::ascii($queryLower);

        // Phân tích intent của người dùng
        $intent = $this->detectIntent($queryLower, $queryAscii);

        return match ($intent) {
            'greeting' => $this->handleGreeting($user, $catalogSummary),
            'my_courses' => $this->handleMyCourses($user, $userProgress, $catalogSummary),
            'all_courses' => $this->handleAllCourses($catalogSummary),
            'free_courses' => $this->handleFreeCourses($catalogSummary),
            'roadmap' => $this->handleRoadmap($catalogSummary, $relevantCourses),
            'quizzes' => $this->handleQuizzes(),
            default => $this->handleSearch($query, $relevantCourses, $catalogSummary),
        };
    }

    /**
     * Nhận diện ý định (Intent Detection) bằng heuristics.
     */
    protected function detectIntent(string $lower, string $ascii): string
    {
        if (preg_match('/^(xin chào|chào|hello|hi|hey|alo|bạn là ai|giúp tôi|trợ giúp)/i', $lower)
            || $lower === 'hi' || $lower === 'hello') {
            return 'greeting';
        }

        if (str_contains($lower, 'khóa học của tôi')
            || str_contains($lower, 'khoa hoc cua toi')
            || str_contains($lower, 'tôi đang học')
            || str_contains($lower, 'toi dang hoc')
            || str_contains($lower, 'tiến độ')
            || str_contains($lower, 'tien do')
            || str_contains($lower, 'bài tiếp theo')
            || str_contains($lower, 'học tiếp')) {
            return 'my_courses';
        }

        if (str_contains($lower, 'các khóa học hiện có')
            || str_contains($lower, 'danh sách khóa học')
            || str_contains($lower, 'tất cả khóa học')
            || str_contains($lower, 'có những khóa nào')
            || str_contains($lower, 'toàn bộ khóa học')
            || str_contains($lower, 'khoa hoc hien co')) {
            return 'all_courses';
        }

        if (str_contains($lower, 'miễn phí')
            || str_contains($lower, 'mien phi')
            || str_contains($lower, 'free')) {
            return 'free_courses';
        }

        if (str_contains($lower, 'lộ trình')
            || str_contains($lower, 'lo trinh')
            || str_contains($lower, 'tư vấn')
            || str_contains($lower, 'tu van')
            || str_contains($lower, 'người mới')
            || str_contains($lower, 'nguoi moi')
            || str_contains($lower, 'mới bắt đầu')
            || str_contains($lower, 'nên học gì')) {
            return 'roadmap';
        }

        if (str_contains($lower, 'quiz')
            || str_contains($lower, 'trắc nghiệm')
            || str_contains($lower, 'trac nghiem')
            || str_contains($lower, 'thi thử')
            || str_contains($lower, 'bài kiểm tra')) {
            return 'quizzes';
        }

        return 'search';
    }

    /**
     * Chào hỏi ngắn gọn
     */
    protected function handleGreeting(?User $user, array $catalogSummary): array
    {
        $userName = $user ? " **{$user->name}**" : '';
        $totalCourses = $catalogSummary['total_courses'] ?? 0;
        $freeCount = $catalogSummary['free_courses_count'] ?? 0;

        $msg = "Xin chào{$userName}! Tôi là **Trợ lý AI** của **Ctrl C+V LMS**.\n\n";
        $msg .= "Hệ thống hiện có **{$totalCourses} khóa học** (gồm **{$freeCount} khóa miễn phí**) và kho đề thi trắc nghiệm thực tế.\n\n";
        $msg .= "**Bạn có thể tra cứu nhanh:**\n";
        $msg .= "- Danh sách khóa học & học phí\n";
        $msg .= "- Lộ trình học tập phù hợp cho người mới\n";
        $msg .= "- Tiến độ bài học cá nhân\n";
        $msg .= "- Luyện đề thi trắc nghiệm\n\n";
        $msg .= 'Bạn cần tìm khóa học hoặc thông tin gì?';

        return [
            'message' => $msg,
            'intent' => 'greeting',
            'suggested_chips' => [
                'Tất cả khóa học',
                'Lộ trình học',
                'Khóa học của tôi',
                'Khóa học miễn phí',
            ],
        ];
    }

    /**
     * Khóa học của tôi & tiến độ (gọn gàng, rõ ý)
     */
    protected function handleMyCourses(?User $user, array $userProgress, array $catalogSummary): array
    {
        if (! $user) {
            $msg = "**Bạn chưa đăng nhập**\n\n";
            $msg .= 'Vui lòng [Đăng nhập]('.route('login').') hoặc [Đăng ký]('.route('register').") để theo dõi tiến độ học tập và bài học tiếp theo.\n\n";
            $msg .= "**Khóa học tiêu biểu:**\n";

            $coursesList = array_slice($catalogSummary['courses_list'] ?? [], 0, 3);
            foreach ($coursesList as $c) {
                $msg .= "- [{$c['title']}]({$c['url']}) — Giảng viên: **{$c['teacher']}** • **{$c['price']}**\n";
            }

            return [
                'message' => $msg,
                'intent' => 'my_courses',
                'suggested_chips' => [
                    'Tất cả khóa học',
                    'Khóa học miễn phí',
                ],
            ];
        }

        $enrolledCourses = $userProgress['enrolled_courses'] ?? [];
        if (empty($enrolledCourses)) {
            $msg = "Chào **{$user->name}**, bạn chưa đăng ký khóa học nào trên hệ thống.\n\n";
            $msg .= "**Gợi ý bắt đầu:**\n";

            $coursesList = array_slice($catalogSummary['courses_list'] ?? [], 0, 3);
            foreach ($coursesList as $c) {
                $msg .= "- [{$c['title']}]({$c['url']}) — **{$c['teacher']}** • **{$c['price']}**\n";
            }
            $msg .= "\nXem thêm tại: [Tất cả khóa học](".route('courses.index').')';

            return [
                'message' => $msg,
                'intent' => 'my_courses',
                'suggested_chips' => [
                    'Tất cả khóa học',
                    'Lộ trình học',
                ],
            ];
        }

        $msg = "**Tiến độ học tập của {$user->name}** (".count($enrolledCourses)." khóa học):\n\n";

        foreach ($enrolledCourses as $course) {
            $percent = $course['progress_percent'];
            $completed = $course['completed_lessons'];
            $total = $course['total_lessons'];

            $msg .= "- [{$course['title']}]({$course['url']})\n";
            $msg .= "  Tiến độ: **{$percent}%** ({$completed}/{$total} bài)\n";

            if (! empty($course['next_lesson'])) {
                $next = $course['next_lesson'];
                $msg .= "  Bài tiếp theo: [{$next['title']}]({$next['url']})\n";
            } else {
                $msg .= "  Trạng thái: Đã hoàn thành toàn bộ bài học\n";
            }
        }

        return [
            'message' => $msg,
            'intent' => 'my_courses',
            'suggested_chips' => [
                'Lộ trình học',
                'Luyện thi trắc nghiệm',
                'Tất cả khóa học',
            ],
        ];
    }

    /**
     * Danh sách khóa học (gọn gàng, kèm thống kê đánh giá & học viên)
     */
    protected function handleAllCourses(array $catalogSummary): array
    {
        $courses = $catalogSummary['courses_list'] ?? [];
        $total = $catalogSummary['total_courses'] ?? count($courses);

        if (empty($courses)) {
            return [
                'message' => 'Hệ thống đang cập nhật danh sách khóa học. Vui lòng quay lại sau.',
                'intent' => 'all_courses',
                'suggested_chips' => ['Luyện thi trắc nghiệm'],
            ];
        }

        $msg = "**Danh sách khóa học hiện có ({$total} khóa):**\n\n";

        foreach ($courses as $index => $c) {
            $num = $index + 1;
            $rating = number_format($c['average_rating'] ?? 5.0, 1);
            $revCount = (int) ($c['reviews_count'] ?? 0);
            $enrollCount = (int) ($c['enrollments_count'] ?? 0);

            $msg .= "{$num}. [{$c['title']}]({$c['url']})\n";
            $msg .= "   Giảng viên: **{$c['teacher']}** • ⭐ {$rating} ({$revCount} đánh giá) • 👥 {$enrollCount} học viên • **{$c['price']}** ({$c['lessons_count']} bài)\n";
        }

        $msg .= "\nNhấp vào tên khóa học để xem nội dung chi tiết.";

        return [
            'message' => $msg,
            'intent' => 'all_courses',
            'suggested_chips' => [
                'Khóa học miễn phí',
                'Lộ trình học',
                'Khóa học của tôi',
            ],
        ];
    }

    /**
     * Khóa học miễn phí
     */
    protected function handleFreeCourses(array $catalogSummary): array
    {
        $courses = $catalogSummary['courses_list'] ?? [];
        $freeCourses = array_filter($courses, fn ($c) => $c['price'] === 'Miễn phí' || str_contains(mb_strtolower($c['price']), 'miễn phí'));

        if (empty($freeCourses)) {
            $msg = 'Hiện tại các khóa học đang có mức học phí tượng trưng. Bạn có thể xem toàn bộ tại: [Tất cả khóa học]('.route('courses.index').').';

            return [
                'message' => $msg,
                'intent' => 'free_courses',
                'suggested_chips' => ['Tất cả khóa học'],
            ];
        }

        $msg = "**Khóa học miễn phí:**\n\n";

        foreach ($freeCourses as $c) {
            $msg .= "- [{$c['title']}]({$c['url']})\n";
            $msg .= "  Giảng viên: **{$c['teacher']}** • {$c['lessons_count']} bài giảng\n";
        }

        return [
            'message' => $msg,
            'intent' => 'free_courses',
            'suggested_chips' => [
                'Lộ trình học',
                'Tất cả khóa học',
                'Khóa học của tôi',
            ],
        ];
    }

    /**
     * Lộ trình học tập
     */
    protected function handleRoadmap(array $catalogSummary, array $relevantCourses): array
    {
        $courses = $catalogSummary['courses_list'] ?? [];

        $msg = "**Lộ trình học tập đề xuất (3 bước):**\n\n";

        $msg .= "1. **Nền tảng & Cú pháp:**\n";
        if (! empty($courses)) {
            $first = $courses[0];
            $msg .= "   - Học: [{$first['title']}]({$first['url']}) ({$first['price']})\n";
        } else {
            $msg .= "   - Nắm vững kiến thức cơ bản và tư duy logic.\n";
        }

        $msg .= "2. **Thực hành Dự án:**\n";
        if (count($courses) > 1) {
            $second = $courses[1];
            $msg .= "   - Học: [{$second['title']}]({$second['url']}) ({$second['price']})\n";
        } else {
            $msg .= "   - Xây dựng ứng dụng hoàn chỉnh và tương tác cơ sở dữ liệu.\n";
        }

        $msg .= "3. **Đánh giá Kiến thức:**\n";
        $msg .= '   - Làm bài kiểm tra tại [Thi Trắc Nghiệm / Quizzes]('.route('student.quizzes.index').") để củng cố phản xạ.\n";

        return [
            'message' => $msg,
            'intent' => 'roadmap',
            'suggested_chips' => [
                'Tất cả khóa học',
                'Khóa học miễn phí',
                'Luyện thi trắc nghiệm',
            ],
        ];
    }

    /**
     * Thi trắc nghiệm
     */
    protected function handleQuizzes(): array
    {
        $url = route('student.quizzes.index');
        $msg = "**Hệ thống thi trắc nghiệm Ctrl C+V:**\n\n";
        $msg .= "- Tự động bấm giờ và tính điểm tức thì\n";
        $msg .= "- Xem lại lời giải thích chi tiết sau khi nộp bài\n";
        $msg .= "- Luyện đề theo từng khóa học hoặc đề thi tổng hợp\n\n";
        $msg .= "Truy cập ngay: [Vào Trang Thi Trắc Nghiệm]({$url})";

        return [
            'message' => $msg,
            'intent' => 'quizzes',
            'suggested_chips' => [
                'Tất cả khóa học',
                'Lộ trình học',
            ],
        ];
    }

    /**
     * Tìm kiếm và phân tích số liệu khóa học (Analytical Search Logic)
     */
    protected function handleSearch(string $query, array $relevantCourses, array $catalogSummary): array
    {
        if (empty($relevantCourses)) {
            $msg = "Không tìm thấy khóa học khớp với từ khóa **\"{$query}\"**.\n\n";
            $msg .= "**Khóa học gợi ý:**\n";

            $popular = array_slice($catalogSummary['courses_list'] ?? [], 0, 3);
            foreach ($popular as $c) {
                $rating = number_format($c['average_rating'] ?? 5.0, 1);
                $revCount = (int) ($c['reviews_count'] ?? 0);
                $enrollCount = (int) ($c['enrollments_count'] ?? 0);
                $msg .= "- [{$c['title']}]({$c['url']}) — **{$c['teacher']}** • ⭐ {$rating} ({$revCount} đánh giá) • 👥 {$enrollCount} học viên • **{$c['price']}**\n";
            }

            $msg .= "\nXem danh mục đầy đủ: [Tất cả khóa học](".route('courses.index').')';

            return [
                'message' => $msg,
                'intent' => 'search',
                'suggested_chips' => [
                    'Tất cả khóa học',
                    'Lộ trình học',
                    'Khóa học miễn phí',
                ],
            ];
        }

        $count = count($relevantCourses);
        $sortCriteria = $this->contextService?->detectSortingCriteria($query);

        // Sinh tiêu đề tự nhiên theo đúng tiêu chí và số lượng yêu cầu
        $title = match ($sortCriteria) {
            'most_reviews' => ($count === 1) ? '**Khóa học có nhiều lượt đánh giá nhất trên Ctrl C+V:**' : "**Top {$count} khóa học có nhiều lượt đánh giá nhất:**",
            'highest_rating' => ($count === 1) ? '**Khóa học có điểm đánh giá cao nhất trên Ctrl C+V:**' : "**Top {$count} khóa học được đánh giá cao nhất:**",
            'most_enrolled' => ($count === 1) ? '**Khóa học có số lượng học viên đông nhất:**' : "**Top {$count} khóa học đông học viên nhất:**",
            'most_lessons' => ($count === 1) ? '**Khóa học có số lượng bài giảng nhiều nhất:**' : "**Top {$count} khóa học nhiều bài giảng nhất:**",
            'cheapest' => ($count === 1) ? '**Khóa học có mức học phí tiết kiệm nhất:**' : '**Các khóa học có học phí tiết kiệm nhất:**',
            'most_expensive' => ($count === 1) ? '**Khóa học cao cấp nhất trên hệ thống:**' : '**Các khóa học tiêu biểu trên hệ thống:**',
            'newest' => ($count === 1) ? '**Khóa học mới nhất trên Ctrl C+V:**' : '**Các khóa học mới nhất trên Ctrl C+V:**',
            default => ($count === 1) ? '**Khóa học phù hợp nhất với yêu cầu của bạn:**' : "**Tìm thấy {$count} khóa học phù hợp:**",
        };

        $msg = "{$title}\n\n";

        foreach ($relevantCourses as $index => $c) {
            $num = $index + 1;
            $rating = number_format($c['average_rating'] ?? 5.0, 1);
            $revCount = (int) ($c['reviews_count'] ?? 0);
            $enrollCount = (int) ($c['enrollments_count'] ?? 0);
            $lesCount = (int) ($c['lessons_count'] ?? 0);
            $priceStr = $c['formatted_price'] ?? ($c['price'] > 0 ? number_format($c['price']).' đ' : 'Miễn phí');

            if ($count === 1) {
                $msg .= "- [{$c['title']}]({$c['url']})\n";
                $msg .= "  Giảng viên: **{$c['teacher']}** • ⭐ **{$rating}/5.0** ({$revCount} đánh giá) • 👥 **{$enrollCount} học viên** • {$lesCount} bài • **{$priceStr}**\n";
                if (! empty($c['description'])) {
                    $msg .= "  Mô tả: {$c['description']}\n";
                }
            } else {
                $msg .= "{$num}. [{$c['title']}]({$c['url']})\n";
                $msg .= "   Giảng viên: **{$c['teacher']}** • ⭐ {$rating} ({$revCount} đánh giá) • 👥 {$enrollCount} học viên • {$lesCount} bài • **{$priceStr}**\n";
            }
        }

        return [
            'message' => $msg,
            'intent' => 'search',
            'suggested_chips' => [
                'Lộ trình học',
                'Tất cả khóa học',
                'Khóa học của tôi',
            ],
        ];
    }
}
