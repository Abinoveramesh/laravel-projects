<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deliverypartners extends Model
{
    //
    use SoftDeletes;
    protected $table = 'delivery_partners';
    protected $fillable = ['name'];
    protected $with = ['Deliverypartner_detail','Vehicle'];

    /**
    * set relationship to delivery boy.
    *
    */
    protected function getNameAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function Foodrequest()
    {
        return $this->hasMany('App\Model\Foodrequest','delivery_boy_id','id');
    }

    /**
    * set relationship to delivery boy detail.
    *
    */
    public function Deliverypartner_detail()
    {

        return $this->hasOne('App\Model\Deliverypartner_detail','delivery_partners_id','id');

    }

    /**
    * set relationship to delivery boy detail.
    *
    */
    public function DriverPayoutHistory()
    {
        return $this->hasMany('App\Model\DriverPayoutHistory','delivery_boy_id','id');
    }

    /**
     * set relationship to Vehicle.
     */
    public function Vehicle()
    {
        return $this->hasOne('App\Model\Vehicle','delivery_partners_id','id');
    }

     /**
     * set relationship to state.
     */
    public function Addzone()
    {
        return $this->belongsTo('App\Model\Addzone','zone_id','id');
    }

    /**
    * set relationship to city.
    *
    */
    public function State()
    {
        return $this->belongsTo('App\Model\State','city_id','id');
    }
}
