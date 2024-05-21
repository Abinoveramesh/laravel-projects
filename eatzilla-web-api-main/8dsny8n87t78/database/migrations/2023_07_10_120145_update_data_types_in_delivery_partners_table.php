<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDataTypesInDeliveryPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_partners', function (Blueprint $table) {
            $table->decimal('partner_commision',11,2)->default(0.00)->change();
            $table->decimal('total_earnings',11,2)->default(0.00)->change();
            $table->decimal('pending_payout',11,2)->default(0.00)->change();
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