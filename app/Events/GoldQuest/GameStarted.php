<?php

namespace App\Events\GoldQuest;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomPin,
        public int $durationSeconds
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("gold-quest.{$this->roomPin}");
    }

    public function broadcastAs(): string
    {
        return 'GameStarted';
    }
}
