<?php

namespace App\Services\Ai;

class LocalAiClient
{
    public function __construct(
        protected ?AiClientManager $clientManager = null
    ) {
        $this->clientManager = $clientManager ?? app(AiClientManager::class);
    }

    /**
     * Gửi yêu cầu hoàn thành đoạn chat qua hệ thống Multi-API Key Failover.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{success: bool, content: ?string, error: ?string, model: string, provider?: ?string}
     */
    public function chat(array $messages): array
    {
        return $this->clientManager->chat($messages);
    }

    /**
     * Kiểm tra trạng thái hoạt động của hệ thống AI.
     *
     * @return array{online: bool, model: string, base_url: string, message: string, provider?: string}
     */
    public function ping(): array
    {
        return $this->clientManager->ping();
    }

    public function getModel(): string
    {
        return (string) config('ai_chat.api_model', config('local_ai.model', 'qwen2.5:latest'));
    }

    public function getBaseUrl(): string
    {
        return (string) config('ai_chat.api_url', config('local_ai.base_url', 'http://127.0.0.1:11434/v1'));
    }

    public function getClientManager(): AiClientManager
    {
        return $this->clientManager;
    }
}
