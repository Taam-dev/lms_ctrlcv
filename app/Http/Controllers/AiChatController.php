<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiClientManager;
use App\Services\Ai\AiGuardrailService;
use App\Services\Ai\LmsContextService;
use App\Services\Ai\SmartFallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AiChatController extends Controller
{
    public function __construct(
        protected LmsContextService $contextService,
        protected AiClientManager $aiClient,
        protected SmartFallbackService $fallbackService,
        protected AiGuardrailService $guardrailService
    ) {}

    /**
     * Xử lý gửi tin nhắn trò chuyện với Multi-API Key Failover hoặc Smart Fallback.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $userMessage = trim($validated['message']);
        $rawHistory = $validated['history'] ?? [];
        $user = $request->user();

        // 1. Tầng 1: Kiểm tra Scope Guardrail (Từ chối câu hỏi ngoài lề trang web)
        if ($this->guardrailService->isOffTopic($userMessage)) {
            return response()->json($this->guardrailService->getRefusalResponse());
        }

        try {
            // 2. Thu thập dữ liệu LMS thời gian thực với phân tích số liệu chính xác
            $relevantCourses = $this->contextService->findRelevantCourses($userMessage, $user);
            $catalogSummary = $this->contextService->getCatalogSummary();
            $userProgress = $this->contextService->getUserLearningProgress($user);

            // 3. Khử trùng lặp lịch sử trò chuyện (Anti-Duplicate Messages)
            $cleanedHistory = $this->deduplicateHistory($rawHistory);
            $maxTurns = (int) config('ai_chat.max_history_turns', 8);
            $recentHistory = array_slice($cleanedHistory, -$maxTurns);

            // 4. Chuẩn bị ngữ cảnh và System Prompt tuân thủ Guardrail
            $systemPrompt = $this->contextService->buildSystemPrompt(
                $user,
                $relevantCourses,
                $catalogSummary,
                $userProgress
            );

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($recentHistory as $turn) {
                $messages[] = [
                    'role' => $turn['role'],
                    'content' => (string) $turn['content'],
                ];
            }

            $messages[] = [
                'role' => 'user',
                'content' => $userMessage,
            ];

            // 5. Gửi yêu cầu qua hệ thống Multi-API Key Failover
            $aiResponse = $this->aiClient->chat($messages);

            if ($aiResponse['success'] && ! empty($aiResponse['content'])) {
                $content = $aiResponse['content'];

                // Nếu người dùng chỉ chào hỏi mà model AI phản hồi nhầm câu từ chối, tự động chuyển về câu chào thân thiện
                if ($this->contextService->isGreetingOrCasual($userMessage) && trim($content) === AiGuardrailService::REFUSAL_MESSAGE) {
                    $userName = $user ? " **{$user->name}**" : '';
                    $content = "Xin chào{$userName}! Tôi là Trợ lý AI Cố vấn Học tập của **Ctrl C+V LMS**.\n\nBạn cần tìm khóa học, tư vấn lộ trình hay giải đáp thắc mắc nào trên hệ thống không?";
                }

                // Nếu người dùng hỏi nghiệp vụ giảng viên nhưng model AI chưa nêu rõ bước Profile góc phải -> GV
                if ($this->contextService->isTeacherInquiry($userMessage)) {
                    $contentLower = mb_strtolower($content);
                    $contentAscii = Str::ascii($contentLower);
                    $hasProfileRef = str_contains($contentLower, 'profile') || str_contains($contentLower, 'góc') || str_contains($contentAscii, 'goc');
                    $hasGvRef = str_contains($contentLower, 'gv') || str_contains($contentLower, 'giảng viên') || str_contains($contentAscii, 'giang vien');

                    if (! ($hasProfileRef && $hasGvRef)) {
                        $fallback = $this->fallbackService->generateResponse($userMessage, $user, [], $catalogSummary, $userProgress);
                        $content = $fallback['message'];
                    }
                }

                // Nếu người dùng hỏi chung chung về "tất cả khóa học", đảm bảo không liệt kê lan man mà hỏi về lĩnh vực quan tâm
                if ($this->contextService->isGeneralCatalogInquiry($userMessage)) {
                    $contentLower = mb_strtolower($content);
                    if (! str_contains($contentLower, 'lĩnh vực') && ! str_contains($contentLower, 'quan tâm')) {
                        $fallback = $this->fallbackService->generateResponse($userMessage, $user, [], $catalogSummary, $userProgress);
                        $content = $fallback['message'];
                    }
                }

                // Nếu người dùng tâm sự chuyện buồn/chia tay nhưng model AI phản hồi máy móc cứng nhắc
                if ($this->contextService->isHeartbreakOrVenting($userMessage)) {
                    $contentLower = mb_strtolower($content);
                    $isRobotic = str_contains($contentLower, 'không thể tham gia vào các vấn đề đời tư')
                        || str_contains($contentLower, 'đời tư hay tình cảm')
                        || str_contains($contentLower, 'không thể hỗ trợ về chuyện tình cảm')
                        || str_contains($contentLower, 'vấn đề tình cảm cá nhân')
                        || str_contains($contentLower, 'chỉ là trợ lý ai')
                        || (! str_contains($contentLower, '😎') && ! str_contains($contentLower, 'buồn'));

                    if ($isRobotic) {
                        $fallback = $this->fallbackService->generateResponse($userMessage, $user, $relevantCourses, $catalogSummary, $userProgress);
                        $content = $fallback['message'];
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => $content,
                    'source' => $aiResponse['provider_id'] ?? 'ai_provider',
                    'model' => $aiResponse['model'],
                    'provider' => $aiResponse['provider'] ?? 'AI Provider',
                    'relevant_courses' => $relevantCourses,
                    'suggested_chips' => $this->generateSuggestedChips($userMessage, $user),
                ]);
            }

            // 6. Tự động kích hoạt Smart Offline Fallback nếu toàn bộ API Key lỗi/hết hạn
            Log::info('All AI API keys failed or are offline. Triggering SmartFallbackService.');

            $fallback = $this->fallbackService->generateResponse(
                $userMessage,
                $user,
                $relevantCourses,
                $catalogSummary,
                $userProgress
            );

            return response()->json([
                'success' => true,
                'message' => $fallback['message'],
                'source' => 'smart_fallback',
                'intent' => $fallback['intent'],
                'model' => 'Ctrl C+V LMS Intelligence',
                'relevant_courses' => $relevantCourses,
                'suggested_chips' => $fallback['suggested_chips'],
                'offline_notice' => 'Đã kích hoạt cơ chế tra cứu dữ liệu nội bộ (Máy chủ AI tạm thời gián đoạn).',
            ]);
        } catch (Throwable $e) {
            Log::error('AI Chatbot critical error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hệ thống đang tạm thời xử lý dữ liệu. Bạn có thể xem danh sách khóa học tại [Tất cả khóa học]('.route('courses.index').') hoặc thử lại sau ít giây nhé!',
                'source' => 'safety_fallback',
                'relevant_courses' => [],
                'suggested_chips' => ['Tất cả khóa học', 'Lộ trình học'],
            ]);
        }
    }

    /**
     * Lấy trạng thái cấu hình và kiểm tra kết nối hệ thống AI.
     */
    public function status(): JsonResponse
    {
        $ping = $this->aiClient->ping();
        $catalog = $this->contextService->getCatalogSummary();

        return response()->json([
            'success' => true,
            'enabled' => (bool) config('ai_chat.enabled', true),
            'provider' => $ping['provider'] ?? config('ai_chat.providers.0.name', 'Primary AI'),
            'model' => $ping['model'] ?? config('ai_chat.api_model', 'gpt-4o-mini'),
            'base_url' => $ping['base_url'] ?? '',
            'ai_health' => $ping,
            'catalog_summary' => [
                'total_courses' => $catalog['total_courses'],
                'free_courses' => $catalog['free_courses_count'],
                'paid_courses' => $catalog['paid_courses_count'],
            ],
        ]);
    }

    /**
     * Loại bỏ các tin nhắn trùng lặp liên tiếp trong lịch sử hội thoại (Anti-Duplicate Protection).
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    protected function deduplicateHistory(array $history): array
    {
        $cleaned = [];
        $lastRole = null;
        $lastContent = null;

        foreach ($history as $msg) {
            $role = (string) ($msg['role'] ?? '');
            $content = trim((string) ($msg['content'] ?? ''));

            if ($content === '') {
                continue;
            }

            if ($role === $lastRole && $content === $lastContent) {
                continue;
            }

            $cleaned[] = [
                'role' => $role,
                'content' => $content,
            ];

            $lastRole = $role;
            $lastContent = $content;
        }

        return $cleaned;
    }

    /**
     * Gợi ý các nút câu hỏi nhanh linh hoạt sau phản hồi của AI.
     *
     * @return array<int, string>
     */
    protected function generateSuggestedChips(string $query, $user): array
    {
        $lower = mb_strtolower($query);

        if ($this->contextService->isHeartbreakOrVenting($query)) {
            return [
                'Lập trình Web',
                'Lộ trình học',
                'Khóa học miễn phí',
                'Luyện thi trắc nghiệm',
            ];
        }

        if ($this->contextService->isTeacherInquiry($query)) {
            return [
                'Bảng điều khiển Giảng viên',
                'Tạo khóa học mới',
                'Tạo đề trắc nghiệm',
                'Khóa học của tôi',
            ];
        }

        if ($this->contextService->isGeneralCatalogInquiry($query)) {
            return [
                'Lập trình Web',
                'Laravel & PHP',
                'Frontend & React',
                'Lộ trình học',
            ];
        }

        if (str_contains($lower, 'khóa học') || str_contains($lower, 'khoa hoc')) {
            return [
                'Lộ trình học',
                'Khóa học miễn phí',
                'Luyện thi trắc nghiệm',
            ];
        }

        if (str_contains($lower, 'lộ trình') || str_contains($lower, 'người mới')) {
            return [
                'Lập trình Web',
                'Khóa học của tôi',
                'Khóa học miễn phí',
            ];
        }

        return [
            'Lập trình Web',
            'Lộ trình học',
            'Khóa học của tôi',
        ];
    }
}
