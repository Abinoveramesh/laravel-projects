<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class AvailableProviders extends Eloquent
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'availableproviders';
    protected $primaryKey = 'provider_id';
    
    protected $fillable = [
        'provider_id','lat', 'lng', 'updated_at' , 'status'
    ];
}
