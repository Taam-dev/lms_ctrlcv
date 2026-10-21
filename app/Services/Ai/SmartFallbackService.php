<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Str;

class SmartFallbackService
{
    /**
     * Sinh câu trả lời thông minh dựa trên dữ liệu database khi máy chủ Local AI offline.
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
        $queryLower = mb_strtolower(trim($query), 'UTF-8');
        $queryAscii = Str::ascii($queryLower);

        // 1. Phân tích intent của người dùng
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
     * Nhận diện ý định (Intent Detection) bằng heuristics chính xác cao.
     */
    protected function detectIntent(string $lower, string $ascii): string
    {
        // Chào hỏi
        if (preg_match('/^(xin chào|chào|hello|hi|hey|alo|bạn là ai|giúp tôi|trợ giúp)/i', $lower)
            || $lower === 'hi' || $lower === 'hello') {
            return 'greeting';
        }

        // Khóa học của tôi / Tiến độ học tập
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

        // Tất cả khóa học / Danh mục
        if (str_contains($lower, 'các khóa học hiện có')
            || str_contains($lower, 'danh sách khóa học')
            || str_contains($lower, 'tất cả khóa học')
            || str_contains($lower, 'có những khóa nào')
            || str_contains($lower, 'toàn bộ khóa học')
            || str_contains($lower, 'khoa hoc hien co')) {
            return 'all_courses';
        }

        // Khóa học miễn phí
        if (str_contains($lower, 'miễn phí')
            || str_contains($lower, 'mien phi')
            || str_contains($lower, 'free')) {
            return 'free_courses';
        }

        // Lộ trình học tập / Tư vấn người mới
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

        // Bài thi trắc nghiệm / Quizzes
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
     * Xử lý câu chào hỏi ban đầu
     */
    protected function handleGreeting(?User $user, array $catalogSummary): array
    {
        $userName = $user ? " **{$user->name}**" : '';
        $totalCourses = $catalogSummary['total_courses'] ?? 0;
        $freeCount = $catalogSummary['free_courses_count'] ?? 0;

        $msg = "👋 Xin chào{$userName}! Mình là **Trợ lý AI Học tập** của nền tảng **Ctrl C+V**.\n\n";
        $msg .= "Hiện tại hệ thống đang có **{$totalCourses} khóa học** (trong đó có **{$freeCount} khóa học miễn phí**) và nhiều bộ đề kiểm tra trắc nghiệm thực chiến.\n\n";
        $msg .= "Bạn có thể hỏi mình những nội dung như:\n";
        $msg .= "- 📚 **Xem các khóa học hiện có** trên hệ thống.\n";
        $msg .= "- 🎯 **Tư vấn lộ trình học** cho người mới bắt đầu hoặc theo chuyên môn.\n";
        $msg .= "- 📖 **Kiểm tra tiến độ học tập** và gợi ý bài học tiếp theo của bạn.\n";
        $msg .= "- 📝 **Luyện thi trắc nghiệm (Quizzes)** nâng cao phản xạ kiến thức.\n\n";
        $msg .= 'Bạn đang quan tâm đến chủ đề hoặc kỹ năng nào hôm nay?';

        return [
            'message' => $msg,
            'intent' => 'greeting',
            'suggested_chips' => [
                '📚 Các khóa học hiện có',
                '🎯 Tư vấn lộ trình học',
                '📖 Khóa học của tôi',
                '🚀 Khóa học miễn phí',
            ],
        ];
    }

    /**
     * Xử lý xem khóa học cá nhân & tiến độ học tập
     */
    protected function handleMyCourses(?User $user, array $userProgress, array $catalogSummary): array
    {
        if (! $user) {
            $msg = "🔒 **Bạn hiện chưa đăng nhập vào hệ thống!**\n\n";
            $msg .= 'Để theo dõi tiến độ hoàn thành bài học, lưu điểm quiz và nhận lộ trình học tập cá nhân hóa, bạn hãy [Đăng nhập]('.route('login').') hoặc [Đăng ký tài khoản mới]('.route('register').") nhé.\n\n";
            $msg .= "Dưới đây là một số khóa học nổi bật mà bạn có thể tham khảo ngay:\n";

            $coursesList = array_slice($catalogSummary['courses_list'] ?? [], 0, 3);
            foreach ($coursesList as $c) {
                $msg .= "- 🎓 [{$c['title']}]({$c['url']}) — Giảng viên: **{$c['teacher']}** ({$c['price']})\n";
            }

            return [
                'message' => $msg,
                'intent' => 'my_courses',
                'suggested_chips' => [
                    '📚 Các khóa học hiện có',
                    '🚀 Khóa học miễn phí',
                ],
            ];
        }

        $enrolledCourses = $userProgress['enrolled_courses'] ?? [];
        if (empty($enrolledCourses)) {
            $msg = "Chào **{$user->name}**, hiện tại bạn **chưa đăng ký khóa học nào** trên hệ thống Ctrl C+V.\n\n";
            $msg .= "Đừng lo lắng, hãy bắt đầu hành trình học tập ngay hôm nay với các khóa học được chọn lọc dành cho bạn:\n\n";

            $coursesList = array_slice($catalogSummary['courses_list'] ?? [], 0, 3);
            foreach ($coursesList as $c) {
                $msg .= "- 🚀 [{$c['title']}]({$c['url']}) ({$c['price']}) — **{$c['teacher']}**\n";
            }
            $msg .= "\n👉 Xem thêm tại danh mục: [Tất cả khóa học](".route('courses.index').')';

            return [
                'message' => $msg,
                'intent' => 'my_courses',
                'suggested_chips' => [
                    '📚 Các khóa học hiện có',
                    '🎯 Tư vấn lộ trình học',
                ],
            ];
        }

        $msg = "📊 **Tiến độ học tập hiện tại của {$user->name}:**\n\n";
        $msg .= 'Bạn đang tham gia **'.count($enrolledCourses)." khóa học**:\n\n";

        foreach ($enrolledCourses as $course) {
            $percent = $course['progress_percent'];
            $completed = $course['completed_lessons'];
            $total = $course['total_lessons'];

            $progressBar = $this->renderAsciiProgress($percent);

            $msg .= "### 📖 [{$course['title']}]({$course['url']})\n";
            $msg .= "- **Tiến độ**: {$progressBar} **{$percent}%** ({$completed}/{$total} bài đã hoàn thành)\n";

            if (! empty($course['next_lesson'])) {
                $next = $course['next_lesson'];
                $msg .= "- 🎯 **Bài tiếp theo gợi ý**: [{$next['title']}]({$next['url']})\n";
            } else {
                $msg .= "- 🎉 **Chúc mừng!** Bạn đã hoàn thành toàn bộ bài học trong khóa này.\n";
            }
            $msg .= "\n";
        }

        $msg .= '💡 *Mẹo: Hãy dành 30 phút mỗi ngày để hoàn thành ít nhất 1 bài học nhé!*';

        return [
            'message' => $msg,
            'intent' => 'my_courses',
            'suggested_chips' => [
                '🎯 Tư vấn lộ trình học',
                '📝 Luyện thi trắc nghiệm',
                '📚 Các khóa học khác',
            ],
        ];
    }

    /**
     * Xử lý hiển thị toàn bộ danh sách khóa học hiện có
     */
    protected function handleAllCourses(array $catalogSummary): array
    {
        $courses = $catalogSummary['courses_list'] ?? [];
        $total = $catalogSummary['total_courses'] ?? count($courses);

        if (empty($courses)) {
            return [
                'message' => 'Hiện tại hệ thống đang cập nhật danh sách khóa học mới. Bạn vui lòng quay lại sau ít phút hoặc liên hệ Quản trị viên nhé!',
                'intent' => 'all_courses',
                'suggested_chips' => ['📝 Luyện thi trắc nghiệm'],
            ];
        }

        $msg = "📚 **Danh sách các khóa học hiện có trên Ctrl C+V ({$total} khóa học):**\n\n";

        foreach ($courses as $index => $c) {
            $num = $index + 1;
            $msg .= "{$num}. **[{$c['title']}]({$c['url']})**\n";
            $msg .= "   - 👨‍🏫 Giảng viên: **{$c['teacher']}**\n";
            $msg .= "   - 💰 Học phí: **{$c['price']}** | 📝 Bài giảng: **{$c['lessons_count']} bài**\n\n";
        }

        $msg .= '👉 Bạn có thể nhấp trực tiếp vào tên khóa học để xem nội dung chi tiết hoặc [Khám phá Thư viện khóa học]('.route('courses.index').').';

        return [
            'message' => $msg,
            'intent' => 'all_courses',
            'suggested_chips' => [
                '🚀 Khóa học miễn phí',
                '🎯 Tư vấn lộ trình học',
                '📖 Khóa học của tôi',
            ],
        ];
    }

    /**
     * Xử lý lọc khóa học miễn phí
     */
    protected function handleFreeCourses(array $catalogSummary): array
    {
        $courses = $catalogSummary['courses_list'] ?? [];
        $freeCourses = array_filter($courses, fn ($c) => $c['price'] === 'Miễn phí' || str_contains(mb_strtolower($c['price']), 'miễn phí'));

        if (empty($freeCourses)) {
            $msg = 'Hiện tại các khóa học trên hệ thống đều có mức học phí tượng trưng cực kỳ ưu đãi. Bạn có thể tham khảo toàn bộ danh sách tại: [Khám phá khóa học]('.route('courses.index').').';

            return [
                'message' => $msg,
                'intent' => 'free_courses',
                'suggested_chips' => ['📚 Các khóa học hiện có'],
            ];
        }

        $msg = "🚀 **Danh sách các khóa học MIỄN PHÍ chất lượng cao dành cho bạn:**\n\n";

        foreach ($freeCourses as $c) {
            $msg .= "- 🌟 **[{$c['title']}]({$c['url']})**\n";
            $msg .= "  - 👨‍🏫 Giảng viên: **{$c['teacher']}**\n";
            $msg .= "  - 📑 Số lượng bài giảng: **{$c['lessons_count']} bài** (Học trọn đời không mất phí)\n\n";
        }

        $msg .= 'Đăng ký ngay hôm nay để không bỏ lỡ kiến thức bổ ích nhé!';

        return [
            'message' => $msg,
            'intent' => 'free_courses',
            'suggested_chips' => [
                '🎯 Tư vấn lộ trình học',
                '📚 Tất cả khóa học',
                '📖 Khóa học của tôi',
            ],
        ];
    }

    /**
     * Xử lý tư vấn lộ trình học tập
     */
    protected function handleRoadmap(array $catalogSummary, array $relevantCourses): array
    {
        $courses = $catalogSummary['courses_list'] ?? [];

        $msg = "🎯 **Gợi ý Lộ trình học tập chuẩn mực tại Ctrl C+V:**\n\n";
        $msg .= "Để đạt hiệu quả học tập cao nhất, bạn nên đi theo lộ trình 3 giai đoạn tinh gọn sau:\n\n";

        $msg .= "### 🟢 Giai đoạn 1: Nắm vững Nền tảng & Cú pháp\n";
        $msg .= "- Bắt đầu với các khóa học căn bản, làm quen với tư duy lập trình và cấu trúc dữ liệu.\n";
        if (! empty($courses)) {
            $first = $courses[0];
            $msg .= "- Khóa học gợi ý: [{$first['title']}]({$first['url']}) ({$first['price']})\n";
        }
        $msg .= "\n";

        $msg .= "### 🟡 Giai đoạn 2: Thực chiến Dự án & Kỹ năng Nâng cao\n";
        $msg .= "- Áp dụng kiến thức vào xây dựng ứng dụng thực tế, hiểu rõ luồng xử lý và tương tác cơ sở dữ liệu.\n";
        if (count($courses) > 1) {
            $second = $courses[1];
            $msg .= "- Khóa học gợi ý: [{$second['title']}]({$second['url']}) ({$second['price']})\n";
        }
        $msg .= "\n";

        $msg .= "### 🔴 Giai đoạn 3: Kiểm tra Đánh giá & Rèn luyện Phản xạ\n";
        $msg .= '- Tham gia làm các bài trắc nghiệm tính giờ tại hệ thống [Thi Trắc Nghiệm / Quizzes]('.route('student.quizzes.index').") để củng cố lý thuyết và sẵn sàng cho các bài kiểm tra thực tế.\n\n";

        $msg .= '💡 *Bạn có thể bấm vào từng khóa học ở trên để xem chi tiết đề cương và bắt đầu học ngay!*';

        return [
            'message' => $msg,
            'intent' => 'roadmap',
            'suggested_chips' => [
                '📚 Các khóa học hiện có',
                '🚀 Khóa học miễn phí',
                '📝 Làm bài thi quizzes',
            ],
        ];
    }

    /**
     * Xử lý điều hướng tới hệ thống Quizzes
     */
    protected function handleQuizzes(): array
    {
        $url = route('student.quizzes.index');
        $msg = "📝 **Hệ thống Thi Trắc Nghiệm Trực Tuyến Ctrl C+V**\n\n";
        $msg .= "Nền tảng cung cấp kho bài kiểm tra trắc nghiệm đa dạng với các tính năng:\n";
        $msg .= "- ⏱️ **Tự động đếm giờ & tính điểm** chính xác, minh bạch.\n";
        $msg .= "- 📊 **Xem lại kết quả & giải thích chi tiết** từng câu hỏi ngay sau khi nộp bài.\n";
        $msg .= "- 🔄 Hỗ trợ làm bài trắc nghiệm theo từng khóa học hoặc bài thi độc lập.\n\n";
        $msg .= "👉 Nhấn vào đây để thử sức ngay: **[Vào Trang Thi Trắc Nghiệm]({$url})**";

        return [
            'message' => $msg,
            'intent' => 'quizzes',
            'suggested_chips' => [
                '📚 Các khóa học hiện có',
                '🎯 Tư vấn lộ trình học',
            ],
        ];
    }

    /**
     * Xử lý tìm kiếm thông minh từ khóa trong danh mục
     */
    protected function handleSearch(string $query, array $relevantCourses, array $catalogSummary): array
    {
        if (empty($relevantCourses)) {
            $msg = "🔎 Xin lỗi bạn, hệ thống chưa tìm thấy khóa học nào khớp chính xác với từ khóa **\"{$query}\"**.\n\n";
            $msg .= "Tuy nhiên, bạn có thể tham khảo các khóa học đang được học viên yêu thích nhất trên Ctrl C+V:\n\n";

            $popular = array_slice($catalogSummary['courses_list'] ?? [], 0, 3);
            foreach ($popular as $c) {
                $msg .= "- 📌 **[{$c['title']}]({$c['url']})** — Giảng viên: **{$c['teacher']}** ({$c['price']})\n";
            }

            $msg .= "\n👉 Hoặc bạn có thể truy cập [Trang danh mục tất cả khóa học](".route('courses.index').') để tra cứu đầy đủ hơn.';

            return [
                'message' => $msg,
                'intent' => 'search',
                'suggested_chips' => [
                    '📚 Các khóa học hiện có',
                    '🎯 Tư vấn lộ trình học',
                    '🚀 Khóa học miễn phí',
                ],
            ];
        }

        $msg = '🔍 **Tìm thấy '.count($relevantCourses)." khóa học phù hợp với yêu cầu của bạn:**\n\n";

        foreach ($relevantCourses as $index => $c) {
            $num = $index + 1;
            $msg .= "### {$num}. [{$c['title']}]({$c['url']})\n";
            $msg .= "- 👨‍🏫 **Giảng viên**: {$c['teacher']}\n";
            $msg .= "- 💰 **Học phí**: **{$c['formatted_price']}** | 📑 **Số bài học**: {$c['lessons_count']} bài\n";

            if (! empty($c['average_rating']) && $c['average_rating'] > 0) {
                $msg .= "- ⭐ **Đánh giá**: {$c['average_rating']}/5.0 ({$c['reviews_count']} lượt đánh giá)\n";
            }

            if (! empty($c['description'])) {
                $msg .= "- 📝 *{$c['description']}*\n";
            }

            if (! empty($c['key_lessons'])) {
                $lessonsPreview = implode(', ', array_slice($c['key_lessons'], 0, 3));
                $msg .= "- 🔑 **Nội dung nổi bật**: {$lessonsPreview}...\n";
            }

            $msg .= "\n";
        }

        $msg .= '💡 *Nhấp vào liên kết từng khóa học để xem chi tiết giáo trình và đăng ký học ngay.*';

        return [
            'message' => $msg,
            'intent' => 'search',
            'suggested_chips' => [
                '🎯 Tư vấn lộ trình học',
                '📚 Tất cả khóa học',
                '📖 Khóa học của tôi',
            ],
        ];
    }

    /**
     * Tạo thanh tiến độ ASCII đẹp mắt
     */
    protected function renderAsciiProgress(int $percent): string
    {
        $percent = max(0, min(100, $percent));
        $bars = (int) round($percent / 10);
        $empty = 10 - $bars;

        return '['.str_repeat('█', $bars).str_repeat('░', $empty).']';
    }
}
