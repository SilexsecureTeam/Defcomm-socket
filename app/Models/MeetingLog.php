<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function userjoin()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meetings_id')->withDefault();
    }
}
