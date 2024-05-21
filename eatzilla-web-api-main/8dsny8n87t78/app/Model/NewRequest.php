<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class NewRequest extends Eloquent
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'newrequests';
    protected $primaryKey = 'request_id';
    
    protected $fillable = [
        'provider_id', 'request_id', 'status' , 'user_id'
    ];
}

