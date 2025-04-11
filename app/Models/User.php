<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    protected $guard_name = 'api';

    protected $appends = ['avatar'];

    public function getAvatarAttribute()
        {
            if (
                isset($this->attributes['avatar']) &&
                isset($this->attributes['avatar'][0])
            ) {
                return url($this->attributes['avatar']);
            }
        }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class);
    }
    

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Obtiene los servicios favoritos (relación a través del modelo Favorite)
     */
    public function favoriteServices()
    {
        return $this->belongsToMany(Service::class, 'favorites')
                    ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }
}
