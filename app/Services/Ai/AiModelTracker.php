<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiModelTracker
{
    /**
     * Mẫu regex nhận diện các mô hình KHÔNG phục vụ chat hội thoại (Embedding, Audio, Vision-only, Rerank, Guard, v.v.).
     */
    protected const NON_CHAT_PATTERNS = '/(embedding|embed|bge-|whisper|transcri|audio|tts|voice|dall-e|image|diffusion|flux|midjourney|moderation|guard|safety|rerank|realtime|davinci|babbage|curie|ada)/i';

    /**
     * Mẫu regex phân cấp chất lượng & thế hệ mô hình để tự động chọn model mới nhất và tốt nhất.
     */
    protected const TIER_5_PATTERNS = '/(o3|o1|gpt-4o(?!-mini)|gpt-4\.5|claude-3-7|claude-3-5-sonnet|gemini-2\.5|gemini-2\.0|deepseek-chat|deepseek-v3|deepseek-r1|llama-3\.3|qwen2\.5-72b)/i';

    protected const TIER_4_PATTERNS = '/(gpt-4o-mini|claude-3-5-haiku|gemini-1\.5-pro|gemini-1\.5-flash|llama-3\.1-70b|llama-3\.2-3b|qwen2\.5-32b|mistral-large)/i';

    protected const TIER_3_PATTERNS = '/(gpt-4-turbo|gpt-4(?!o)|llama-3\.1-8b|qwen2\.5-7b|qwen2\.5|mixtral|mistral-small)/i';

    protected const TIER_2_PATTERNS = '/(gpt-3\.5-turbo|gemma-2|gemma2)/i';

    /**
     * Lấy danh sách các mô hình đang hoạt động (active) từ API của Provider.
     *
     * @return array<int, array{id: string, created: int, owned_by: ?string}>
     */
    public function fetchActiveModels(string $baseUrl, ?string $apiKey = null, bool $forceRefresh = false): array
    {
        $cleanUrl = rtrim($baseUrl, '/');
        if (empty($cleanUrl)) {
            return [];
        }

        $cacheKey = $this->getCacheKey($cleanUrl, $apiKey);

        if (! $forceRefresh) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && ! empty($cached)) {
                return $cached;
            }
        }

        $endpoint = $this->resolveModelsEndpoint($cleanUrl);
        $timeout = (int) config('ai_chat.timeout', 10);
        $connectTimeout = (int) config('ai_chat.connect_timeout', 3);

        try {
            $request = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->acceptJson();

            if (! empty($apiKey)) {
                $request = $request->withToken($apiKey);
            }

            $response = $request->get($endpoint);

            // Thử endpoint dự phòng nếu trả về 404
            if ($response->status() === 404 && ! str_contains($endpoint, '/v1/')) {
                $altEndpoint = $cleanUrl.'/v1/models';
                $response = $request->get($altEndpoint);
            }

            if (! $response->successful()) {
                Log::warning("AiModelTracker: API [{$cleanUrl}] trả về lỗi {$response->status()} khi lấy danh sách model: ".$response->body());

                return Cache::get($cacheKey, []);
            }

            $rawModels = $response->json('data') ?? $response->json('models') ?? $response->json();
            if (! is_array($rawModels)) {
                return [];
            }

            $activeChatModels = [];
            foreach ($rawModels as $item) {
                if (is_string($item)) {
                    $id = $item;
                    $created = 0;
                    $ownedBy = null;
                } elseif (is_array($item)) {
                    $id = (string) ($item['id'] ?? $item['name'] ?? '');
                    $created = (int) ($item['created'] ?? 0);
                    $ownedBy = isset($item['owned_by']) ? (string) $item['owned_by'] : null;
                } else {
                    continue;
                }

                if (empty($id)) {
                    continue;
                }

                // Loại bỏ tiền tố models/ nếu API trả về dạng Google Gemini native
                if (str_starts_with($id, 'models/')) {
                    $id = substr($id, 7);
                }

                // Lọc bỏ các mô hình không phải chat hội thoại (Audio, Embed, Image...)
                if (preg_match(self::NON_CHAT_PATTERNS, $id)) {
                    continue;
                }

                $activeChatModels[] = [
                    'id' => $id,
                    'created' => $created,
                    'owned_by' => $ownedBy,
                ];
            }

            $ttl = (int) config('ai_chat.track_cache_seconds', 3600);
            Cache::put($cacheKey, $activeChatModels, $ttl);

            return $activeChatModels;
        } catch (Throwable $e) {
            Log::warning("AiModelTracker: Ngoại lệ kết nối tới [{$cleanUrl}]: ".$e->getMessage());

            return Cache::get($cacheKey, []);
        }
    }

    /**
     * Tự động chọn mô hình chat mới nhất và tốt nhất đang hoạt động ở API đó.
     */
    public function pickBestActiveModel(
        string $baseUrl,
        ?string $apiKey = null,
        ?string $preferredModel = null,
        bool $forceRefresh = false
    ): string {
        $preferredModel = trim((string) $preferredModel);
        $models = $this->fetchActiveModels($baseUrl, $apiKey, $forceRefresh);

        // 1. Nếu người dùng cấu hình một model cụ thể (khác 'auto' và không rỗng)
        if (! empty($preferredModel) && strtolower($preferredModel) !== 'auto') {
            // Kiểm tra xem model cấu hình có nằm trong danh sách đang hoạt động không
            foreach ($models as $m) {
                if (strcasecmp($m['id'], $preferredModel) === 0) {
                    return $m['id'];
                }
            }

            // Nếu không tìm thấy chính xác nhưng danh sách models rỗng (không fetch được), giữ nguyên cấu hình
            if (empty($models)) {
                return $preferredModel;
            }

            // Kiểm tra xem có model nào chứa tên cấu hình không (ví dụ: gpt-4o-mini khớp gpt-4o-mini-2024-07-18)
            foreach ($models as $m) {
                if (stripos($m['id'], $preferredModel) !== false) {
                    return $m['id'];
                }
            }

            // Nếu model cấu hình không tồn tại trên API, tự động chuyển sang chọn model tốt nhất đang hoạt động
            Log::info("AiModelTracker: Model cấu hình [{$preferredModel}] không tồn tại trên [{$baseUrl}], tự động chọn model hoạt động mới nhất.");
        }

        // 2. Nếu không có danh sách model nào từ API, trả về model mặc định
        if (empty($models)) {
            return (! empty($preferredModel) && strtolower($preferredModel) !== 'auto')
                ? $preferredModel
                : 'gpt-4o-mini';
        }

        // 3. Chấm điểm và sắp xếp để chọn model mới nhất và chất lượng cao nhất
        usort($models, function (array $a, array $b): int {
            $scoreA = $this->calculateModelScore($a);
            $scoreB = $this->calculateModelScore($b);

            if ($scoreA === $scoreB) {
                return ($b['created'] ?? 0) <=> ($a['created'] ?? 0);
            }

            return $scoreB <=> $scoreA;
        });

        return $models[0]['id'];
    }

    /**
     * Kiểm tra trạng thái sức khỏe và thông tin các model của Provider.
     *
     * @return array{online: bool, model_count: int, selected_model: string, active_models: array<string>, error: ?string}
     */
    public function getProviderSummary(string $baseUrl, ?string $apiKey = null, ?string $preferredModel = null): array
    {
        $models = $this->fetchActiveModels($baseUrl, $apiKey, true);

        if (empty($models)) {
            return [
                'online' => false,
                'model_count' => 0,
                'selected_model' => (! empty($preferredModel) && strtolower($preferredModel) !== 'auto') ? $preferredModel : 'unknown',
                'active_models' => [],
                'error' => 'Không thể kết nối hoặc không tìm thấy mô hình chat nào khả dụng.',
            ];
        }

        $selected = $this->pickBestActiveModel($baseUrl, $apiKey, $preferredModel);
        $ids = array_column($models, 'id');

        return [
            'online' => true,
            'model_count' => count($models),
            'selected_model' => $selected,
            'active_models' => array_slice($ids, 0, 10),
            'error' => null,
        ];
    }

    /**
     * Xóa cache danh sách model của Provider (ví dụ khi bị 404 Model Not Found).
     */
    public function clearCache(string $baseUrl, ?string $apiKey = null): void
    {
        $cleanUrl = rtrim($baseUrl, '/');
        $cacheKey = $this->getCacheKey($cleanUrl, $apiKey);
        Cache::forget($cacheKey);
    }

    /**
     * Tính điểm đánh giá năng lực & độ mới của model.
     *
     * @param  array{id: string, created: int, owned_by: ?string}  $model
     */
    protected function calculateModelScore(array $model): int
    {
        $id = $model['id'];
        $score = 10000;

        if (preg_match(self::TIER_5_PATTERNS, $id)) {
            $score += 50000;
        } elseif (preg_match(self::TIER_4_PATTERNS, $id)) {
            $score += 40000;
        } elseif (preg_match(self::TIER_3_PATTERNS, $id)) {
            $score += 30000;
        } elseif (preg_match(self::TIER_2_PATTERNS, $id)) {
            $score += 20000;
        }

        // Điểm cộng theo thời gian tạo (ngày gần hơn có điểm cao hơn)
        if (! empty($model['created']) && $model['created'] > 0) {
            $daysSinceEpoch = (int) ($model['created'] / 86400);
            $score += $daysSinceEpoch;
        }

        // Trừ điểm bản preview hoặc thử nghiệm
        if (stripos($id, 'preview') !== false) {
            $score -= 500;
        }

        // Trừ điểm nặng các model đã bị đánh dấu lạc hậu hoặc deprecated
        if (stripos($id, 'deprecated') !== false || stripos($id, 'legacy') !== false) {
            $score -= 30000;
        }

        return $score;
    }

    /**
     * Tạo cache key duy nhất dựa trên URL và API Key.
     */
    protected function getCacheKey(string $cleanUrl, ?string $apiKey): string
    {
        $keyFragment = ! empty($apiKey) ? substr($apiKey, 0, 8) : 'no_key';

        return 'ai_models_track_'.md5($cleanUrl.'|'.$keyFragment);
    }

    /**
     * Chuẩn hóa URL endpoint lấy danh sách mô hình.
     */
    protected function resolveModelsEndpoint(string $cleanUrl): string
    {
        if (str_ends_with($cleanUrl, '/v1') || str_ends_with($cleanUrl, '/openai')) {
            return $cleanUrl.'/models';
        }

        return $cleanUrl.'/models';
    }
}
