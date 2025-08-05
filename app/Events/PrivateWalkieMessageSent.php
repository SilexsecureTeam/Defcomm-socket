<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class PrivateWalkieMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $walkieId;
    public $senderId;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $walkieId, $message)
    {
        $this->walkieId = $walkieId;
        $this->senderId = $senderId;
        $this->message = $message;
    }

    /**
     * The channel the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("walkie.{$this->walkieId}");
    }

    /**
     * The name of the event to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'walkie.message.sent';
    }

    /**
     * Customize the broadcast payload.
     */
    public function broadcastWith(): array
    {
        return ['data' => $this->message];
    }
}
