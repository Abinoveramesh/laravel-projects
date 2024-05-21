<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RestaurantTimer extends Model
{
    //
    protected $table = 'restaurant_opening_hours';


    /**
    * set relationship to restaurant.
    *
    */
    public function Restaurants()
    {
        return $this->belongsTo('App\Model\Restaurants','restaurant_id','id');
    }


}
