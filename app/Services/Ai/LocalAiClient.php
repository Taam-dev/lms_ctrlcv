<?php

namespace App\Services\Ai;

/**
 * Lớp bọc tương thích ngược cho AiClientManager.
 * Quản trị kết nối Multi-Provider AI và chuyển đổi API backup khi hết token.
 */
class LocalAiClient
{
    public function __construct(
        protected ?AiClientManager $clientManager = null
    ) {
        $this->clientManager = $clientManager ?? app(AiClientManager::class);
    }

    /**
     * Gửi yêu cầu hoàn thành đoạn chat qua hệ thống Multi-Provider AI Failover.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{success: bool, content: ?string, error: ?string, model: string, provider?: ?string, provider_id?: string}
     */
    public function chat(array $messages): array
    {
        return $this->clientManager->chat($messages);
    }

    /**
     * Kiểm tra trạng thái hoạt động của hệ thống AI Provider.
     *
     * @return array{online: bool, model: string, base_url: string, provider: string, message: string}
     */
    public function ping(): array
    {
        return $this->clientManager->ping();
    }

    public function getModel(): string
    {
        return (string) config('ai_chat.api_model', config('ai_chat.providers.0.model', 'auto'));
    }

    public function getBaseUrl(): string
    {
        return (string) config('ai_chat.api_url', config('ai_chat.providers.0.base_url', ''));
    }

    public function getClientManager(): AiClientManager
    {
        return $this->clientManager;
    }
}
