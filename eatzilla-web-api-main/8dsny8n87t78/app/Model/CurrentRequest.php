<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class CurrentRequest extends Eloquent
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'currentrequests';
    protected $primaryKey = 'request_id';
    
    protected $fillable = [
        'lat', 'lng','provider_id', 'request_id', 'status' , 'user_id'
    ];
}
