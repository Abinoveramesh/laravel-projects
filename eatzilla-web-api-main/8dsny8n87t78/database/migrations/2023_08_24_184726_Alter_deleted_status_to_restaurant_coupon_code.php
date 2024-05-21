<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDeletedStatusToRestaurantCouponCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurant_coupon_code', function (Blueprint $table) {
            $table->integer('deleted_status')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurant_coupon_code', function (Blueprint $table) {
            $table->dropColumn('deleted_status')->after('status')->nullable();
        });
    }
}
