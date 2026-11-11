<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiClientManager
{
    /**
     * Danh sách các mục tiêu AI theo thứ tự ưu tiên (Primary -> Backup 1 -> Backup 2...).
     *
     * @var array<int, array{id: string, name: string, base_url: string, api_key: ?string, model: string, is_backup: bool}>
     */
    protected array $targets = [];

    protected int $timeout;

    protected int $connectTimeout;

    protected float $temperature;

    protected int $maxTokens;

    protected int $cooldownSeconds;

    protected bool $autoTrackModels;

    public function __construct(
        protected ?AiModelTracker $modelTracker = null
    ) {
        $this->modelTracker = $modelTracker ?? app(AiModelTracker::class);
        $this->timeout = (int) config('ai_chat.timeout', 30);
        $this->connectTimeout = (int) config('ai_chat.connect_timeout', 5);
        $this->temperature = (float) config('ai_chat.temperature', 0.6);
        $this->maxTokens = (int) config('ai_chat.max_tokens', 1200);
        $this->cooldownSeconds = (int) config('ai_chat.cooldown_seconds', 300);
        $this->autoTrackModels = (bool) config('ai_chat.auto_track_models', true);

        $this->buildTargetQueue();
    }

    /**
     * Khởi tạo hàng đợi các mục tiêu API theo thứ tự ưu tiên Failover (Primary -> Backup).
     * Các API hết token đang trong thời gian hạ nhiệt (cooldown) sẽ được xếp sau cùng.
     */
    public function buildTargetQueue(): void
    {
        $rawTargets = [];

        // 1. Shorthand AI_API_KEYS (xoay vòng nhiều key dự phòng)
        $shorthandKeys = config('ai_chat.api_keys', []);
        $shorthandUrl = config('ai_chat.api_url') ?: config('ai_chat.providers.0.base_url');
        $shorthandModel = config('ai_chat.api_model') ?: config('ai_chat.providers.0.model', 'auto');

        if (! empty($shorthandKeys) && ! empty($shorthandUrl)) {
            foreach ($shorthandKeys as $idx => $key) {
                $num = $idx + 1;
                $rawTargets[] = [
                    'id' => "shorthand_{$num}",
                    'name' => "API Key #{$num}",
                    'base_url' => rtrim($shorthandUrl, '/'),
                    'api_key' => $key,
                    'model' => $shorthandModel ?: 'auto',
                    'is_backup' => $idx > 0,
                ];
            }
        }

        // 2. Danh sách các Provider cấu hình riêng (Provider 1 Chính, Provider 2 Backup, Provider 3 Backup)
        $configuredProviders = config('ai_chat.providers', []);
        foreach ($configuredProviders as $idx => $p) {
            $id = $p['id'] ?? ('provider_'.($idx + 1));
            $url = rtrim($p['base_url'] ?? $p['url'] ?? '', '/');
            $key = trim($p['api_key'] ?? $p['key'] ?? '');
            $model = trim($p['model'] ?? 'auto');
            $isBackup = ($idx > 0) || ! empty($p['is_backup']);

            if (! empty($url) && ! empty($key)) {
                // Kiểm tra xem key có chứa danh sách nhiều key ngăn cách bằng dấu phẩy không
                if (str_contains($key, ',')) {
                    $subKeys = array_values(array_filter(array_map('trim', explode(',', $key))));
                    foreach ($subKeys as $kIdx => $subKey) {
                        $rawTargets[] = [
                            'id' => "{$id}_key_".($kIdx + 1),
                            'name' => ($p['name'] ?? 'Provider '.($idx + 1)).' (Key '.($kIdx + 1).')',
                            'base_url' => $url,
                            'api_key' => $subKey,
                            'model' => $model ?: 'auto',
                            'is_backup' => $isBackup || ($kIdx > 0),
                        ];
                    }
                } else {
                    $rawTargets[] = [
                        'id' => $id,
                        'name' => $p['name'] ?? ($idx === 0 ? 'Provider 1 (Chính)' : 'Provider '.($idx + 1).' (Backup)'),
                        'base_url' => $url,
                        'api_key' => $key,
                        'model' => $model ?: 'auto',
                        'is_backup' => $isBackup,
                    ];
                }
            }
        }

        if (empty($rawTargets)) {
            $this->targets = [];

            return;
        }

        // Phân loại: Các API sẵn sàng xếp trước, các API đang trong cooldown (vừa hết token) xếp sau
        $readyTargets = [];
        $cooldownTargets = [];

        foreach ($rawTargets as $target) {
            if ($this->isTargetInCooldown($target['id'])) {
                $cooldownTargets[] = $target;
            } else {
                $readyTargets[] = $target;
            }
        }

        // Nếu tất cả API đều trong cooldown, cho phép thử lại toàn bộ để tránh treo hệ thống
        if (empty($readyTargets)) {
            $this->targets = $rawTargets;
        } else {
            $this->targets = array_merge($readyTargets, $cooldownTargets);
        }
    }

    /**
     * Gửi yêu cầu trò chuyện qua AI Provider, tự động track model mới nhất và failover sang API backup nếu hết token.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{success: bool, content: ?string, error: ?string, model: string, provider: ?string, provider_id: string}
     */
    public function chat(array $messages): array
    {
        $this->buildTargetQueue();

        if (empty($this->targets)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'Chưa cấu hình API Provider AI nào trong hệ thống (Vui lòng kiểm tra AI_PROVIDER_1_URL và KEY).',
                'model' => 'none',
                'provider' => null,
                'provider_id' => 'none',
            ];
        }

        $lastError = 'Chưa thể kết nối tới các dịch vụ AI Provider.';
        $failedProviders = [];

        foreach ($this->targets as $target) {
            $targetId = $target['id'];
            $targetName = $target['name'];
            $baseUrl = $target['base_url'];
            $apiKey = $target['api_key'];

            // Tự động track model đang hoạt động từ API để chọn model mới nhất và tốt nhất
            $resolvedModel = $this->resolveActiveModel($baseUrl, $apiKey, $target['model']);
            $endpoint = $this->resolveChatEndpoint($baseUrl);

            $payload = [
                'model' => $resolvedModel,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'stream' => false,
            ];

            try {
                $request = Http::timeout($this->timeout)
                    ->connectTimeout($this->connectTimeout)
                    ->acceptJson()
                    ->asJson();

                if (! empty($apiKey)) {
                    $request = $request->withToken($apiKey);
                }

                $response = $request->post($endpoint, $payload);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    if (! empty($content)) {
                        // Xóa cooldown nếu provider này vừa hoạt động thành công trở lại
                        $this->clearTargetCooldown($targetId);

                        return [
                            'success' => true,
                            'content' => trim((string) $content),
                            'error' => null,
                            'model' => (string) ($response->json('model') ?? $resolvedModel),
                            'provider' => $targetName,
                            'provider_id' => $targetId,
                        ];
                    }
                }

                $status = $response->status();
                $body = (string) ($response->json('error.message') ?? $response->body());
                $lastError = "{$targetName} (HTTP {$status}): {$body}";
                $failedProviders[] = $targetName;

                // Kiểm tra xem lỗi có phải do hết token / hết hạn mức (Quota exceeded, Rate limit, Payment required)
                if ($this->isExhaustionError($status, $body)) {
                    $this->markTargetExhausted($targetId, "Hết token / Quota exceeded (HTTP {$status})");
                    Log::warning("[AI Failover] API Provider [{$targetName}] đã hết token hoặc chạm quota ({$status}). Tự động chuyển sang API backup tiếp theo... Chi tiết: {$body}");
                } else {
                    // Nếu lỗi 404 Model Not Found, xóa cache model để lần sau track lại
                    if ($status === 404) {
                        $this->modelTracker->clearCache($baseUrl, $apiKey);
                    }
                    Log::warning("[AI Failover] API Provider [{$targetName}] lỗi HTTP {$status}. Chuyển sang API backup tiếp theo... Chi tiết: {$body}");
                }
            } catch (Throwable $e) {
                $lastError = "{$targetName} exception: ".$e->getMessage();
                $failedProviders[] = $targetName;
                Log::warning("[AI Failover] API Provider [{$targetName}] gặp sự cố kết nối: ".$e->getMessage().'. Đang chuyển sang API backup tiếp theo...');
            }
        }

        return [
            'success' => false,
            'content' => null,
            'error' => "Tất cả các API provider chính và backup đều không khả dụng hoặc đã hết token: {$lastError}",
            'model' => 'failover_exhausted',
            'provider' => null,
            'provider_id' => 'none',
        ];
    }

    /**
     * Kiểm tra trạng thái sức khỏe, track model và kết nối của các Provider AI.
     *
     * @return array{online: bool, model: string, base_url: string, provider: string, message: string, active_models?: array<string>}
     */
    public function ping(): array
    {
        $this->buildTargetQueue();

        if (empty($this->targets)) {
            return [
                'online' => false,
                'model' => 'none',
                'base_url' => '',
                'provider' => 'None',
                'message' => 'Chưa cấu hình API Provider AI nào.',
            ];
        }

        foreach ($this->targets as $target) {
            $summary = $this->modelTracker->getProviderSummary(
                $target['base_url'],
                $target['api_key'],
                $target['model']
            );

            if ($summary['online']) {
                return [
                    'online' => true,
                    'model' => $summary['selected_model'],
                    'base_url' => $target['base_url'],
                    'provider' => $target['name'],
                    'message' => "Máy chủ AI Provider [{$target['name']}] đang hoạt động (Đã track {$summary['model_count']} models).",
                    'active_models' => $summary['active_models'],
                ];
            }
        }

        return [
            'online' => false,
            'model' => $this->targets[0]['model'] ?? 'unknown',
            'base_url' => $this->targets[0]['base_url'] ?? '',
            'provider' => $this->targets[0]['name'] ?? 'Provider',
            'message' => 'Không thể kết nối đến các máy chủ AI Provider đã cấu hình.',
        ];
    }

    /**
     * Tự động xác định model đang hoạt động và mới nhất từ API.
     */
    public function resolveActiveModel(string $baseUrl, ?string $apiKey, string $configuredModel): string
    {
        if (! $this->autoTrackModels && ! empty($configuredModel) && strtolower($configuredModel) !== 'auto') {
            return $configuredModel;
        }

        return $this->modelTracker->pickBestActiveModel($baseUrl, $apiKey, $configuredModel);
    }

    /**
     * Kiểm tra xem phản hồi có phải là tín hiệu hết token / hết hạn mức (Quota / Rate Limit) hay không.
     */
    public function isExhaustionError(int $status, string $body): bool
    {
        if (in_array($status, [429, 402, 403], true)) {
            return true;
        }

        $lowerBody = mb_strtolower($body);
        $exhaustionKeywords = [
            'quota',
            'tokens',
            'rate_limit',
            'rate limit',
            'credit',
            'balance',
            'insufficient_quota',
            'exceeded',
            'overloaded',
            'exhausted',
            'billing',
        ];

        foreach ($exhaustionKeywords as $kw) {
            if (str_contains($lowerBody, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra xem một Target có đang trong thời gian hạ nhiệt do hết token hay không.
     */
    public function isTargetInCooldown(string $targetId): bool
    {
        return Cache::has("ai_provider_cooldown_{$targetId}");
    }

    /**
     * Đánh dấu Target bị hết token vào bộ nhớ đệm (Cooldown) để ưu tiên dùng ngay API backup.
     */
    public function markTargetExhausted(string $targetId, string $reason): void
    {
        Cache::put("ai_provider_cooldown_{$targetId}", [
            'until' => now()->addSeconds($this->cooldownSeconds)->timestamp,
            'reason' => $reason,
        ], $this->cooldownSeconds);
    }

    /**
     * Xóa trạng thái cooldown của Target khi khôi phục thành công.
     */
    public function clearTargetCooldown(string $targetId): void
    {
        Cache::forget("ai_provider_cooldown_{$targetId}");
    }

    public function resetAllCooldowns(): void
    {
        foreach ($this->targets as $target) {
            $this->clearTargetCooldown($target['id']);
        }
    }

    public function getTargets(): array
    {
        return $this->targets;
    }

    public function getModelTracker(): AiModelTracker
    {
        return $this->modelTracker;
    }

    /**
     * Chuẩn hóa URL chat completions endpoint.
     */
    protected function resolveChatEndpoint(string $cleanUrl): string
    {
        if (str_ends_with($cleanUrl, '/chat/completions')) {
            return $cleanUrl;
        }

        return $cleanUrl.'/chat/completions';
    }
}
