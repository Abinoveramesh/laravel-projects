<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Addzone extends Model
{
    //
    protected $table = 'add_zone';

    protected $with = ['zone_geofencing', 'Restaurants'];

    /**
     * set relationship to city_geofencing.
     */
    public function zone_geofencing()
    {
        return $this->hasOne('App\Model\Zone_geofencing','zone_id','id');
    }

    /**
     * set relationship to area.
     */
    public function Area()
    {
        return $this->hasMany('App\Model\Addarea','add_city_id','id');
    }

    /**
     * set relationship to city.
     */
    public function Restaurants()
    {
        return $this->belongsToMany('App\Model\Restaurants', 'restaurant_city', 'restaurant_id', 'city_id');
    }

    /**
     * set relationship to city.
     */
    public function Deliverypartner_detail()
    {
        return $this->hasMany('App\Model\Deliverypartner_detail','city','id');
    }


    /**
     * set relationship to Country.
     */
    public function Country()
    {
        return $this->belongsTo('App\Model\Country', 'country_id','id');
    }

    /**
     * set relationship to NewState.
     */
    public function NewState()
    {
        return $this->belongsTo('App\Model\NewState', 'new_state_id','id');
    }

    /**
     * set relationship to state.
     */
    public function State()
    {
        return $this->belongsTo('App\Model\State', 'state_id','id');
    }

    /**
     * set relationship to state.
     */
    public function Deliverypartners()
    {
        return $this->hasMany('App\Model\Deliverypartners','zone_id','id');
    }
    
}
