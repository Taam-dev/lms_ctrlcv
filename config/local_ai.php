<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Local AI Chatbot Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình kết nối tới máy chủ Local AI (Ollama, LM Studio, vLLM hoặc bất kỳ
    | endpoint nào tương thích với chuẩn OpenAI API).
    |
    */

    'enabled' => (bool) env('LOCAL_AI_ENABLED', true),

    // Nhà cung cấp / chuẩn giao tiếp: 'ollama', 'lmstudio', hoặc 'openai_compatible'
    'provider' => env('LOCAL_AI_PROVIDER', 'ollama'),

    // Endpoint gốc của Local AI (ví dụ Ollama: http://127.0.0.1:11434/v1, LM Studio: http://127.0.0.1:1234/v1)
    'base_url' => rtrim(env('LOCAL_AI_BASE_URL', 'http://127.0.0.1:11434/v1'), '/'),

    // Tên mô hình AI được nạp trên máy chủ local (ví dụ: qwen2.5:latest, llama3.2, mistral, gemma2, v.v.)
    'model' => env('LOCAL_AI_MODEL', 'qwen2.5:latest'),

    // Khóa API nếu endpoint cục bộ yêu cầu xác thực (mặc định để trống hoặc 'ollama')
    'api_key' => env('LOCAL_AI_API_KEY', ''),

    // Thời gian chờ tối đa cho mỗi phản hồi (giây)
    'timeout' => (int) env('LOCAL_AI_TIMEOUT', 30),

    // Thời gian chờ kết nối ban đầu (giây)
    'connect_timeout' => (int) env('LOCAL_AI_CONNECT_TIMEOUT', 3),

    // Độ sáng tạo của câu trả lời (0.0 đến 1.0)
    'temperature' => (float) env('LOCAL_AI_TEMPERATURE', 0.6),

    // Giới hạn số token đầu ra
    'max_tokens' => (int) env('LOCAL_AI_MAX_TOKENS', 1200),

    // Số lượt hội thoại gần nhất được lưu trong bộ nhớ ngữ cảnh gửi lên LLM
    'max_history_turns' => (int) env('LOCAL_AI_MAX_HISTORY_TURNS', 8),
];
