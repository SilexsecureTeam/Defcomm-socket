<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupId;
    public $senderId;
    public $message;
    public $senderName;
    public $groupName;
    public $senderAvatar;

    public function __construct($groupId, $senderId, $message, $senderName = null, $groupName = null, $senderAvatar = null)
    {
        $this->groupId = $groupId;
        $this->senderId = $senderId;
        $this->message = $message;
        $this->senderName = $senderName;
        $this->groupName = $groupName;
        $this->senderAvatar = $senderAvatar;
    }

    public function broadcastOn()
    {
        return new Channel("group.{$this->groupId}");
    }

    public function broadcastAs()
    {
        return 'group.message.sent';
    }

    public function broadcastWith()
    {
        return ['data' => $this->message];
    }
}
