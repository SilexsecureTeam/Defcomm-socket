<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileFolder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function file()
    {
        return $this->belongsTo(Files::class, 'file_id')->withDefault();
    }

    public function folder()
    {
        return $this->belongsTo(Folders::class, 'folder_id')->withDefault();
    }
}
