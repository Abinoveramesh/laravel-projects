<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //
    protected $table = 'offers_banner';

    /**
     * set relationship to restaurant.
     */
    public function Restaurants()
    {
        return $this->belongsTo('App\Model\Restaurants','restaurant_id','id');
    }

}
