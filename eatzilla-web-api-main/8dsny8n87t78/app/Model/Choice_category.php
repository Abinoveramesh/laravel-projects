<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Choice_category extends Model
{
    protected $table = 'choice_category';
    protected $with = ['choice'];

    /**
     * set relationship to area.
     */
    public function Choice()
    {
        return $this->hasMany('App\Model\Choice','choice_category_id','id');
    }

    /**
     * set relationship to foodlist model.
     */
    public function Foodlist()
    {
        return $this->belongsTo('App\Model\Foodlist', 'food_id','id');
    }


}
