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
            'heartbreak' => $this->handleHeartbreak($query, $relevantCourses, $catalogSummary),
            'teacher_guide' => $this->handleTeacherGuide($query, $user),
            'minigame' => $this->handleMinigame($query, $user),
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
        if (preg_match('/^(xin\s*chao[p]?|chao[p]?|hello+|helo+|hi+|hey+|alo+|bạn là ai|giúp tôi|trợ giúp)/i', $lower)
            || preg_match('/^(xin\s*chao[p]?|chao[p]?|hello+|helo+|hi+|hey+|alo+)/i', $ascii)
            || $lower === 'hi' || $lower === 'hello') {
            return 'greeting';
        }

        if ($this->contextService?->isHeartbreakOrVenting($lower)
            || preg_match('/(chia tay|bo da|that tinh|nguoi yeu|cam sung|nyc|nguoi yeu cu|yeu duong|tinh yeu)/i', $ascii)
            || preg_match('/(buon qua|chan qua|chan doi|co don|dau long)/i', $ascii)
            || str_contains($lower, 'chia tay')
            || str_contains($lower, 'thất tình')
            || str_contains($lower, 'bồ đá')
            || str_contains($lower, 'người yêu')
            || str_contains($lower, 'buồn quá')
            || str_contains($lower, 'chán đời')) {
            return 'heartbreak';
        }

        if ($this->contextService?->isTeacherInquiry($lower)
            || preg_match('/(tao|them|huong dan|cach|lam sao|xem)\s*(bai giang|bai hoc|khoa hoc|quiz|de thi|de trac nghiem)/i', $ascii)
            || preg_match('/(panel|bang dieu khien|trang|kenh|muc)\s*(giang vien|gv|instructor|teacher)/i', $ascii)
            || preg_match('/(vao|mo)\s*(gv|giang vien|instructor)/i', $ascii)
            || str_contains($lower, 'tạo bài giảng')
            || str_contains($lower, 'tạo bài học')
            || str_contains($lower, 'thêm bài giảng')
            || str_contains($lower, 'thêm bài học')
            || str_contains($lower, 'tạo khóa học')
            || str_contains($lower, 'tạo đề thi')
            || str_contains($lower, 'tạo quiz')
            || str_contains($lower, 'tạo trắc nghiệm')
            || str_contains($lower, 'bảng điều khiển giảng viên')
            || str_contains($lower, 'panel gv')
            || str_contains($lower, 'panel giảng viên')
            || str_contains($lower, 'trang giảng viên')) {
            return 'teacher_guide';
        }

        if ($this->contextService?->isMinigameInquiry($lower)
            || preg_match('/(minigame|mini game|gold quest|blooket|cuop vang|mo ruong|ruong vang|swap|doi vang|ma pin|dau truong|phong choi|linh vat|blook)/i', $ascii)
            || str_contains($lower, 'minigame')
            || str_contains($lower, 'gold quest')
            || str_contains($lower, 'cướp vàng')
            || str_contains($lower, 'mở rương')
            || str_contains($lower, 'blooket')
            || str_contains($lower, 'mã pin')) {
            return 'minigame';
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
                'Lập trình Web',
                'Lộ trình học',
                'Khóa học của tôi',
                'Khóa học miễn phí',
            ],
        ];
    }

    /**
     * Phản hồi hài hước "thêm muối" khi người dùng tâm sự thất tình, chia tay, buồn bã.
     *
     * @return array{
     *     message: string,
     *     intent: string,
     *     suggested_chips: array<int, string>
     * }
     */
    protected function handleHeartbreak(string $query, array $relevantCourses, array $catalogSummary): array
    {
        // Chọn một khóa học tốt nhất để gợi ý
        $course = ! empty($relevantCourses)
            ? $relevantCourses[0]
            : (! empty($catalogSummary['courses_list']) ? $catalogSummary['courses_list'][0] : null);

        if ($course) {
            $priceText = ! empty($course['price']) ? $course['price'] : ($course['formatted_price'] ?? 'Miễn phí');
            $courseLink = "[{$course['title']}]({$course['url']}) ({$priceText})";
        } else {
            $courseLink = '[Khóa học Lập trình Web]('.route('courses.index').')';
        }

        $msg = "Nghe buồn vậy... 🥺 Nhưng mà nghĩ tích cực lên bạn ơi!\n\n";
        $msg .= "Nếu vậy thì bạn có thể tham gia ngay khóa học {$courseLink} trên Ctrl C+V để quên đi nỗi buồn nhé! 😎😆\n\n";
        $msg .= "Vì khi bạn bắt đầu học, tâm trí và não bộ sẽ dồn hết 100% công lực vào tư duy, viết code và fix bug. Bận rộn và cuốn hút như thế thì đảm bảo bạn chẳng còn thời gian đâu mà bận tâm hay nhớ nhung đến tình yêu nữa!\n\n";
        $msg .= 'Người yêu có thể rời bỏ bạn, chứ kiến thức, kỹ năng và mức lương nghìn đô thì sẽ luôn ở bên bạn. Bắt đầu học ngay để nâng cấp bản thân nhé! 🚀✨';

        return [
            'message' => $msg,
            'intent' => 'heartbreak',
            'suggested_chips' => [
                'Lập trình Web',
                'Lộ trình học',
                'Khóa học miễn phí',
                'Luyện thi trắc nghiệm',
            ],
        ];
    }

    /**
     * Hướng dẫn chính xác thao tác trong Panel Giảng viên (tạo bài giảng, tạo khóa, tạo quiz).
     *
     * @return array{
     *     message: string,
     *     intent: string,
     *     suggested_chips: array<int, string>
     * }
     */
    protected function handleTeacherGuide(string $query, ?User $user): array
    {
        $dashboardUrl = route('instructor.dashboard');
        $createCourseUrl = route('instructor.courses.create');
        $createQuizUrl = route('instructor.quizzes.create');

        $ascii = Str::ascii(mb_strtolower($query, 'UTF-8'));
        $isCourseSpecific = (bool) preg_match('/(tao khoa|mo khoa|them khoa|lap khoa)/i', $ascii);
        $isQuizSpecific = (bool) preg_match('/(quiz|trac nghiem|de thi|tao trac nghiem)/i', $ascii);

        $msg = "Để thực hiện các thao tác quản lý và giảng dạy trên nền tảng **Ctrl C+V LMS**, bạn làm theo các bước chính xác sau:\n\n";
        $msg .= "### Bước 1: Vào Bảng điều khiển Giảng viên (Panel GV)\n";
        $msg .= "1. Bấm vào **ảnh đại diện / tên tài khoản (Profile)** ở **góc trên bên phải** màn hình để mở panel menu tùy chọn.\n";
        $msg .= "2. Trong menu vừa xuất hiện, chọn mục **\"GV\"** để truy cập vào [Bảng điều khiển Giảng viên]({$dashboardUrl}).\n\n";

        $msg .= "### Bước 2: Thao tác chức năng\n";
        if ($isCourseSpecific) {
            $msg .= "**Cách tạo khóa học mới:**\n";
            $msg .= "- Tại góc trên Bảng điều khiển Giảng viên, bấm nút [**+ Tạo khóa học mới**]({$createCourseUrl}).\n";
            $msg .= "- Nhập tiêu đề khóa học, tóm tắt mô tả, ảnh đại diện, cấu hình học phí (hoặc chọn Miễn phí) rồi gửi xét duyệt.\n\n";
        } elseif ($isQuizSpecific) {
            $msg .= "**Cách tạo đề kiểm tra trắc nghiệm (Quiz):**\n";
            $msg .= "- Chuyển sang tab **\"Bài kiểm tra\"** trong Bảng điều khiển Giảng viên.\n";
            $msg .= "- Bấm nút [**+ Tạo bài kiểm tra (quiz)**]({$createQuizUrl}).\n";
            $msg .= "- Bạn có thể thêm từng câu hỏi thủ công hoặc sử dụng tính năng **AI sinh đề thi tự động**.\n\n";
        } else {
            $msg .= "**Cách tạo / thêm bài giảng vào khóa học:**\n";
            $msg .= "- Tại tab **\"Khóa học & Bài học\"**, tìm khóa học bạn muốn thêm bài giảng.\n";
            $msg .= "- Bấm nút **\"+ Bài giảng\"** ở cột thao tác bên phải của khóa học đó (hoặc bấm vào dòng khóa học để mở rộng danh sách bài giảng rồi bấm **\"+ Thêm bài giảng vào khóa này\"**).\n";
            $msg .= "- Điền thông tin: **Tiêu đề bài học**, **Số thứ tự**, **Thời lượng (phút)**, chọn **Loại bài học** (Video / Văn bản / Trắc nghiệm) và bấm **\"Lưu bài giảng\"**.\n\n";
            $msg .= "**Các tính năng giảng viên khác:**\n";
            $msg .= "- **Tạo khóa học mới:** Bấm nút [**+ Tạo khóa học mới**]({$createCourseUrl}) ở góc trên bảng điều khiển.\n";
            $msg .= "- **Tạo bài kiểm tra:** Chuyển sang tab **\"Bài kiểm tra\"** -> bấm [**+ Tạo bài kiểm tra (quiz)**]({$createQuizUrl}).\n";
            $msg .= "- **Xem kết quả học viên:** Chuyển sang tab **\"Kết quả học viên\"** để theo dõi điểm số và lịch sử làm bài.\n\n";
        }

        $msg .= '*(Lưu ý: Bạn cần đăng nhập bằng tài khoản có vai trò Giảng viên để thấy mục "GV" trong panel menu profile góc phải).*';

        return [
            'message' => $msg,
            'intent' => 'teacher_guide',
            'suggested_chips' => [
                'Bảng điều khiển Giảng viên',
                'Tạo khóa học mới',
                'Tạo đề trắc nghiệm',
                'Khóa học của tôi',
            ],
        ];
    }

    /**
     * Xử lý câu hỏi về Minigame Gold Quest (Blooket), cách chơi, luật chơi, mở rương, cướp vàng.
     */
    protected function handleMinigame(string $query, ?User $user): array
    {
        $minigameUrl = route('minigame.index');
        $minigameSummary = $this->contextService?->getMinigameSummary() ?? ['total_quizzes' => 0, 'quizzes_list' => []];
        $totalQuizzes = $minigameSummary['total_quizzes'] ?? 0;

        $msg = "🎮 **Minigame Gold Quest (Đại Chiến Cướp Vàng)** là chế độ thi đấu trắc nghiệm tương tác thời gian thực cực kỳ hấp dẫn trên nền tảng **Ctrl C+V LMS**!\n\n";
        $msg .= "👉 **Truy cập ngay:** [Đấu Trường Minigame]({$minigameUrl})\n\n";
        $msg .= "🏆 **Cách tham gia & Luật chơi:**\n";
        $msg .= "1. **Vào phòng:** Nhập **Mã PIN 6 số** do Giảng viên cung cấp, chọn **Linh vật (Blook)** yêu thích (Vua Vàng, Hải Tặc, Phù Thủy, Phi Hành Gia, Cáo...) và đặt tên hiển thị.\n";
        $msg .= "2. **Trả lời trắc nghiệm:** Làm bài theo tốc độ cá nhân độc lập. Trả lời **ĐÚNG** bạn sẽ được mở 1 trong **3 chiếc rương may mắn**; trả lời **SAI** bị dừng 2 giây xem đáp án đúng rồi tiếp tục.\n";
        $msg .= "3. **Mở Rương Bí Ẩn:**\n";
        $msg .= "   - 💰 **Cộng vàng:** +20, +50, +100 hoặc rương khủng +250 vàng.\n";
        $msg .= "   - 📈 **Nhân hệ số:** Nhân thêm +10% hoặc +25% tổng số vàng.\n";
        $msg .= "   - ⚔️ **Cướp vàng (Steal):** Chọn 1 đối thủ bất kỳ trong phòng để **cướp 10% - 30% vàng** của họ! (Người bị cướp sẽ có rung lắc màn hình và thông báo cảnh báo tức thì).\n";
        $msg .= "   - 🔄 **Tráo đổi (Swap):** Đổi toàn bộ số vàng và thứ hạng hiện tại của bạn với 1 người chơi khác!\n";
        $msg .= "4. **Vinh danh:** Khi hết giờ, hệ thống khóa tương tác và vinh danh Top 1, Top 2, Top 3 trên Bục vinh quang (Podium) với pháo hoa rực rỡ.\n\n";

        if ($user && $user->canManageMinigames()) {
            $msg .= "👨‍🏫 **Dành riêng cho Giảng viên:** Bạn có thể vào tab **\"Quản Lý Phòng Chơi\"** để tạo bộ câu hỏi (thủ công, AI tự động, hoặc dán JSON Blooket), bấm **\"Mở Phòng Cho Lớp Chơi\"**, lấy mã PIN và chiếu máy chiếu cho cả lớp tranh tài!\n\n";
        }

        if ($totalQuizzes > 0) {
            $msg .= "Hiện hệ thống đang có **{$totalQuizzes} bộ đề Minigame** công khai sẵn sàng để bạn trải nghiệm ngay!";
        }

        return [
            'message' => $msg,
            'intent' => 'minigame',
            'suggested_chips' => [
                'Luật chơi Gold Quest',
                'Cách mở rương & cướp vàng',
                'Hướng dẫn Giảng viên tạo phòng',
                'Xem các khóa học hot',
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
                    'Lập trình Web',
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
                    'Lập trình Web',
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
                'Khóa học miễn phí',
            ],
        ];
    }

    /**
     * Phản hồi khi người dùng hỏi chung chung về "tất cả khóa học" / "danh sách khóa học".
     */
    protected function handleAllCourses(array $catalogSummary): array
    {
        $total = $catalogSummary['total_courses'] ?? 0;
        $url = route('courses.index');

        $msg = "Hệ thống Ctrl C+V hiện có **{$total} khóa học** với rất nhiều lĩnh vực và chủ đề phong phú.\n\n";
        $msg .= "Nếu liệt kê toàn bộ thì như vậy sẽ có rất nhiều khóa học. Bạn có thể cho tôi biết bạn đang quan tâm đến lĩnh vực hay công nghệ nào (ví dụ: **Lập trình Web**, **Laravel & PHP**, **Frontend & React**, **Cơ sở dữ liệu**...) không để có thể dễ dàng tìm kiếm khóa học phù hợp hơn cho bạn?\n\n";
        $msg .= "*(Bạn cũng có thể xem trực tiếp toàn bộ danh mục trên web tại [Xem danh mục khóa học]({$url}))*.";

        return [
            'message' => $msg,
            'intent' => 'all_courses',
            'suggested_chips' => [
                'Lập trình Web',
                'Laravel & PHP',
                'Frontend & React',
                'Lộ trình học',
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
            $msg = 'Hiện tại các khóa học đang có mức học phí tượng trưng. Bạn có thể xem toàn bộ tại: [Danh mục khóa học]('.route('courses.index').').';

            return [
                'message' => $msg,
                'intent' => 'free_courses',
                'suggested_chips' => ['Lập trình Web', 'Lộ trình học'],
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
                'Lập trình Web',
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
                'Lập trình Web',
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
                'Lập trình Web',
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
                    'Lập trình Web',
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
                'Lập trình Web',
                'Khóa học của tôi',
            ],
        ];
    }
}
