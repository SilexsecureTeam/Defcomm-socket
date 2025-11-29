<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BountyCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sub()
    {
        return $this->hasMany(BountyCategorySub::class, 'category');
    }

    public function report()
    {
        return $this->hasMany(BountyUserReport::class, 'program_id');
    }
}
