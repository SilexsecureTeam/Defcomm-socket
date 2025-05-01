<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function companyUsers()
    {
        return $this->belongsTo(CompanyUser::class, 'company_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withDefault();
    }

    public function filesShares()
    {
        return $this->hasMany(FilesShares::class, 'file_id');
    }
    
    public function fileShareLog()
    {
        return $this->hasMany(FileShareLog::class, 'file_id');
    }
}
