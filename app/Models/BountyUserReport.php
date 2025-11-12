<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BountyUserReport extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(BountyUser::class, 'user_id')->withDefault();
    }
    
    public function program()
    {
        return $this->belongsTo(BountyUserProgram::class, 'program_id')->withDefault();
    }
}
