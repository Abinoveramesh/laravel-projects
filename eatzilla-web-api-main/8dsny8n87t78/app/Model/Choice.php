<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $table = 'choice';

    /**
     * set relationship to choice_category model.
     */
    public function Choice_category()
    {
        return $this->belongsTo('App\Model\Choice_category', 'choice_category_id','id');
    }
}
