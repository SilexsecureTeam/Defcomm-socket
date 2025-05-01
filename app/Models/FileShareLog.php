<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShareLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function companyUsers()
    {
        return $this->belongsTo(CompanyUser::class, 'company_id')->withDefault();
    }

    public function file()
    {
        return $this->belongsTo(Files::class, 'file_id')->withDefault();
    }
}
