<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Foodlist extends Model
{
    //
    protected $table = 'food_list';

    protected $with = ['Category', 'Menu', 'Add_ons' , 'Choice_category'];

    /**
    * set relationship to Add_ons.
    *
    */
    public function Add_ons()
    {
        return $this->belongsToMany('App\Model\Add_ons','foodlist_addons', 'foodlist_id', 'addons_id');
    }


    /**
    * set relationship to FoodQuantity.
    *
    */
    public function FoodQuantity()
    {
        return $this->belongsToMany('App\Model\FoodQuantity', 'foodlist_foodquantity', 'foodlist_id', 'foodquantity_id')->withPivot("price",'is_default');
    }



    /**
    * set relationship to category.
    *
    */
    public function Category()
    {
        return $this->belongsToMany('App\Model\Category','foodlist_category','foodlist_id','category_id');
    }


    /**
    * set relationship to menu.
    *
    */
    public function Menu()
    {
        return $this->belongsTo('App\Model\Menu','menu_id','id');
    }


    /**
    * set relationship to menu.
    *
    */
    public function Requestdetail()
    {
        return $this->belongsTo('App\Model\Requestdetail','food_id','id');
    }


     /**
    * set relationship to restaurant.
    *
    */
    public function Restaurants()
    {
        return $this->belongsTo('App\Model\Restaurants','restaurant_id','id');
    }

    /**
    * set relationship to Choice category 
    *
    */

    public function Choice_category()
    {
        return $this->hasMany('App\Model\Choice_category','food_id','id');
    }

}
