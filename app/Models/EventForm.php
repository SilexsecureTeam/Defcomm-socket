<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventForm extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id')->withDefault();
    }

    public function group()
    {
        return $this->belongsTo(CompanyGroup::class, 'group_id')->withDefault();
    }

    public function event()
    {
        return $this->hasMany(EventRegistration::class, 'form_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'form_id');
    }

    public function souvenirs()
    {
        return $this->hasMany(Souvenir::class, 'form_id');
    }
}
