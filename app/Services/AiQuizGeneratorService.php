<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiQuizGeneratorService
{
    /**
     * Danh sách các nhà cung cấp AI được hỗ trợ và các model khả quan tương ứng.
     *
     * @return array<string, array{name: string, description: string, placeholder: string, models: array<string, string>}>
     */
    public static function getSupportedProviders(): array
    {
        return [
            'gemini' => [
                'name' => 'Google Gemini AI',
                'description' => 'Mô hình tiên tiến của Google, miễn phí & tốc độ phản hồi cực nhanh.',
                'placeholder' => 'AIzaSy...',
                'models' => [
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash (Khuyên dùng - Nhanh & miễn phí)',
                    'gemini-2.0-flash' => 'Gemini 2.0 Flash (Thế hệ mới nhất, siêu tốc)',
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro (Phân tích chuyên sâu & thông minh nhất)',
                ],
            ],
            'openai' => [
                'name' => 'OpenAI (ChatGPT)',
                'description' => 'Mô hình chuẩn mực quốc tế của OpenAI với độ chính xác cao.',
                'placeholder' => 'sk-proj-...',
                'models' => [
                    'gpt-4o-mini' => 'GPT-4o Mini (Khuyên dùng - Rẻ, nhanh & chất lượng cao)',
                    'gpt-4o' => 'GPT-4o (Mô hình đa nhiệm mạnh mẽ nhất của OpenAI)',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Mô hình kinh điển, phản hồi ổn định)',
                ],
            ],
            'openrouter' => [
                'name' => 'OpenRouter AI',
                'description' => 'Cổng kết nối hàng trăm mô hình mã nguồn mở và thương mại.',
                'placeholder' => 'sk-or-v1-...',
                'models' => [
                    'google/gemini-2.0-flash-001' => 'Google: Gemini 2.0 Flash',
                    'meta-llama/llama-3.3-70b-instruct' => 'Meta: Llama 3.3 70B Instruct',
                    'deepseek/deepseek-chat' => 'DeepSeek: DeepSeek-V3 Chat',
                    'openai/gpt-4o-mini' => 'OpenAI: GPT-4o Mini',
                ],
            ],
            'groq' => [
                'name' => 'GroqCloud (LPU)',
                'description' => 'Nền tảng xử lý AI siêu tốc bằng phần cứng Groq LPU.',
                'placeholder' => 'gsk_...',
                'models' => [
                    'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant (Khuyên dùng - Cực nhanh & Ổn định)',
                    'openai/gpt-oss-120b' => 'OpenAI GPT-OSS 120B (Thông minh nhất trên Groq)',
                    'openai/gpt-oss-20b' => 'OpenAI GPT-OSS 20B (Nhanh & Tối ưu chi phí)',
                    'deepseek-r1-distill-llama-70b' => 'DeepSeek R1 Distill 70B (Suy luận logic)',
                    'gemma2-9b-it' => 'Google Gemma 2 9B (Nhẹ & Phản hồi nhanh)',
                ],
            ],
        ];
    }

    /**
     * Tạo danh sách câu hỏi trắc nghiệm từ nội dung văn bản thông qua AI.
     *
     * @return array{success: bool, questions?: array<int, array{question_text: string, explanation: string, options: array<int, array{option_text: string, is_correct: bool}>}>, message?: string}
     */
    public function generate(
        string $provider,
        string $apiKey,
        string $model,
        string $content,
        int $questionCount = 5,
        string $difficulty = 'medium'
    ): array {
        $difficultyText = match ($difficulty) {
            'easy' => 'cơ bản / dễ hiểu',
            'hard' => 'nâng cao / đòi hỏi tư duy phân tích sâu',
            'mixed' => 'đa dạng (gồm cả câu hỏi nhận biết, thông hiểu và vận dụng)',
            default => 'trung bình / chuẩn mực theo chương trình học',
        };

        $prompt = "Bạn là chuyên gia giáo dục và biên soạn đề thi trắc nghiệm chuyên nghiệp.\n"
            ."Nhiệm vụ của bạn là dựa vào tài liệu / nội dung sau đây để tạo ra đúng {$questionCount} câu hỏi trắc nghiệm tiếng Việt chất lượng cao.\n"
            ."Mức độ khó của đề thi: {$difficultyText}.\n\n"
            ."YÊU CẦU NGHIÊM NGẶT VỀ CÂU HỎI:\n"
            ."1. Mỗi câu hỏi BẮT BUỘC có đúng 4 phương án lựa chọn (A, B, C, D).\n"
            ."2. CHỈ CÓ DUY NHẤT 1 phương án đúng ('is_correct': true), 3 phương án còn lại là sai ('is_correct': false).\n"
            ."3. Mỗi câu hỏi phải có trường 'explanation' giải thích rõ ràng, ngắn gọn tại sao phương án đó đúng.\n"
            ."4. Câu hỏi và các phương án phải bám sát nội dung được cung cấp, không bịa đặt thông tin sai lệch.\n\n"
            ."TÀI LIỆU / NỘI DUNG CẦN TẠO CÂU HỎI:\n"
            ."\"\"\"\n"
            .$content."\n"
            ."\"\"\"\n\n"
            ."ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:\n"
            ."Chỉ trả về DUY NHẤT một mảng JSON hợp lệ chứa các đối tượng câu hỏi, không bao gồm bất kỳ lời dẫn hay ghi chú nào ngoài mảng JSON này. Định dạng mẫu:\n"
            ."[\n"
            ."  {\n"
            ."    \"question_text\": \"Câu hỏi 1...?\",\n"
            ."    \"explanation\": \"Giải thích ngắn gọn...\",\n"
            ."    \"options\": [\n"
            ."      { \"option_text\": \"Đáp án A\", \"is_correct\": true },\n"
            ."      { \"option_text\": \"Đáp án B\", \"is_correct\": false },\n"
            ."      { \"option_text\": \"Đáp án C\", \"is_correct\": false },\n"
            ."      { \"option_text\": \"Đáp án D\", \"is_correct\": false }\n"
            ."    ]\n"
            ."  }\n"
            .']';

        try {
            $rawJson = match ($provider) {
                'gemini' => $this->callGemini($apiKey, $model, $prompt),
                'openai' => $this->callOpenAiCompatible('https://api.openai.com/v1/chat/completions', $apiKey, $model, $prompt),
                'openrouter' => $this->callOpenAiCompatible('https://openrouter.ai/api/v1/chat/completions', $apiKey, $model, $prompt),
                'groq' => $this->callOpenAiCompatible('https://api.groq.com/openai/v1/chat/completions', $apiKey, $model, $prompt),
                default => throw new Exception("Nhà cung cấp AI '{$provider}' không được hỗ trợ."),
            };

            return $this->parseAndValidateQuestions($rawJson);
        } catch (Exception $e) {
            Log::warning('AI Quiz Generation failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Gọi API Google Gemini.
     */
    protected function callGemini(string $apiKey, string $model, string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.7,
                ],
            ]);

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            if ($response->status() === 400 && str_contains(strtolower($errorMsg), 'api_key_invalid')) {
                throw new Exception('Google Gemini API Key không hợp lệ. Vui lòng kiểm tra lại mã API key của bạn.');
            }
            if ($response->status() === 429) {
                throw new Exception('Tài khoản Google Gemini của bạn đã hết hạn mức (Quota/Rate limit) tạm thời. Vui lòng thử lại sau.');
            }

            throw new Exception("Lỗi từ Google Gemini ({$response->status()}): {$errorMsg}");
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (! $text) {
            throw new Exception('Google Gemini không trả về dữ liệu nội dung câu hỏi.');
        }

        return $text;
    }

    /**
     * Gọi API tương thích OpenAI (OpenAI, OpenRouter, Groq).
     */
    protected function callOpenAiCompatible(string $endpoint, string $apiKey, string $model, string $prompt): string
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Bạn là chuyên gia sư phạm tạo đề thi trắc nghiệm. Luôn luôn phản hồi bằng mảng JSON thuần túy.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            if ($response->status() === 401) {
                throw new Exception('API Key không hợp lệ hoặc không có quyền truy cập. Vui lòng kiểm tra lại API Key.');
            }
            if ($response->status() === 404) {
                throw new Exception("Mô hình '{$model}' không tồn tại hoặc đã bị nhà cung cấp gỡ bỏ (deprecated/không được cấp phép cho key này). Vui lòng bấm 'Quét model từ Key' hoặc đổi sang mô hình khác (như llama-3.1-8b-instant, openai/gpt-oss-120b) hoặc dùng Google Gemini.");
            }
            if ($response->status() === 429) {
                throw new Exception('Đã vượt quá hạn mức sử dụng (Rate limit / Quota) của nhà cung cấp. Vui lòng thử lại sau hoặc nạp thêm credit.');
            }

            throw new Exception("Lỗi từ nhà cung cấp ({$response->status()}): {$errorMsg}");
        }

        $content = $response->json('choices.0.message.content');
        if (! $content) {
            throw new Exception('Mô hình AI không trả về dữ liệu câu hỏi.');
        }

        return $content;
    }

    /**
     * Bóc tách và xác thực mảng câu hỏi trắc nghiệm từ chuỗi JSON AI sinh ra.
     *
     * @return array{success: bool, questions?: array<int, mixed>, message?: string}
     */
    protected function parseAndValidateQuestions(string $rawJson): array
    {
        $cleaned = trim($rawJson);

        // Bỏ các thẻ markdown code fences nếu có ```json ... ```
        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
            $cleaned = trim($cleaned);
        }

        $data = json_decode($cleaned, true);
        if (! is_array($data)) {
            // Thử trích xuất mảng JSON đầu tiên trong văn bản
            if (preg_match('/\[\s*\{.*\}\s*\]/s', $cleaned, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (! is_array($data)) {
            throw new Exception('Không thể phân tích dữ liệu JSON do mô hình AI phản hồi không đúng cấu trúc.');
        }

        // Nếu dữ liệu được bọc trong object { "questions": [...] }
        if (! array_is_list($data) && isset($data['questions']) && is_array($data['questions'])) {
            $data = $data['questions'];
        }

        if (empty($data)) {
            throw new Exception('Mô hình AI không tạo ra câu hỏi nào.');
        }

        $normalized = [];
        foreach ($data as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $qText = trim($item['question_text'] ?? $item['question'] ?? $item['title'] ?? '');
            if ($qText === '') {
                continue;
            }

            $explanation = trim($item['explanation'] ?? $item['explain'] ?? '');
            $rawOptions = $item['options'] ?? $item['answers'] ?? [];

            $options = [];
            $hasCorrect = false;

            if (is_array($rawOptions)) {
                $rawOptionsList = array_is_list($rawOptions) ? $rawOptions : array_values($rawOptions);
                foreach ($rawOptionsList as $optIndex => $opt) {
                    if (is_array($opt)) {
                        $optText = trim($opt['option_text'] ?? $opt['text'] ?? $opt['content'] ?? '');
                        $isCorrect = (bool) ($opt['is_correct'] ?? false);
                    } else {
                        $optText = trim((string) $opt);
                        $isCorrect = ($optIndex === 0);
                    }

                    if ($optText === '') {
                        $optText = 'Phương án '.chr(65 + $optIndex);
                    }

                    if ($isCorrect) {
                        $hasCorrect = true;
                    }

                    $options[] = [
                        'option_text' => $optText,
                        'is_correct' => $isCorrect,
                    ];
                }
            }

            // Đảm bảo đủ tối thiểu 4 lựa chọn
            while (count($options) < 4) {
                $options[] = [
                    'option_text' => 'Phương án '.chr(65 + count($options)),
                    'is_correct' => false,
                ];
            }

            // Nếu chưa có đáp án đúng nào, đặt đáp án đầu tiên là đúng
            if (! $hasCorrect && isset($options[0])) {
                $options[0]['is_correct'] = true;
            }

            $normalized[] = [
                'question_text' => $qText,
                'explanation' => $explanation,
                'options' => array_slice($options, 0, 4),
            ];
        }

        if (empty($normalized)) {
            throw new Exception('Không tìm thấy câu hỏi hợp lệ trong kết quả do AI phản hồi.');
        }

        return [
            'success' => true,
            'questions' => $normalized,
        ];
    }

    /**
     * Lấy danh sách các model đang hoạt động trực tiếp từ nhà cung cấp thông qua API Key.
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function fetchAvailableModels(string $provider, string $apiKey): array
    {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            throw new Exception('Vui lòng cung cấp API Key để quét danh sách model.');
        }

        return match ($provider) {
            'groq' => $this->fetchGroqModels($apiKey),
            'gemini' => $this->fetchGeminiModels($apiKey),
            'openai' => $this->fetchOpenAiModels($apiKey),
            'openrouter' => $this->fetchOpenRouterModels($apiKey),
            default => throw new Exception("Nhà cung cấp '{$provider}' không hỗ trợ quét model."),
        };
    }

    /**
     * Lấy danh sách model từ Groq.
     *
     * @return array<int, array{id: string, name: string}>
     */
    protected function fetchGroqModels(string $apiKey): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])
            ->get('https://api.groq.com/openai/v1/models');

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            if ($response->status() === 401) {
                throw new Exception('Groq API Key không hợp lệ. Vui lòng kiểm tra lại mã API Key.');
            }

            throw new Exception("Lỗi từ Groq ({$response->status()}): {$errorMsg}");
        }

        $data = $response->json('data') ?? [];
        $models = [];
        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            if ($id && ($item['active'] ?? true)) {
                // Lọc bỏ mô hình whisper xử lý âm thanh không dùng để sinh câu hỏi trắc nghiệm
                if (str_contains(strtolower($id), 'whisper')) {
                    continue;
                }
                $models[] = [
                    'id' => $id,
                    'name' => "{$id} (Khả dụng)",
                ];
            }
        }

        if (empty($models)) {
            throw new Exception('Không tìm thấy mô hình nào trong tài khoản Groq của bạn.');
        }

        return $models;
    }

    /**
     * Lấy danh sách model từ Google Gemini.
     *
     * @return array<int, array{id: string, name: string}>
     */
    protected function fetchGeminiModels(string $apiKey): array
    {
        $response = Http::timeout(15)
            ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            if ($response->status() === 400 && str_contains(strtolower($errorMsg), 'api_key_invalid')) {
                throw new Exception('Google Gemini API Key không hợp lệ. Vui lòng kiểm tra lại.');
            }

            throw new Exception("Lỗi từ Google Gemini ({$response->status()}): {$errorMsg}");
        }

        $data = $response->json('models') ?? [];
        $models = [];
        foreach ($data as $item) {
            $name = $item['name'] ?? '';
            $methods = $item['supportedGenerationMethods'] ?? [];
            if (in_array('generateContent', $methods, true)) {
                $id = str_replace('models/', '', $name);
                $displayName = $item['displayName'] ?? $id;
                $models[] = [
                    'id' => $id,
                    'name' => "{$displayName} ({$id})",
                ];
            }
        }

        if (empty($models)) {
            throw new Exception('Không tìm thấy mô hình Gemini khả dụng nào.');
        }

        return $models;
    }

    /**
     * Lấy danh sách model từ OpenAI.
     *
     * @return array<int, array{id: string, name: string}>
     */
    protected function fetchOpenAiModels(string $apiKey): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])
            ->get('https://api.openai.com/v1/models');

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            if ($response->status() === 401) {
                throw new Exception('OpenAI API Key không hợp lệ.');
            }

            throw new Exception("Lỗi từ OpenAI ({$response->status()}): {$errorMsg}");
        }

        $data = $response->json('data') ?? [];
        $models = [];
        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            if ($id && (str_starts_with($id, 'gpt-') || str_starts_with($id, 'o1') || str_starts_with($id, 'o3'))) {
                $models[] = [
                    'id' => $id,
                    'name' => "{$id} (Khả dụng)",
                ];
            }
        }

        return $models;
    }

    /**
     * Lấy danh sách model từ OpenRouter.
     *
     * @return array<int, array{id: string, name: string}>
     */
    protected function fetchOpenRouterModels(string $apiKey): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])
            ->get('https://openrouter.ai/api/v1/models');

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();

            throw new Exception("Lỗi từ OpenRouter ({$response->status()}): {$errorMsg}");
        }

        $data = $response->json('data') ?? [];
        $models = [];
        foreach (array_slice($data, 0, 40) as $item) {
            $id = $item['id'] ?? null;
            $name = $item['name'] ?? $id;
            if ($id) {
                $models[] = [
                    'id' => $id,
                    'name' => "{$name} ({$id})",
                ];
            }
        }

        return $models;
    }
}
