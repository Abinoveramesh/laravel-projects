<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrentAvailableFoodsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            CREATE view current_available_foods_view
            as
            select fl.id,fl.restaurant_id,fl.category_id, fl.name, fl.image, fl.is_veg, fl.price, fl.description, fl.discount_type, fl.target_amount, fl.offer_amount, fl.packaging_charge,fl.status,
            fla.item_start_time, fla.item_finish_time, fla.item_days
            from food_list fl
            left join food_list_availability fla on fl.id = fla.food_list_id
            where CURRENT_TIME() >= fla.item_start_time
            and CURRENT_TIME() <= fla.item_finish_time  
            and LOWER(DATE_FORMAT(CURRENT_TIME(), '%a')) = IF(fla.item_days = 'allday', LOWER(DATE_FORMAT(CURRENT_TIME(), '%a')) , fla.item_days)
            and fl.status = 1;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
            DROP VIEW IF EXISTS 'current_available_foods_view';
        ");
    }
}
