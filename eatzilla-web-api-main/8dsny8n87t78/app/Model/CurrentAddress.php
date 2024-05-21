<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class CurrentAddress extends Eloquent
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'currentaddresses';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id','address_id','address_area','address_title','city','current_address','is_from_saved','lat', 'lng'
    ];
}
