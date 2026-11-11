<?php

namespace App\Events\GoldQuest;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, array{rank: int, nickname: string, blook: string, gold: int}>  $podium
     */
    public function __construct(
        public string $roomPin,
        public array $podium
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("gold-quest.{$this->roomPin}");
    }

    public function broadcastAs(): string
    {
        return 'GameEnded';
    }
}
