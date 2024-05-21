<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCommissionColumnsDataTypesInRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->decimal('loyalty_discount',11,2)->default(0.00)->change();
            $table->decimal('bill_amount',11,2)->default(0.00)->change();
            $table->decimal('admin_commision',11,2)->default(0.00)->change();
            $table->decimal('restaurant_commision',11,2)->default(0.00)->change();
            $table->decimal('delivery_boy_commision',11,2)->default(0.00)->change();
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