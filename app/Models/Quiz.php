<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'course_id',
        'teacher_id',
        'type',
        'title',
        'description',
        'duration_minutes',
        'passing_score',
        'randomize_questions',
        'status',
        'starts_at',
        'ends_at',
    ];

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'midterm' => 'Kiểm tra giữa kỳ',
            'final' => 'Kiểm tra cuối kỳ',
            default => 'Kiểm tra',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'midterm' => 'bg-amber-50 text-amber-700 border-amber-200',
            'final' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    public function isExpired(): bool
    {
        return $this->ends_at !== null && now()->gt($this->ends_at);
    }

    public function isNotStarted(): bool
    {
        return $this->starts_at !== null && now()->lt($this->starts_at);
    }

    public function isAvailable(): bool
    {
        return ! $this->isExpired() && ! $this->isNotStarted();
    }

    protected function casts(): array
    {
        return [
            'randomize_questions' => 'boolean',
            'passing_score' => 'float',
            'duration_minutes' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order_number');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
