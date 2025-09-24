<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WailkieTalkieChannel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function wailkieTalkieRecorder()
    {
        return $this->hasMany(WailkieTalkieRecorder::class, 'channel_id');
    }

    public function wailkieTalkieSubscriber()
    {
        return $this->hasMany(WailkieTalkieSubscriber::class, 'channel_id');
    }

    public function subscriberLog()
    {
        return $this->hasMany(WailkieTalkieSubscriberLog::class, 'channel_id');
    }
}
