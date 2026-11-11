<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinigameRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'quiz_id',
        'pin_code',
        'game_type',
        'status',
        'duration_minutes',
        'settings',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'settings' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(MinigameQuiz::class, 'quiz_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MinigameParticipant::class, 'room_id')->orderByDesc('gold');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(MinigameActivityLog::class, 'room_id')->latest();
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isPlaying(): bool
    {
        if ($this->status !== 'playing') {
            return false;
        }

        // Tự động kiểm tra nếu đã quá thời gian
        if ($this->started_at && $this->remainingSeconds() <= 0) {
            $this->update([
                'status' => 'finished',
                'ended_at' => Carbon::now(),
            ]);

            return false;
        }

        return true;
    }

    public function isFinished(): bool
    {
        if ($this->status === 'finished') {
            return true;
        }

        if ($this->status === 'playing' && $this->remainingSeconds() <= 0) {
            $this->update([
                'status' => 'finished',
                'ended_at' => Carbon::now(),
            ]);

            return true;
        }

        return false;
    }

    public function remainingSeconds(): int
    {
        if (! $this->started_at) {
            return $this->duration_minutes * 60;
        }

        $endTime = $this->started_at->copy()->addMinutes($this->duration_minutes);
        $diff = Carbon::now()->diffInSeconds($endTime, false);

        return max(0, (int) $diff);
    }

    /**
     * Sinh mã PIN 6 số duy nhất không trùng lặp
     */
    public static function generatePin(): string
    {
        do {
            $pin = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('pin_code', $pin)->whereIn('status', ['waiting', 'playing'])->exists());

        return $pin;
    }
}
