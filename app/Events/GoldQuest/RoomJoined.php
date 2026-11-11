<?php

namespace App\Events\GoldQuest;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{player_id: string, nickname: string, blook: string}  $player
     */
    public function __construct(
        public string $roomPin,
        public array $player
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("gold-quest.{$this->roomPin}");
    }

    public function broadcastAs(): string
    {
        return 'RoomJoined';
    }
}
