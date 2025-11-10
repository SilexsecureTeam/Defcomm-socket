<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class BountyUser extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // A user belongs to one manager
    public function group()
    {
        return $this->belongsTo(BountyUser::class, 'rel_group');
    }
}
