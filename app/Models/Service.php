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

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'service_category');
    }
}