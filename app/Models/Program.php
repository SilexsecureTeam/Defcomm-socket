<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function attendance()
    {
        return $this->hasMany(ProgramAttendance::class, 'program_id');
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
