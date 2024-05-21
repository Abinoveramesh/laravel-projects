<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFoodImageNameToImportFoodListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_food_list', function (Blueprint $table) {
            $table->string('food_image_name')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_food_list', function (Blueprint $table) {
            $table->dropColumn('food_image_name')->nullable()->after('status');
        });
    }
}
