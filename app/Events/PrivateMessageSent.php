<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $senderId;
    public $receiverId;
    public $senderName;
    public $senderAvatar;

    public function __construct($senderId, $receiverId, $message, $senderName = null, $senderAvatar = null)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->message = $message;
        $this->senderName = $senderName;
        $this->senderAvatar = $senderAvatar;
    }

    public function broadcastOn()
    {
        if ($this->senderId === $this->receiverId) {
            return [new PrivateChannel("chat.{$this->senderId}"),];
        } else {
            return [
                new PrivateChannel("chat.{$this->receiverId}"),
                new PrivateChannel("chat.{$this->senderId}"),
            ];
        }
    }

    public function broadcastAs()
    {
        return 'private.message.sent';
    }

    public function broadcastWith()
    {
        return ['data' => $this->message];
    }
}
