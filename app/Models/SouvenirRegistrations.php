<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SouvenirRegistrations extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function form()
    {
        return $this->belongsTo(EventForm::class, 'event_registration_id')->withDefault();
    }

    public function souvenir()
    {
        return $this->belongsTo(Souvenir::class, 'souvenir_id')->withDefault();
    }

}
