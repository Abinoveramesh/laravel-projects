<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cuisines extends Model
{
    //
    protected $table = 'cuisines';
    protected $fillable = ['name'];

    /**
    * set relationship to restaurants.
    *
    */

    protected function getNameAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function Restaurants()
    {
        return $this->belongsToMany('App\Model\Restaurants', 'restaurant_cuisines', 'restaurant_id', 'cuisine_id');
    }
}
