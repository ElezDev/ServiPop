<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];
    

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
    
    public function portfolioImages()
    {
        return $this->hasMany(PortfolioImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'service_category');
    }
 /**
     * Obtiene los favoritos asociados a este servicio
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Obtiene los usuarios que marcaron este servicio como favorito
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')
                   ->withTimestamps();
    }
    // En app/Models/Service.php
    public function serviceProviderUser()
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }
    
}