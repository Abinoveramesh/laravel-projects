<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryInstruction extends Model
{
    //
    use SoftDeletes;
    protected $table = 'delivery_instruction';
}
