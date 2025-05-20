<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function userCreate()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function meetingLog()
    {
        return $this->hasMany(MeetingLog::class, 'meetings_id');
    }
}
