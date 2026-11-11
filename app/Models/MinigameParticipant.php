<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinigameParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'session_id',
        'nickname',
        'blook',
        'gold',
        'correct_count',
        'wrong_count',
        'chests_opened',
        'final_rank',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'gold' => 'integer',
            'correct_count' => 'integer',
            'wrong_count' => 'integer',
            'chests_opened' => 'integer',
            'final_rank' => 'integer',
            'last_active_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(MinigameRoom::class, 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(MinigameActivityLog::class, 'participant_id');
    }
}
