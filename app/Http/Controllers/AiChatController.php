<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiGuardrailService;
use App\Services\Ai\LmsContextService;
use App\Services\Ai\LocalAiClient;
use App\Services\Ai\SmartFallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiChatController extends Controller
{
    public function __construct(
        protected LmsContextService $contextService,
        protected LocalAiClient $aiClient,
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
            $maxTurns = (int) config('ai_chat.max_history_turns', config('local_ai.max_history_turns', 8));
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
                // Kiểm tra xem phản hồi của model có vi phạm quy tắc từ chối ngoài lề không
                return response()->json([
                    'success' => true,
                    'message' => $aiResponse['content'],
                    'source' => $aiResponse['provider_id'] ?? 'local_ai',
                    'model' => $aiResponse['model'],
                    'provider' => $aiResponse['provider'] ?? 'AI',
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

        if (str_contains($lower, 'khóa học') || str_contains($lower, 'khoa hoc')) {
            return [
                'Lộ trình học',
                'Khóa học miễn phí',
                'Luyện thi trắc nghiệm',
            ];
        }

        if (str_contains($lower, 'lộ trình') || str_contains($lower, 'người mới')) {
            return [
                'Tất cả khóa học',
                'Khóa học của tôi',
                'Khóa học miễn phí',
            ];
        }

        return [
            'Tất cả khóa học',
            'Lộ trình học',
            'Khóa học của tôi',
        ];
    }
}
