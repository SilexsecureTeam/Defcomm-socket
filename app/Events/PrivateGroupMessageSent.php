<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class PrivateGroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupId;
    public $senderId;
    public $message;
    public $senderName;
    public $groupName;
    public $senderAvatar;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $groupId, $message, $senderName = null, $groupName = null, $senderAvatar = null)
    {
        $this->groupId = $groupId;
        $this->senderId = $senderId;
        $this->message = $message;
        $this->senderName = $senderName;
        $this->groupName = $groupName;
        $this->senderAvatar = $senderAvatar;
    }

    /**
     * The channel the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("group.{$this->groupId}");
    }

    /**
     * The name of the event to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'group.message.sent';
    }

    /**
     * Customize the broadcast payload.
     */
    public function broadcastWith(): array
    {
        return ['data' => $this->message];
    }
}
