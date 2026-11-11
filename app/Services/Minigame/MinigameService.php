<?php

namespace App\Services\Minigame;

use App\Models\Course;
use App\Models\MinigameActivityLog;
use App\Models\MinigameParticipant;
use App\Models\MinigameQuestion;
use App\Models\MinigameQuiz;
use App\Models\MinigameRoom;
use App\Services\Ai\AiClientManager;
use Exception;
use Illuminate\Support\Facades\DB;

class MinigameService
{
    public function __construct(
        protected AiClientManager $aiManager
    ) {}

    /**
     * Tạo câu hỏi tự động bằng AI Client Manager
     *
     * @return array<int, array{question: string, options: array<string, string>, correct_answer: string, time_limit: int}>
     *
     * @throws Exception
     */
    public function generateQuestionsWithAi(string $topic, int $count = 5, string $difficulty = 'medium', ?Course $course = null): array
    {
        $count = max(1, min(20, $count));
        $difficultyLabel = match ($difficulty) {
            'easy' => 'Dễ (Kiến thức cơ bản, nhận biết)',
            'hard' => 'Khó (Tư duy chuyên sâu, vận dụng cao)',
            default => 'Trung bình (Hiểu và áp dụng kiến thức)',
        };

        $contextDetails = '';
        if ($course) {
            $contextDetails = "\nThông tin khóa học tham khảo:\n- Tiêu đề khóa học: {$course->title}\n- Mô tả: {$course->description}";
        }

        $systemPrompt = "Bạn là trợ lý giáo dục chuyên gia tạo câu hỏi trắc nghiệm khách quan cho hệ thống minigame học tập kiểu Blooket.\n"
            ."Nhiệm vụ: Tạo chính xác {$count} câu hỏi trắc nghiệm chất lượng, rõ ràng, thực tế về chủ đề: \"{$topic}\".\n"
            ."Mức độ: {$difficultyLabel}.{$contextDetails}\n\n"
            ."QUY TẮC BẮT BUỘC:\n"
            ."1. Mỗi câu hỏi phải có đúng 4 lựa chọn A, B, C, D.\n"
            ."2. Một và chỉ một lựa chọn đúng (correct_answer phải là một trong các chữ cái 'A', 'B', 'C', 'D').\n"
            ."3. Thời gian suy nghĩ time_limit từ 15 đến 30 giây (mặc định 20 giây).\n"
            ."4. Phản hồi CHỈ ĐƯỢC CHỨA DUY NHẤT một mảng JSON thuần túy (không kèm theo văn bản giải thích mở đầu hay kết luận). Cấu trúc mẫu:\n"
            ."[\n"
            ."  {\n"
            ."    \"question\": \"Nội dung câu hỏi?\",\n"
            ."    \"options\": {\n"
            ."      \"A\": \"Nội dung phương án A\",\n"
            ."      \"B\": \"Nội dung phương án B\",\n"
            ."      \"C\": \"Nội dung phương án C\",\n"
            ."      \"D\": \"Nội dung phương án D\"\n"
            ."    },\n"
            ."    \"correct_answer\": \"A\",\n"
            ."    \"time_limit\": 20\n"
            ."  }\n"
            .']';

        $userPrompt = "Hãy tạo {$count} câu hỏi trắc nghiệm bằng tiếng Việt cho chủ đề: {$topic}. Trả về định dạng JSON theo đúng quy cách.";

        $response = $this->aiManager->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        if (! ($response['success'] ?? false) || empty($response['content'])) {
            $errorMsg = $response['error'] ?? 'Không nhận được phản hồi từ dịch vụ AI.';
            throw new Exception("Lỗi tạo câu hỏi AI: {$errorMsg}");
        }

        return $this->parseQuestionsJson($response['content']);
    }

    /**
     * Phân tích và chuẩn hóa chuỗi JSON câu hỏi
     *
     * @return array<int, array{question: string, options: array<string, string>, correct_answer: string, time_limit: int}>
     *
     * @throws Exception
     */
    public function parseQuestionsJson(string $rawJson): array
    {
        // Loại bỏ markdown code block nếu AI hoặc người dùng gửi
        $cleanJson = trim($rawJson);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $cleanJson, $matches)) {
            $cleanJson = trim($matches[1]);
        }

        // Tìm điểm bắt đầu [ hoặc {
        $firstBracket = strpos($cleanJson, '[');
        $firstBrace = strpos($cleanJson, '{');

        if ($firstBracket !== false && ($firstBrace === false || $firstBracket < $firstBrace)) {
            $lastBracket = strrpos($cleanJson, ']');
            if ($lastBracket !== false) {
                $cleanJson = substr($cleanJson, $firstBracket, $lastBracket - $firstBracket + 1);
            }
        } elseif ($firstBrace !== false) {
            $lastBrace = strrpos($cleanJson, '}');
            if ($lastBrace !== false) {
                $cleanJson = substr($cleanJson, $firstBrace, $lastBrace - $firstBrace + 1);
            }
        }

        $decoded = json_decode($cleanJson, true);

        if (! is_array($decoded)) {
            throw new Exception('Dữ liệu JSON không đúng cú pháp. Vui lòng kiểm tra lại định dạng JSON.');
        }

        // Nếu dữ liệu được bọc trong object {"questions": [...]} hoặc {"data": [...]}
        if (isset($decoded['questions']) && is_array($decoded['questions'])) {
            $items = $decoded['questions'];
        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
            $items = $decoded['data'];
        } elseif (array_is_list($decoded)) {
            $items = $decoded;
        } else {
            // Trường hợp 1 object câu hỏi đơn lẻ
            $items = [$decoded];
        }

        $parsedQuestions = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $questionText = trim((string) ($item['question'] ?? $item['title'] ?? ''));
            if (empty($questionText)) {
                continue;
            }

            $rawOptions = $item['options'] ?? $item['answers'] ?? [];
            $options = [];

            if (is_array($rawOptions)) {
                if (array_is_list($rawOptions)) {
                    $keys = ['A', 'B', 'C', 'D'];
                    foreach ($rawOptions as $optIdx => $optVal) {
                        $key = $keys[$optIdx] ?? chr(65 + $optIdx);
                        $options[$key] = trim((string) $optVal);
                    }
                } else {
                    foreach ($rawOptions as $key => $optVal) {
                        $optKey = strtoupper(trim((string) $key));
                        $options[$optKey] = trim((string) $optVal);
                    }
                }
            }

            // Đảm bảo có đủ tối thiểu 2 phương án
            if (count($options) < 2) {
                continue;
            }

            $correctAnswer = strtoupper(trim((string) ($item['correct_answer'] ?? $item['answer'] ?? 'A')));
            // Nếu correct_answer không nằm trong keys mà trùng với text của option nào đó
            if (! isset($options[$correctAnswer])) {
                foreach ($options as $optKey => $optVal) {
                    if (mb_strtolower($optVal) === mb_strtolower($correctAnswer)) {
                        $correctAnswer = $optKey;
                        break;
                    }
                }
            }

            if (! isset($options[$correctAnswer])) {
                $firstKey = array_key_first($options);
                $correctAnswer = $firstKey ?? 'A';
            }

            $timeLimit = (int) ($item['time_limit'] ?? 20);
            if ($timeLimit < 5 || $timeLimit > 120) {
                $timeLimit = 20;
            }

            $parsedQuestions[] = [
                'question' => $questionText,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'time_limit' => $timeLimit,
            ];
        }

        if (empty($parsedQuestions)) {
            throw new Exception('Không tìm thấy câu hỏi hợp lệ trong dữ liệu JSON cung cấp.');
        }

        return $parsedQuestions;
    }

    /**
     * Mẫu JSON câu hỏi chuẩn để tải về hoặc tham khảo
     */
    public function getSampleJson(): array
    {
        return [
            [
                'question' => 'Framework PHP phổ biến nhất hiện nay là gì?',
                'options' => [
                    'A' => 'Laravel',
                    'B' => 'Django',
                    'C' => 'Spring Boot',
                    'D' => 'Ruby on Rails',
                ],
                'correct_answer' => 'A',
                'time_limit' => 20,
            ],
            [
                'question' => 'Trong Laravel, file định tuyến web mặc định được lưu ở đâu?',
                'options' => [
                    'A' => 'app/routes.php',
                    'B' => 'routes/web.php',
                    'C' => 'config/routes.php',
                    'D' => 'resources/routes.php',
                ],
                'correct_answer' => 'B',
                'time_limit' => 15,
            ],
            [
                'question' => 'ORM mặc định được tích hợp trong Laravel có tên là gì?',
                'options' => [
                    'A' => 'Doctrine',
                    'B' => 'Hibernate',
                    'C' => 'Eloquent',
                    'D' => 'Prisma',
                ],
                'correct_answer' => 'C',
                'time_limit' => 20,
            ],
        ];
    }

    /**
     * Lưu danh sách câu hỏi vào Quiz
     *
     * @param  array<int, array{question: string, options: array<string, string>, correct_answer: string, time_limit: int}>  $questions
     */
    public function saveQuestionsToQuiz(MinigameQuiz $quiz, array $questions, bool $replaceExisting = false): void
    {
        DB::transaction(function () use ($quiz, $questions, $replaceExisting) {
            if ($replaceExisting) {
                $quiz->questions()->delete();
                $startOrder = 1;
            } else {
                $startOrder = ((int) $quiz->questions()->max('order_number')) + 1;
            }

            foreach ($questions as $q) {
                MinigameQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $q['question'],
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'time_limit' => $q['time_limit'] ?? 20,
                    'order_number' => $startOrder++,
                ]);
            }
        });
    }

    /**
     * Xử lý mở Rương Kho báu bí ẩn (Gold Quest Game Loop)
     *
     * @return array{type: string, title: string, description: string, new_gold: int, diff: int, icon: string}
     */
    public function openMysteryChest(MinigameRoom $room, MinigameParticipant $participant, int $chestIndex = 1): array
    {
        return DB::transaction(function () use ($room, $participant) {
            // Tải lại participant mới nhất trong transaction
            $participant = MinigameParticipant::where('id', $participant->id)->lockForUpdate()->first();
            $currentGold = (int) $participant->gold;

            // Roll xác suất chuẩn Blooket Gold Quest (1 - 100)
            // 40% Gold (Small +25/50/100, Medium +250/500, Mega +1000/2500)
            // 18% Multiplier (x2, x3)
            // 18% Steal (Cướp 10%, 25%, 50% từ đối thủ)
            // 10% Swap (Hoán đổi toàn bộ vàng với người dẫn đầu/cao hơn)
            // 8% Trap (Bẫy mất 25% vàng)
            // 6% Nothing (Rương rỗng - 0 vàng)
            $roll = random_int(1, 100);

            if ($roll <= 40) {
                // Rương cộng vàng theo các cấp độ
                $tierRoll = random_int(1, 100);
                if ($tierRoll <= 50) {
                    $goldOptions = [25, 50, 75, 100];
                    $gained = $goldOptions[array_rand($goldOptions)];
                    $title = "+{$gained} Vàng!";
                    $desc = "Bạn tìm thấy túi vàng nhỏ chứa {$gained} thỏi vàng!";
                } elseif ($tierRoll <= 85) {
                    $goldOptions = [200, 350, 500];
                    $gained = $goldOptions[array_rand($goldOptions)];
                    $title = "+{$gained} Vàng!";
                    $desc = "Kho báu hoàng gia! Bạn mở trúng rương chứa {$gained} thỏi vàng lấp lánh!";
                } else {
                    $goldOptions = [1000, 2500];
                    $gained = $goldOptions[array_rand($goldOptions)];
                    $title = "+{$gained} VÀNG KHỦNG (JACKPOT)!";
                    $desc = "SIÊU PHẨM BLOOKET! Bạn đào trúng mỏ vàng thần thoại chứa tận {$gained} vàng ròng!";
                }

                $newGold = $currentGold + $gained;
                $participant->update(['gold' => $newGold]);

                MinigameActivityLog::create([
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'action_type' => 'gold',
                    'message' => "{$participant->nickname} mở rương kho báu nhận được +{$gained} vàng!",
                ]);

                return [
                    'type' => 'gold',
                    'title' => $title,
                    'description' => $desc,
                    'new_gold' => $newGold,
                    'diff' => $gained,
                    'icon' => 'gold',
                ];
            }

            if ($roll <= 58) {
                // Rương nhân vàng (Multiplier: x2 hoặc x3)
                $multiplier = (random_int(1, 10) <= 7) ? 2 : 3;

                if ($currentGold > 0) {
                    $newGold = $currentGold * $multiplier;
                    $diff = $newGold - $currentGold;
                    $participant->update(['gold' => $newGold]);

                    MinigameActivityLog::create([
                        'room_id' => $room->id,
                        'participant_id' => $participant->id,
                        'action_type' => 'multiplier',
                        'message' => "{$participant->nickname} kích hoạt phép nhân x{$multiplier} (lên {$newGold} vàng)!",
                    ]);

                    return [
                        'type' => 'multiplier',
                        'title' => "Nhân {$multiplier} Lần Vàng (x{$multiplier})!",
                        'description' => "Phép màu Blooket! Toàn bộ số vàng của bạn đã được nhân lên gấp {$multiplier} lần!",
                        'new_gold' => $newGold,
                        'diff' => $diff,
                        'icon' => 'multiplier',
                    ];
                }

                // Nếu đang 0 vàng thì tặng 100 vàng khởi nghiệp
                $gained = 100;
                $newGold = $currentGold + $gained;
                $participant->update(['gold' => $newGold]);

                MinigameActivityLog::create([
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'action_type' => 'gold',
                    'message' => "{$participant->nickname} nhận được 100 vàng khởi nghiệp may mắn!",
                ]);

                return [
                    'type' => 'gold',
                    'title' => '+100 Vàng Khởi Nghiệp!',
                    'description' => 'Bạn chưa có vàng để nhân đôi, thần may mắn Blooket tặng bạn 100 vàng khởi nghiệp!',
                    'new_gold' => $newGold,
                    'diff' => $gained,
                    'icon' => 'gold',
                ];
            }

            if ($roll <= 76) {
                // Cướp vàng (Steal 10%, 25% hoặc 50% vàng từ đối thủ)
                $otherPlayers = MinigameParticipant::where('room_id', $room->id)
                    ->where('id', '!=', $participant->id)
                    ->where('gold', '>', 0)
                    ->lockForUpdate()
                    ->get();

                if ($otherPlayers->isNotEmpty()) {
                    $topPlayer = $otherPlayers->sortByDesc('gold')->first();
                    $target = (random_int(1, 2) === 1) ? $topPlayer : $otherPlayers->random();

                    $percentOptions = [10, 25, 50];
                    $percent = $percentOptions[array_rand($percentOptions)];
                    $stolen = (int) ceil(($target->gold * $percent) / 100);
                    $stolen = max(10, $stolen);

                    $target->decrement('gold', $stolen);
                    $newGold = $currentGold + $stolen;
                    $participant->update(['gold' => $newGold]);

                    MinigameActivityLog::create([
                        'room_id' => $room->id,
                        'participant_id' => $participant->id,
                        'action_type' => 'steal',
                        'message' => "🔥 {$participant->nickname} vừa cướp {$stolen} vàng ({$percent}%) của {$target->nickname}!",
                    ]);

                    return [
                        'type' => 'steal',
                        'title' => "Cướp {$stolen} Vàng ({$percent}%)!",
                        'description' => "Bạn đã ra tay thần tốc và cướp {$stolen} vàng từ {$target->nickname}!",
                        'new_gold' => $newGold,
                        'diff' => $stolen,
                        'icon' => 'steal',
                    ];
                }

                // Không có ai để cướp -> nhận 150 vàng
                $gained = 150;
                $newGold = $currentGold + $gained;
                $participant->update(['gold' => $newGold]);

                MinigameActivityLog::create([
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'action_type' => 'gold',
                    'message' => "{$participant->nickname} mở rương kho báu nhận được +{$gained} vàng!",
                ]);

                return [
                    'type' => 'gold',
                    'title' => '+150 Vàng!',
                    'description' => 'Chưa có đối thủ nào có vàng để cướp, bạn tự khai quật được 150 vàng!',
                    'new_gold' => $newGold,
                    'diff' => $gained,
                    'icon' => 'gold',
                ];
            }

            if ($roll <= 86) {
                // Đổi vàng (Swap - Hoán đổi toàn bộ vàng với người có số vàng cao hơn)
                $higherPlayers = MinigameParticipant::where('room_id', $room->id)
                    ->where('id', '!=', $participant->id)
                    ->where('gold', '>', $currentGold)
                    ->lockForUpdate()
                    ->orderByDesc('gold')
                    ->get();

                if ($higherPlayers->isNotEmpty()) {
                    $target = $higherPlayers->first();
                    $targetGold = (int) $target->gold;

                    $target->update(['gold' => $currentGold]);
                    $participant->update(['gold' => $targetGold]);

                    MinigameActivityLog::create([
                        'room_id' => $room->id,
                        'participant_id' => $participant->id,
                        'action_type' => 'swap',
                        'message' => "🔄 {$participant->nickname} hoán đổi toàn bộ số vàng với {$target->nickname} ({$currentGold} ⇄ {$targetGold})!",
                    ]);

                    return [
                        'type' => 'swap',
                        'title' => "Hoán Đổi Vàng Với {$target->nickname}!",
                        'description' => "Lật kèo ngoạn mục! Bạn đã đổi toàn bộ số vàng của mình lấy {$targetGold} vàng của {$target->nickname}!",
                        'new_gold' => $targetGold,
                        'diff' => $targetGold - $currentGold,
                        'icon' => 'swap',
                    ];
                }

                // Nếu đang Top 1 hoặc không ai cao hơn -> cộng 200 vàng
                $gained = 200;
                $newGold = $currentGold + $gained;
                $participant->update(['gold' => $newGold]);

                MinigameActivityLog::create([
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'action_type' => 'gold',
                    'message' => "{$participant->nickname} mở rương kho báu nhận được +{$gained} vàng!",
                ]);

                return [
                    'type' => 'gold',
                    'title' => '+200 Vàng!',
                    'description' => 'Bạn đang dẫn đầu bảng xếp hạng, nhận thưởng nóng 200 vàng từ vương miện!',
                    'new_gold' => $newGold,
                    'diff' => $gained,
                    'icon' => 'gold',
                ];
            }

            if ($roll <= 94) {
                // Bẫy (Trap - Mất 25% số vàng hiện tại)
                if ($currentGold > 0) {
                    $lost = (int) ceil($currentGold * 0.25);
                    $newGold = max(0, $currentGold - $lost);
                    $participant->update(['gold' => $newGold]);

                    MinigameActivityLog::create([
                        'room_id' => $room->id,
                        'participant_id' => $participant->id,
                        'action_type' => 'trap',
                        'message' => "⚠️ {$participant->nickname} dẫm phải bẫy gai và mất {$lost} vàng!",
                    ]);

                    return [
                        'type' => 'trap',
                        'title' => "Dẫm Phải Bẫy! Mất -{$lost} Vàng!",
                        'description' => "Rương chứa bẫy ma thuật của quái vật! Bạn bị rơi mất {$lost} vàng!",
                        'new_gold' => $newGold,
                        'diff' => -$lost,
                        'icon' => 'trap',
                    ];
                }

                MinigameActivityLog::create([
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'action_type' => 'trap',
                    'message' => "⚠️ {$participant->nickname} mở trúng bẫy nhưng may mắn chưa có vàng để mất!",
                ]);

                return [
                    'type' => 'trap',
                    'title' => 'Bẫy Rỗng!',
                    'description' => 'Rương có bẫy nhưng bạn chưa có vàng, thần may mắn mỉm cười với bạn!',
                    'new_gold' => 0,
                    'diff' => 0,
                    'icon' => 'trap',
                ];
            }

            // Rương rỗng (Nothing inside - 0 vàng, chuẩn phong cách Blooket)
            MinigameActivityLog::create([
                'room_id' => $room->id,
                'participant_id' => $participant->id,
                'action_type' => 'nothing',
                'message' => "💨 {$participant->nickname} mở trúng rương rỗng (không có gì)!",
            ]);

            return [
                'type' => 'nothing',
                'title' => 'Không Có Gì Cả!',
                'description' => 'Rương này hoàn toàn trống rỗng! Thử vận may ở câu hỏi tiếp theo nhé!',
                'new_gold' => $currentGold,
                'diff' => 0,
                'icon' => 'nothing',
            ];
        });
    }
}
