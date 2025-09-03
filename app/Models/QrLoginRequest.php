<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrLoginRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }
}
