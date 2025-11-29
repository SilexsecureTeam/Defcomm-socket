<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BountyCategorySub extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(BountyCategory::class, 'category_id')->withDefault();
    }

    public function report()
    {
        return $this->hasMany(BountyUserReport::class, 'category_sub');
    }
}
