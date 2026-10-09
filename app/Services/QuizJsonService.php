<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizJsonService
{
    /**
     * Trích xuất và chuẩn hóa dữ liệu Quiz & Câu hỏi từ JSON string hoặc File upload.
     *
     * @return array{
     *     quiz: array{
     *         title: ?string,
     *         description: ?string,
     *         duration_minutes: ?int,
     *         passing_score: ?float,
     *         randomize_questions: ?bool
     *     },
     *     questions: array<int, array{
     *         question_text: string,
     *         explanation: ?string,
     *         options: array<int, array{option_text: string, is_correct: bool}>
     *     }>
     * }
     *
     * @throws ValidationException
     */
    public function extractFromJson(string|UploadedFile|null $source): array
    {
        if (! $source) {
            throw ValidationException::withMessages([
                'json_content' => 'Vui lòng cung cấp nội dung JSON hoặc chọn tệp tin JSON.',
            ]);
        }

        $jsonString = '';

        if ($source instanceof UploadedFile) {
            if (! $source->isValid()) {
                throw ValidationException::withMessages([
                    'json_file' => 'Tệp tin tải lên bị lỗi hoặc không hợp lệ.',
                ]);
            }
            $jsonString = $source->get();
        } else {
            $jsonString = $source;
        }

        // Loại bỏ BOM UTF-8 nếu có và khoảng trắng thừa
        $jsonString = preg_replace('/^\xEF\xBB\xBF/', '', trim($jsonString));

        if (empty($jsonString)) {
            throw ValidationException::withMessages([
                'json_content' => 'Nội dung JSON không được để trống.',
            ]);
        }

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'json_content' => 'Cú pháp JSON không hợp lệ: '.json_last_error_msg(),
            ]);
        }

        if (! is_array($data)) {
            throw ValidationException::withMessages([
                'json_content' => 'Dữ liệu JSON phải là đối tượng (Object {}) hoặc danh sách câu hỏi (Array []).',
            ]);
        }

        $quizInfo = [
            'title' => null,
            'description' => null,
            'type' => null,
            'duration_minutes' => null,
            'passing_score' => null,
            'randomize_questions' => null,
        ];

        $rawQuestions = [];

        // Trường hợp 1: Mảng các câu hỏi trực tiếp [ { "question_text": ... }, ... ]
        if (array_is_list($data) && isset($data[0]) && is_array($data[0])) {
            $rawQuestions = $data;
        } else {
            // Trường hợp 2: Đối tượng đầy đủ { "title": "...", "questions": [...] }
            $quizSource = isset($data['quiz']) && is_array($data['quiz']) ? $data['quiz'] : $data;

            if (isset($quizSource['title']) && is_string($quizSource['title'])) {
                $quizInfo['title'] = trim($quizSource['title']);
            }
            if (isset($quizSource['description']) && is_string($quizSource['description'])) {
                $quizInfo['description'] = trim($quizSource['description']);
            }
            if (isset($quizSource['type']) && is_string($quizSource['type'])) {
                $rawType = strtolower(trim($quizSource['type']));
                if (in_array($rawType, ['midterm', 'giua_ky', 'giữa kỳ', 'kiểm tra giữa kỳ'])) {
                    $quizInfo['type'] = 'midterm';
                } elseif (in_array($rawType, ['final', 'cuoi_ky', 'cuối kỳ', 'kiểm tra cuối kỳ'])) {
                    $quizInfo['type'] = 'final';
                } else {
                    $quizInfo['type'] = 'quiz';
                }
            }
            if (isset($quizSource['duration_minutes'])) {
                $quizInfo['duration_minutes'] = (int) $quizSource['duration_minutes'];
            }
            if (isset($quizSource['passing_score'])) {
                $quizInfo['passing_score'] = (float) $quizSource['passing_score'];
            }
            if (isset($quizSource['randomize_questions'])) {
                $quizInfo['randomize_questions'] = (bool) $quizSource['randomize_questions'];
            }

            if (isset($data['questions']) && is_array($data['questions'])) {
                $rawQuestions = $data['questions'];
            }
        }

        $normalizedQuestions = [];

        foreach ($rawQuestions as $index => $q) {
            $questionNum = $index + 1;

            if (! is_array($q)) {
                throw ValidationException::withMessages([
                    'json_content' => "Dữ liệu câu hỏi thứ {$questionNum} không hợp lệ (phải là đối tượng JSON).",
                ]);
            }

            $questionText = $q['question_text'] ?? $q['question'] ?? $q['title'] ?? null;
            if (empty($questionText) || ! is_string($questionText)) {
                throw ValidationException::withMessages([
                    'json_content' => "Câu hỏi thứ {$questionNum} thiếu nội dung (thuộc tính 'question_text').",
                ]);
            }

            $rawOptions = $q['options'] ?? $q['answers'] ?? [];
            if (! is_array($rawOptions) || count($rawOptions) < 2) {
                throw ValidationException::withMessages([
                    'json_content' => "Câu hỏi thứ {$questionNum} phải có ít nhất 2 phương án trả lời trong mảng 'options'.",
                ]);
            }

            $explanation = $q['explanation'] ?? $q['explain'] ?? null;

            // Xử lý options và xác định đáp án đúng
            $parsedOptions = [];
            $correctIndex = null;

            // Kiểm tra xem options có sẵn thuộc tính is_correct không
            $hasExplicitCorrect = false;

            // Chuyển mảng nếu là key-value ['A' => 'Opt 1', 'B' => 'Opt 2']
            $optItems = array_values($rawOptions);

            foreach ($optItems as $optIdx => $optVal) {
                $optText = '';
                $isCorrect = false;

                if (is_array($optVal)) {
                    $optText = $optVal['option_text'] ?? $optVal['text'] ?? $optVal['content'] ?? '';
                    if (! empty($optVal['is_correct'])) {
                        $isCorrect = true;
                        $hasExplicitCorrect = true;
                        $correctIndex = $optIdx;
                    }
                } else {
                    $optText = (string) $optVal;
                }

                $optText = trim($optText);
                if ($optText === '') {
                    throw ValidationException::withMessages([
                        'json_content' => "Câu hỏi thứ {$questionNum} có phương án thứ ".($optIdx + 1).' rỗng.',
                    ]);
                }

                $parsedOptions[] = [
                    'option_text' => $optText,
                    'is_correct' => $isCorrect,
                ];
            }

            // Nếu chưa tìm thấy is_correct trong từng option, kiểm tra trường 'correct_option' của câu hỏi
            if (! $hasExplicitCorrect) {
                $correctVal = $q['correct_option'] ?? $q['correct_answer'] ?? $q['answer'] ?? null;

                if ($correctVal !== null) {
                    if (is_numeric($correctVal)) {
                        $correctIndex = (int) $correctVal;
                    } elseif (is_string($correctVal)) {
                        $trimmedVal = trim($correctVal);
                        $letterMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5];
                        $upperLetter = strtoupper($trimmedVal);

                        if (isset($letterMap[$upperLetter])) {
                            $correctIndex = $letterMap[$upperLetter];
                        } else {
                            // Thử tìm theo nội dung chuỗi đáp án trùng khớp
                            foreach ($parsedOptions as $idx => $opt) {
                                if (mb_strtolower($opt['option_text']) === mb_strtolower($trimmedVal)) {
                                    $correctIndex = $idx;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($correctIndex === null || $correctIndex < 0 || $correctIndex >= count($parsedOptions)) {
                    // Mặc định chọn đáp án đầu tiên nếu không khớp
                    $correctIndex = 0;
                }

                foreach ($parsedOptions as $idx => &$opt) {
                    $opt['is_correct'] = ($idx === $correctIndex);
                }
                unset($opt);
            }

            $normalizedQuestions[] = [
                'question_text' => trim($questionText),
                'explanation' => is_string($explanation) ? trim($explanation) : null,
                'options' => $parsedOptions,
            ];
        }

        return [
            'quiz' => $quizInfo,
            'questions' => $normalizedQuestions,
        ];
    }

    /**
     * Thêm danh sách câu hỏi vào Quiz đã tồn tại.
     *
     * @param  array<int, array{question_text: string, explanation: ?string, options: array<int, array{option_text: string, is_correct: bool}>}>  $questions
     * @return int Số lượng câu hỏi đã tạo
     */
    public function importQuestionsToQuiz(Quiz $quiz, array $questions): int
    {
        if (empty($questions)) {
            return 0;
        }

        return DB::transaction(function () use ($quiz, $questions) {
            $currentMaxOrder = $quiz->questions()->max('order_number') ?? 0;
            $count = 0;

            foreach ($questions as $qData) {
                $currentMaxOrder++;

                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $qData['question_text'],
                    'explanation' => $qData['explanation'] ?? null,
                    'order_number' => $currentMaxOrder,
                ]);

                foreach ($qData['options'] as $opt) {
                    QuizOption::create([
                        'quiz_question_id' => $question->id,
                        'option_text' => $opt['option_text'],
                        'is_correct' => (bool) $opt['is_correct'],
                    ]);
                }

                $count++;
            }

            return $count;
        });
    }

    /**
     * Tạo Quiz kèm toàn bộ câu hỏi trong transaction.
     *
     * @param  array<string, mixed>  $quizAttributes
     * @param  array<int, array{question_text: string, explanation: ?string, options: array<int, array{option_text: string, is_correct: bool}>}>  $questions
     */
    public function createQuizWithQuestions(array $quizAttributes, array $questions = []): Quiz
    {
        return DB::transaction(function () use ($quizAttributes, $questions) {
            $quiz = Quiz::create($quizAttributes);

            if (! empty($questions)) {
                $this->importQuestionsToQuiz($quiz, $questions);
            }

            return $quiz;
        });
    }

    /**
     * Cung cấp chuỗi JSON mẫu để hiển thị cho người dùng tham khảo hoặc sao chép.
     */
    public static function getSampleJson(): string
    {
        $sample = [
            'title' => 'Kiểm tra kiến thức Lập trình Web với Laravel',
            'description' => 'Đề thi trắc nghiệm đánh giá kiến thức cơ bản về MVC, Eloquent ORM và Blade Templates.',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'randomize_questions' => true,
            'questions' => [
                [
                    'question_text' => 'Eloquent trong Laravel đóng vai trò gì?',
                    'explanation' => 'Eloquent là thư viện ORM (Object-Relational Mapping) mạnh mẽ được tích hợp sẵn trong Laravel.',
                    'options' => [
                        'ORM (Object-Relational Mapping)',
                        'Template Engine',
                        'Routing Handler',
                        'CSS Framework',
                    ],
                    'correct_option' => 0,
                ],
                [
                    'question_text' => 'Tập tin cấu hình cơ sở dữ liệu và biến môi trường mặc định là file nào?',
                    'explanation' => 'File .env lưu cấu hình môi trường như DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD.',
                    'options' => [
                        'config/database.php',
                        '.env',
                        'composer.json',
                        'routes/web.php',
                    ],
                    'correct_option' => 1,
                ],
                [
                    'question_text' => 'Lệnh Artisan nào dùng để tạo một Controller mới trong Laravel?',
                    'explanation' => 'Cú pháp chuẩn là php artisan make:controller [TênController]',
                    'options' => [
                        'php artisan new:controller',
                        'php artisan build:controller',
                        'php artisan make:controller',
                        'php artisan generate:controller',
                    ],
                    'correct_option' => 2,
                ],
            ],
        ];

        return json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
