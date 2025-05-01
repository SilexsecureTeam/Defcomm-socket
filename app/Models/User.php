<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function CompanyUsers()
    {
        return $this->belongsTo(CompanyUser::class, 'company_id')->withDefault();
    }

    public function CompanyUser()
    {
        return $this->hasOne(CompanyUser::class, 'user_id')->withDefault();
    }

    public function companyGroupUser()
    {
        return $this->hasMany(CompanyGroupUser::class, 'user_id');
    }

    public function files()
    {
        return $this->hasMany(Files::class, 'uploaded_by');
    }

    public function filesShares()
    {
        return $this->hasMany(FilesShares::class, 'user_id');
    }
    
    public function filesSharesFrom()
    {
        return $this->hasMany(FilesShares::class, 'user_from');
    }

    public function fileShareLog()
    {
        return $this->hasMany(FileShareLog::class, 'user_id');
    }
    
    public function contactList()
    {
        return $this->hasMany(ContactList::class, 'user_id');
    }
    
    public function contactListLink()
    {
        return $this->hasMany(ContactList::class, 'user_link');
    }

    public function chatMessage()
    {
        return $this->hasMany(ChatMessage::class, 'user_id');
    }
    
    public function chatMessageTo()
    {
        return $this->hasMany(ChatMessage::class, 'user_to');
    }
    
    public function chatLastLog()
    {
        return $this->hasMany(ChatLastLog::class, 'user_id');
    }
    
    public function chatLastLogTo()
    {
        return $this->hasMany(ChatLastLog::class, 'user_to');
    }
    
    public function chatSettings()
    {
        return $this->hasOne(ChatSettings::class, 'user_id')->withDefault();
    }
}
