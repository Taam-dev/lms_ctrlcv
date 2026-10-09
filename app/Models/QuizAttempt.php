<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'student_id',
        'total_questions',
        'correct_answers',
        'score',
        'is_passed',
        'answers',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'is_passed' => 'boolean',
            'answers' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
