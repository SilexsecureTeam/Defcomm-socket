<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramAttendance extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function userBounty()
    {
        return $this->belongsTo(BountyUser::class, 'user_id')->withDefault();
    }
}
