<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioImage extends Model
{
    protected $guarded = [];
    protected $appends = ['imageUrl'];

public function getImageUrlAttribute()
    {
        if (
            isset($this->attributes['image_url']) &&
            isset($this->attributes['image_url'][0])
        ) {
            return url($this->attributes['image_url']);
        }
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }


}