<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayoutSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->integer('rider_payout_first_2_KM_charge');
            $table->integer('rider_payout_remaining_each_KM_charge');
            $table->integer('rider_payout_waiting_time_charge');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::drop('payout_settings');
    }
}
