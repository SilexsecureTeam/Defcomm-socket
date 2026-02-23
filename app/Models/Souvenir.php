<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Souvenir extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function form()
    {
        return $this->belongsTo(EventForm::class, 'form_id');
    }

    public function registrations()
    {
        return $this->belongsToMany(EventRegistration::class, 'souvenir_registrations')
            ->withPivot('is_collected')
            ->withTimestamps();
    }
}
