<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderRejectedDriver extends Model
{
    protected $table = 'order_rejected_driver';
    protected $fillable = [
        'driver_id' , 'order_id' , 'restaurant_id' , 'status'
    ];
}
