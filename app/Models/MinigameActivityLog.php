<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'participant_id',
        'action_type',
        'message',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(MinigameRoom::class, 'room_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(MinigameParticipant::class, 'participant_id');
    }
}
