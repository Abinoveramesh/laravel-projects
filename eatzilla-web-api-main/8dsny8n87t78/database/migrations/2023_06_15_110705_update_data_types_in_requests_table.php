<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDataTypesInRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->decimal('offer_discount',11,2)->default(0.00)->change();
            $table->decimal('restaurant_discount',11,2)->default(0.00)->change();
            $table->decimal('restaurant_packaging_charge',11,2)->default(0.00)->change();
            $table->decimal('tax',11,2)->default(0.00)->change();
            $table->decimal('delivery_charge',11,2)->default(0.00)->change();
            $table->decimal('item_total',11,2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
