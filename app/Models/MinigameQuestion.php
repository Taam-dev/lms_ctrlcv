<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question',
        'options',
        'correct_answer',
        'time_limit',
        'order_number',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'time_limit' => 'integer',
            'order_number' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(MinigameQuiz::class, 'quiz_id');
    }

    /**
     * Kiểm tra đáp án của người chơi
     */
    public function isCorrect(string $answer): bool
    {
        $normalizedAnswer = trim(mb_strtolower($answer));
        $normalizedCorrect = trim(mb_strtolower((string) $this->correct_answer));

        if ($normalizedAnswer === $normalizedCorrect) {
            return true;
        }

        // Nếu correct_answer là key 'A', 'B', 'C', 'D' và đáp án gửi lên là nội dung text của option
        $options = $this->options ?? [];
        if (isset($options[$this->correct_answer])) {
            if ($normalizedAnswer === trim(mb_strtolower((string) $options[$this->correct_answer]))) {
                return true;
            }
        }

        // Ngược lại, nếu correct_answer là text và answer là key 'A','B','C','D'
        if (isset($options[$answer])) {
            if (trim(mb_strtolower((string) $options[$answer])) === $normalizedCorrect) {
                return true;
            }
        }

        return false;
    }
}
