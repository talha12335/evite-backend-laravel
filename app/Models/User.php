<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role_id',
        'location_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }



    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function isGlobalAdmin()
    {
        return (int) $this->role_id === 1;
    }

    public function isStudioAdmin()
    {
        return (int) $this->role_id === 2;
    }

    public function isSupport()
    {
        return (int) $this->role_id === 3;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
