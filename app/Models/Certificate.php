<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function form()
    {
        return $this->belongsTo(EventForm::class, 'form_id');
    }

    public function registrations()
    {
        return $this->belongsToMany(EventRegistration::class, 'certificate_registrations')
            ->withPivot('is_collected', 'is_sent')
            ->withTimestamps();
    }
}
