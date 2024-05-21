<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportFoodListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('import_food_list'))
        {
            Schema::create('import_food_list', function (Blueprint $table) {
                $table->increments('id')->index();
                $table->text('restaurant_id')->nullable();
                $table->text('category_name')->nullable();
                $table->text('food_title')->nullable();
                $table->text('food_price')->nullable();
                $table->text('food_type')->nullable();
                $table->text('cuisines')->nullable();
                $table->text('packaging_charge')->nullable();
                $table->text('bc')->nullable();
                $table->text('bs')->nullable();
                $table->text('food_prepartion_time')->nullable();
                $table->text('food_description')->nullable();
                $table->text('food_image')->nullable();
                $table->text('selling_days')->nullable();
                $table->text('selling_days_start_time')->nullable();
                $table->text('selling_days_finish_time')->nullable();
                $table->text('choice_category_name')->nullable();
                $table->text('min')->nullable();
                $table->text('max')->nullable();
                $table->text('choice_title')->nullable();
                $table->text('choice_price')->nullable(); 
                $table->integer('status')->default(0);                          
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_food_list');
    }
}
