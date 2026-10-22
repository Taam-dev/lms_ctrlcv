<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiClientManager
{
    protected array $targets = [];

    protected int $timeout;

    protected int $connectTimeout;

    protected float $temperature;

    protected int $maxTokens;

    public function __construct()
    {
        $this->timeout = (int) config('ai_chat.timeout', config('local_ai.timeout', 30));
        $this->connectTimeout = (int) config('ai_chat.connect_timeout', config('local_ai.connect_timeout', 3));
        $this->temperature = (float) config('ai_chat.temperature', config('local_ai.temperature', 0.6));
        $this->maxTokens = (int) config('ai_chat.max_tokens', config('local_ai.max_tokens', 1200));

        $this->buildTargetQueue();
    }

    /**
     * Khởi tạo hàng đợi các mục tiêu API theo thứ tự ưu tiên Failover.
     */
    public function buildTargetQueue(): void
    {
        $this->targets = [];

        // 1. Shorthand AI_API_KEYS (xoay vòng nhiều key)
        $shorthandKeys = config('ai_chat.api_keys', []);
        $shorthandUrl = config('ai_chat.api_url') ?: config('ai_chat.providers.0.base_url');
        $shorthandModel = config('ai_chat.api_model') ?: config('ai_chat.providers.0.model', 'gpt-4o-mini');

        if (! empty($shorthandKeys) && ! empty($shorthandUrl)) {
            foreach ($shorthandKeys as $idx => $key) {
                $num = $idx + 1;
                $this->targets[] = [
                    'id' => "shorthand_{$num}",
                    'name' => "Shorthand Key #{$num}",
                    'base_url' => rtrim($shorthandUrl, '/'),
                    'api_key' => $key,
                    'model' => $shorthandModel,
                ];
            }
        }

        // 2. Danh sách các Provider cấu hình riêng (Provider 1, 2, 3)
        $configuredProviders = config('ai_chat.providers', []);
        foreach ($configuredProviders as $idx => $p) {
            $id = $p['id'] ?? ('provider_'.($idx + 1));
            $url = rtrim($p['base_url'] ?? $p['url'] ?? '', '/');
            $key = trim($p['api_key'] ?? $p['key'] ?? '');
            $model = trim($p['model'] ?? '');

            if (! empty($url) && ! empty($key)) {
                // Kiểm tra xem key có chứa danh sách ngăn cách bằng dấu phẩy không
                if (str_contains($key, ',')) {
                    $subKeys = array_values(array_filter(array_map('trim', explode(',', $key))));
                    foreach ($subKeys as $kIdx => $subKey) {
                        $this->targets[] = [
                            'id' => $id,
                            'name' => ($p['name'] ?? 'Provider').' (Key '.($kIdx + 1).')',
                            'base_url' => $url,
                            'api_key' => $subKey,
                            'model' => $model ?: 'gpt-4o-mini',
                        ];
                    }
                } else {
                    $this->targets[] = [
                        'id' => $id,
                        'name' => $p['name'] ?? 'Configured Provider',
                        'base_url' => $url,
                        'api_key' => $key,
                        'model' => $model ?: 'gpt-4o-mini',
                    ];
                }
            }
        }

        // 3. Local AI (Ollama / LM Studio) làm tầng backup
        $localAiEnabled = (bool) config('ai_chat.local_ai.enabled', config('local_ai.enabled', true));
        $localAiUrl = rtrim((string) config('ai_chat.local_ai.base_url', config('local_ai.base_url', 'http://127.0.0.1:11434/v1')), '/');
        $localAiModel = (string) config('ai_chat.local_ai.model', config('local_ai.model', 'qwen2.5:latest'));
        $localAiKey = config('ai_chat.local_ai.api_key') ?: config('local_ai.api_key', '');

        if ($localAiEnabled && ! empty($localAiUrl)) {
            $this->targets[] = [
                'id' => 'local_ai',
                'name' => 'Local AI (Ollama / LM Studio)',
                'base_url' => $localAiUrl,
                'api_key' => $localAiKey ?: null,
                'model' => $localAiModel,
            ];
        }
    }

    /**
     * Gửi yêu cầu hoàn thành chat với cơ chế Multi-API Key Failover tự động.
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
                'error' => 'Không có nhà cung cấp AI nào được cấu hình trong hệ thống.',
                'model' => 'none',
                'provider' => null,
                'provider_id' => 'none',
            ];
        }

        $lastError = 'Chưa thể kết nối tới các dịch vụ AI.';

        foreach ($this->targets as $target) {
            $endpoint = $target['base_url'].'/chat/completions';
            $targetName = $target['name'];
            $targetId = $target['id'] ?? 'local_ai';
            $model = $target['model'];

            $payload = [
                'model' => $model,
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

                if (! empty($target['api_key'])) {
                    $request = $request->withToken($target['api_key']);
                }

                $response = $request->post($endpoint, $payload);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    if (! empty($content)) {
                        return [
                            'success' => true,
                            'content' => trim((string) $content),
                            'error' => null,
                            'model' => (string) ($response->json('model') ?? $model),
                            'provider' => $targetName,
                            'provider_id' => $targetId,
                        ];
                    }
                }

                $status = $response->status();
                $body = $response->json('error.message') ?? $response->body();
                $lastError = "{$targetName} (HTTP {$status}): {$body}";
                Log::warning("AI Provider [{$targetName}] failed with status {$status}. Failing over to next target. Details: {$body}");
            } catch (Throwable $e) {
                $lastError = "{$targetName} exception: ".$e->getMessage();
                Log::warning("AI Provider [{$targetName}] exception: ".$e->getMessage().'. Failing over to next target.');
            }
        }

        return [
            'success' => false,
            'content' => null,
            'error' => "Tất cả các API key và nhà cung cấp AI đều không phản hồi: {$lastError}",
            'model' => 'failover_exhausted',
            'provider' => null,
        ];
    }

    /**
     * Kiểm tra trạng thái sức khỏe của các Provider.
     */
    public function ping(): array
    {
        if (empty($this->targets)) {
            return [
                'online' => false,
                'model' => 'none',
                'base_url' => '',
                'message' => 'Chưa cấu hình API endpoint nào.',
            ];
        }

        foreach ($this->targets as $target) {
            try {
                $endpoint = $target['base_url'].'/models';
                $request = Http::timeout(2)->connectTimeout(2)->acceptJson();

                if (! empty($target['api_key'])) {
                    $request = $request->withToken($target['api_key']);
                }

                $res = $request->get($endpoint);
                if ($res->successful()) {
                    return [
                        'online' => true,
                        'model' => $target['model'],
                        'base_url' => $target['base_url'],
                        'provider' => $target['name'],
                        'message' => "Máy chủ AI [{$target['name']}] đang hoạt động.",
                    ];
                }
            } catch (Throwable $e) {
                // Tiếp tục thử target tiếp theo
            }
        }

        return [
            'online' => false,
            'model' => $this->targets[0]['model'] ?? 'unknown',
            'base_url' => $this->targets[0]['base_url'] ?? '',
            'message' => 'Không thể kết nối đến các máy chủ AI đã cấu hình.',
        ];
    }

    public function getTargets(): array
    {
        return $this->targets;
    }
}
