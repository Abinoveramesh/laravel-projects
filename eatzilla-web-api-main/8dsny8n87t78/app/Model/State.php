<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    //table name
    protected $table = 'state';

    protected $with = ['City','NewState'];

    /**
     * set relationship to country.
     */
    public function Country()
    {
        return $this->belongsTo('App\Model\Country', 'country_id','id');
    }

    /**
     * set relationship to new state.
     */
    public function NewState()
    {
        return $this->belongsTo('App\Model\NewState', 'new_state_id','id');
    }

    /**
     * set relationship to city.
     */
    public function City()
    {
        return $this->hasMany('App\Model\Addcity','state_id','id');
    }

    /**
     * set relationship to city.
     */
    public function Zone()
    {
        return $this->hasMany('App\Model\Addzone','state_id','id');
    }

     /**
     * set relationship to delivery partners detail.
     */
    public function Deliverypartner_detail()
    {
        return $this->hasMany('App\Model\Deliverypartner_detail','state_province','id');
    }

     /**
     * set relationship to delivery partners.
     */
    public function Deliverypartners()
    {
        return $this->hasMany('App\Model\Deliverypartners','city_id','id');
    }
}
