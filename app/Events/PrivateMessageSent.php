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

    public function __construct($senderId, $receiverId, $message)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("chat.{$this->receiverId}");
        // return new Channel("chat.{$this->receiverId}");
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
