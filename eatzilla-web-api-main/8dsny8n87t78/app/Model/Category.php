<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'category';

    protected $fillable = ['category_name', 'status', 'created_at', 'updated_at' ];

    /**
     * set relationship to foodlist.
     */
    public function Foodlist()
    {
        return $this->belongsToMany('App\Model\Foodlist','foodlist_category','foodlist_id','category_id');
    }

    /**
     * set relationship to category.
     */
    public function Restaurants()
    {
        return $this->belongsTo('App\Model\Restaurants','restaurant_id','id');
    }
}
