<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-API Key & Multi-Provider AI Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình hỗ trợ đa nhà cung cấp AI, tự động theo dõi (track) API để chọn
    | mô hình mới nhất đang hoạt động, và tự động chuyển đổi sang API backup
    | ngay khi API chính hết token, chạm hạn mức quota (429/402) hoặc gặp lỗi.
    |
    */

    'enabled' => (bool) env('AI_CHAT_ENABLED', true),

    // Tự động kiểm tra API và chọn mô hình chat mới nhất đang hoạt động
    'auto_track_models' => (bool) env('AI_AUTO_TRACK_MODELS', true),

    // Thời gian lưu cache danh sách models đã track từ API (giây)
    'track_cache_seconds' => (int) env('AI_MODEL_TRACK_CACHE', 3600),

    // Thời gian hạ nhiệt (cooldown) khi một API bị hết token/quota trước khi thử lại (giây)
    'cooldown_seconds' => (int) env('AI_FAILOVER_COOLDOWN', 300),

    // Danh sách các Provider AI theo thứ tự ưu tiên (Primary -> Backup 1 -> Backup 2...)
    'providers' => [
        [
            'id' => 'provider_1',
            'name' => env('AI_PROVIDER_1_NAME', 'Provider 1 (Chính)'),
            'base_url' => rtrim(env('AI_PROVIDER_1_URL', ''), '/'),
            'api_key' => env('AI_PROVIDER_1_KEY', ''),
            'model' => env('AI_PROVIDER_1_MODEL', 'auto'),
            'is_backup' => false,
        ],
        [
            'id' => 'provider_2',
            'name' => env('AI_PROVIDER_2_NAME', 'Provider 2 (Backup 1)'),
            'base_url' => rtrim(env('AI_PROVIDER_2_URL', ''), '/'),
            'api_key' => env('AI_PROVIDER_2_KEY', ''),
            'model' => env('AI_PROVIDER_2_MODEL', 'auto'),
            'is_backup' => true,
        ],
        [
            'id' => 'provider_3',
            'name' => env('AI_PROVIDER_3_NAME', 'Provider 3 (Backup 2)'),
            'base_url' => rtrim(env('AI_PROVIDER_3_URL', ''), '/'),
            'api_key' => env('AI_PROVIDER_3_KEY', ''),
            'model' => env('AI_PROVIDER_3_MODEL', 'auto'),
            'is_backup' => true,
        ],
    ],

    // Shorthand danh sách API keys xoay vòng dự phòng (ngăn cách bằng dấu phẩy)
    'api_keys' => array_values(array_filter(array_map('trim', explode(',', env('AI_API_KEYS', ''))))),
    'api_url' => rtrim(env('AI_API_URL', ''), '/'),
    'api_model' => env('AI_API_MODEL', 'auto'),

    // Cài đặt chung kết nối
    'timeout' => (int) env('AI_TIMEOUT', 30),
    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 5),
    'temperature' => (float) env('AI_TEMPERATURE', 0.6),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 1200),
    'max_history_turns' => (int) env('AI_MAX_HISTORY_TURNS', 8),
];
