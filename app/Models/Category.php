<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    protected $table = "categories";
    protected $appends = ['image'];
   

    public function getImageAttribute()
    {
        if (
            isset($this->attributes['image']) &&
            isset($this->attributes['image'][0])
        ) {
            return url($this->attributes['image']);
        }
      
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_category');
    }
}