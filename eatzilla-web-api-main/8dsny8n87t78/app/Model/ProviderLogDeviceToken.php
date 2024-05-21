<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class ProviderLogDeviceToken extends Eloquent
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'providerlogdevicetokens';
    protected $primaryKey = 'provider_id';
    
    protected $fillable = [
        'provider_id' , 'device_token' , 'status'
    ];
}