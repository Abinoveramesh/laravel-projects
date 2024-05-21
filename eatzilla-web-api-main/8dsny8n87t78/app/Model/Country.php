<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    //table name
    protected $table = 'country';

    protected $with = ['State'];

    /**
     * set relationship to state.
     */
    public function State()
    {
        return $this->hasMany('App\Model\State','country_id','id');
    }

    /**
     * set relationship to state.
     */
    public function City()
    {
        return $this->hasMany('App\Model\Addcity','country_id','id');
    }

    /**
     * set relationship to state.
     */
    public function Zone()
    {
        return $this->hasMany('App\Model\Addzone','country_id','id');
    }

     /**
     * set relationship to delivery partners detail.
     */
    public function Deliverypartner_detail()
    {
        return $this->hasMany('App\Model\Deliverypartner_detail','country','id');
    }

}
