<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateIsImportColumnInFoodListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('food_list'))
        {
            Schema::table('food_list', function (Blueprint $table) {
                $table->tinyInteger('is_imported_image')->default(0)->after('offer_amount');
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
        if (Schema::hasTable('food_list'))
        {
            Schema::dropIfExists('food_list');
        }
    }
}
