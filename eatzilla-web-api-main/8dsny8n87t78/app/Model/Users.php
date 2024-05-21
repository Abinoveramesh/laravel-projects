<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    //
    use SoftDeletes;
    protected $table = 'users';
    
    /**
    * set relationship to food request.
    *
    */
    public function Foodrequest()
    {
        return $this->hasMany('App\Model\Foodrequest','user_id','id');
    }

    /**
    * set relationship to state.
    *
    */
    public function State()
    {
        return $this->belongsTo('App\Model\NewState','new_state_id','id');
    }

    /**
    * set relationship to city.
    *
    */
    public function City()
    {
        return $this->belongsTo('App\Model\State','state_id','id');
    }
}
