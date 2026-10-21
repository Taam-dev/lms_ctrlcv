<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LocalAiClient
{
    protected bool $enabled;

    protected string $baseUrl;

    protected string $model;

    protected ?string $apiKey;

    protected int $timeout;

    protected int $connectTimeout;

    protected float $temperature;

    protected int $maxTokens;

    public function __construct()
    {
        $this->enabled = (bool) config('local_ai.enabled', true);
        $this->baseUrl = rtrim((string) config('local_ai.base_url', 'http://127.0.0.1:11434/v1'), '/');
        $this->model = (string) config('local_ai.model', 'qwen2.5:latest');
        $this->apiKey = config('local_ai.api_key') ?: null;
        $this->timeout = (int) config('local_ai.timeout', 30);
        $this->connectTimeout = (int) config('local_ai.connect_timeout', 3);
        $this->temperature = (float) config('local_ai.temperature', 0.6);
        $this->maxTokens = (int) config('local_ai.max_tokens', 1200);
    }

    /**
     * Gửi yêu cầu hoàn thành đoạn chat tới Local AI server (Ollama / LM Studio / OpenAI-compatible).
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{success: bool, content: ?string, error: ?string, model: string}
     */
    public function chat(array $messages): array
    {
        if (! $this->enabled) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'Local AI đã bị tắt trong cấu hình hệ thống.',
                'model' => $this->model,
            ];
        }

        $endpoint = $this->baseUrl.'/chat/completions';

        $payload = [
            'model' => $this->model,
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

            if (! empty($this->apiKey)) {
                $request = $request->withToken($this->apiKey);
            }

            $response = $request->post($endpoint, $payload);

            if (! $response->successful()) {
                $errorMsg = $response->json('error.message') ?? $response->body();
                Log::warning('Local AI returned unsuccessful status code: '.$response->status().' - '.$errorMsg);

                return [
                    'success' => false,
                    'content' => null,
                    'error' => "Máy chủ Local AI trả về lỗi ({$response->status()}): {$errorMsg}",
                    'model' => $this->model,
                ];
            }

            $content = $response->json('choices.0.message.content');
            if (empty($content)) {
                return [
                    'success' => false,
                    'content' => null,
                    'error' => 'Mô hình AI không trả về nội dung hợp lệ.',
                    'model' => $this->model,
                ];
            }

            return [
                'success' => true,
                'content' => trim((string) $content),
                'error' => null,
                'model' => (string) ($response->json('model') ?? $this->model),
            ];
        } catch (Throwable $e) {
            Log::info('Local AI connection failed or timed out: '.$e->getMessage());

            return [
                'success' => false,
                'content' => null,
                'error' => 'Không thể kết nối đến máy chủ Local AI: '.$e->getMessage(),
                'model' => $this->model,
            ];
        }
    }

    /**
     * Kiểm tra trạng thái hoạt động và phản hồi nhanh của Local AI server.
     *
     * @return array{online: bool, model: string, base_url: string, message: string}
     */
    public function ping(): array
    {
        if (! $this->enabled) {
            return [
                'online' => false,
                'model' => $this->model,
                'base_url' => $this->baseUrl,
                'message' => 'Local AI đang bị vô hiệu hóa trong file .env (LOCAL_AI_ENABLED=false)',
            ];
        }

        try {
            $modelsEndpoint = $this->baseUrl.'/models';

            $request = Http::timeout(2)
                ->connectTimeout(2)
                ->acceptJson();

            if (! empty($this->apiKey)) {
                $request = $request->withToken($this->apiKey);
            }

            $response = $request->get($modelsEndpoint);

            if ($response->successful()) {
                return [
                    'online' => true,
                    'model' => $this->model,
                    'base_url' => $this->baseUrl,
                    'message' => 'Máy chủ Local AI đang hoạt động bình thường.',
                ];
            }

            return [
                'online' => false,
                'model' => $this->model,
                'base_url' => $this->baseUrl,
                'message' => 'Máy chủ phản hồi mã lỗi HTTP '.$response->status(),
            ];
        } catch (Throwable $e) {
            return [
                'online' => false,
                'model' => $this->model,
                'base_url' => $this->baseUrl,
                'message' => 'Không tìm thấy máy chủ Local AI tại '.$this->baseUrl,
            ];
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
