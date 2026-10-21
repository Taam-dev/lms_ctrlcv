<?php

namespace App\Http\Controllers;

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
        protected SmartFallbackService $fallbackService
    ) {}

    /**
     * Xử lý gửi tin nhắn trò chuyện với Local AI hoặc Smart Fallback.
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
        $history = $validated['history'] ?? [];
        $user = $request->user();

        try {
            // 1. Thu thập dữ liệu LMS theo thời gian thực (RAG-Lite)
            $relevantCourses = $this->contextService->findRelevantCourses($userMessage, $user, 5);
            $catalogSummary = $this->contextService->getCatalogSummary();
            $userProgress = $this->contextService->getUserLearningProgress($user);

            // 2. Chuẩn bị ngữ cảnh hội thoại cho AI
            $systemPrompt = $this->contextService->buildSystemPrompt(
                $user,
                $relevantCourses,
                $catalogSummary,
                $userProgress
            );

            $maxTurns = (int) config('local_ai.max_history_turns', 8);
            $recentHistory = array_slice($history, -$maxTurns);

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

            // 3. Thử kết nối tới máy chủ Local AI
            $aiResponse = $this->aiClient->chat($messages);

            if ($aiResponse['success'] && ! empty($aiResponse['content'])) {
                return response()->json([
                    'success' => true,
                    'message' => $aiResponse['content'],
                    'source' => 'local_ai',
                    'model' => $aiResponse['model'],
                    'relevant_courses' => $relevantCourses,
                    'suggested_chips' => $this->generateSuggestedChips($userMessage, $user),
                ]);
            }

            // 4. Nếu Local AI offline hoặc lỗi, kích hoạt Graceful Offline Fallback
            Log::info('Local AI is offline or unreachable. Triggering SmartFallbackService.');

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
                'offline_notice' => 'Đã kích hoạt chế độ tra cứu LMS nội bộ (Máy chủ Local AI đang offline).',
            ]);
        } catch (Throwable $e) {
            Log::error('AI Chatbot critical error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Tuyệt đối không trả về mã lỗi 500 làm sập giao diện người dùng
            return response()->json([
                'success' => true,
                'message' => 'Hệ thống đang tạm thời xử lý dữ liệu. Bạn có thể xem danh sách khóa học tại [Tất cả khóa học]('.route('courses.index').') hoặc thử lại sau ít giây nhé!',
                'source' => 'safety_fallback',
                'relevant_courses' => [],
                'suggested_chips' => ['📚 Các khóa học hiện có', '🎯 Tư vấn lộ trình học'],
            ]);
        }
    }

    /**
     * Lấy trạng thái cấu hình và kiểm tra kết nối Local AI.
     */
    public function status(): JsonResponse
    {
        $ping = $this->aiClient->ping();
        $catalog = $this->contextService->getCatalogSummary();

        return response()->json([
            'success' => true,
            'enabled' => (bool) config('local_ai.enabled', true),
            'provider' => config('local_ai.provider', 'ollama'),
            'model' => config('local_ai.model', 'qwen2.5:latest'),
            'base_url' => config('local_ai.base_url'),
            'ai_health' => $ping,
            'catalog_summary' => [
                'total_courses' => $catalog['total_courses'],
                'free_courses' => $catalog['free_courses_count'],
                'paid_courses' => $catalog['paid_courses_count'],
            ],
        ]);
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
                '🎯 Tư vấn lộ trình học',
                '🚀 Khóa học miễn phí',
                '📝 Làm bài thi quizzes',
            ];
        }

        if (str_contains($lower, 'lộ trình') || str_contains($lower, 'người mới')) {
            return [
                '📚 Các khóa học hiện có',
                '📖 Khóa học của tôi',
                '💡 Gợi ý khóa học miễn phí',
            ];
        }

        return [
            '📚 Các khóa học hiện có',
            '🎯 Tư vấn lộ trình học',
            '📖 Khóa học của tôi',
        ];
    }
}
