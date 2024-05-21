<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ViewCurrentAvailableFoods extends Model
{
    //
    protected $table = 'current_available_foods_view';

    /**
    * set relationship to Choice category 
    *
    */

    public function Choice_category()
    {
        return $this->hasMany('App\Model\Choice_category','food_id','id');
    }

    /**
    * set relationship to category.
    *
    */
    public function Category()
    {
        return $this->belongsToMany('App\Model\Category','foodlist_category','foodlist_id','category_id');
    }
}
