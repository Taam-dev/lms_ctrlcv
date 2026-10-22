<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-API Key & Multi-Provider AI Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình hỗ trợ đa nhà cung cấp và tự động chuyển đổi (Failover) khi gặp lỗi
    | hết hạn mức (429), sai key (401), lỗi máy chủ (5xx) hoặc timeout.
    |
    */

    'enabled' => (bool) env('AI_CHAT_ENABLED', true),

    // Các nhà cung cấp ưu tiên (OpenAI, Gemini, Groq, OpenRouter, v.v.)
    'providers' => [
        [
            'name' => 'Provider 1 (Primary)',
            'base_url' => rtrim(env('AI_PROVIDER_1_URL', ''), '/'),
            'api_key' => env('AI_PROVIDER_1_KEY', ''),
            'model' => env('AI_PROVIDER_1_MODEL', 'gpt-4o-mini'),
        ],
        [
            'name' => 'Provider 2 (Backup)',
            'base_url' => rtrim(env('AI_PROVIDER_2_URL', ''), '/'),
            'api_key' => env('AI_PROVIDER_2_KEY', ''),
            'model' => env('AI_PROVIDER_2_MODEL', 'gemini-2.0-flash'),
        ],
        [
            'name' => 'Provider 3 (Backup)',
            'base_url' => rtrim(env('AI_PROVIDER_3_URL', ''), '/'),
            'api_key' => env('AI_PROVIDER_3_KEY', ''),
            'model' => env('AI_PROVIDER_3_MODEL', 'llama-3.3-70b-versatile'),
        ],
    ],

    // Shorthand danh sách API keys xoay vòng (ngăn cách bằng dấu phẩy)
    'api_keys' => array_values(array_filter(array_map('trim', explode(',', env('AI_API_KEYS', ''))))),
    'api_url' => rtrim(env('AI_API_URL', ''), '/'),
    'api_model' => env('AI_API_MODEL', 'gpt-4o-mini'),

    // Tầng Local AI (Ollama / LM Studio)
    'local_ai' => [
        'enabled' => (bool) env('LOCAL_AI_ENABLED', true),
        'base_url' => rtrim(env('LOCAL_AI_BASE_URL', 'http://127.0.0.1:11434/v1'), '/'),
        'model' => env('LOCAL_AI_MODEL', 'qwen2.5:latest'),
        'api_key' => env('LOCAL_AI_API_KEY', ''),
    ],

    // Cài đặt chung
    'timeout' => (int) env('AI_TIMEOUT', env('LOCAL_AI_TIMEOUT', 30)),
    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', env('LOCAL_AI_CONNECT_TIMEOUT', 3)),
    'temperature' => (float) env('AI_TEMPERATURE', 0.6),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 1200),
    'max_history_turns' => (int) env('AI_MAX_HISTORY_TURNS', 8),
];
