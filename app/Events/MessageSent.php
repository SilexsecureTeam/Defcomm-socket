<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $senderId;
    public $senderName;
    public $senderAvatar;

    public function __construct($message, $senderId = null, $senderName = null, $senderAvatar = null)
    {
        $this->message = $message;
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->senderAvatar = $senderAvatar;
    }

    public function broadcastOn()
    {
        return new Channel('public-chat');
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return ['data' => $this->message];
    }
}
