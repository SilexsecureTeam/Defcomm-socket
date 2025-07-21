<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WailkieTalkieSubscriber extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function channel()
    {
        return $this->belongsTo(WailkieTalkieChannel::class, 'channel_id')->withDefault();
    }

    public function recorder()
    {
        return $this->hasMany(WailkieTalkieRecorder::class, 'subscriber_id');
    }
}
