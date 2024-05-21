<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Zone_geofencing extends Model
{
    //table name
    protected $table = 'zone_geofencing';

    //protected $with = ['zone'];

    /**
     * set relationship to city model.
     */
    public function zone()
    {
        return $this->belongsTo('App\Model\Addzone', 'zone_id','id');
    }
}
