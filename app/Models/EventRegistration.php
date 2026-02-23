<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function form()
    {
        return $this->belongsTo(EventForm::class, 'form_id')->withDefault();
    }

    public function certificates()
    {
        return $this->belongsToMany(Certificate::class, 'certificate_registrations')
            ->withPivot('is_collected', 'is_sent')
            ->withTimestamps();
    }

    public function souvenirs()
    {
        return $this->belongsToMany(Souvenir::class, 'souvenir_registrations')
            ->withPivot('is_collected')
            ->withTimestamps();
    }
}
