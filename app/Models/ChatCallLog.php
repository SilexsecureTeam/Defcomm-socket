<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatCallLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function userSender()
    {
        return $this->belongsTo(User::class, 'send_user_id')->withDefault();
    }

    public function userReciever()
    {
        return $this->belongsTo(User::class, 'recieve_user_id')->withDefault();
    }
    
    public function chatMess()
    {
        return $this->belongsTo(ChatMessage::class, 'recieve_user_id')->withDefault();
    }
}
