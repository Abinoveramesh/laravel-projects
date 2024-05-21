<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPayoutTransIdToDriverPayoutHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_payout_history', function (Blueprint $table) {
            $table->string('merchant_ref_no')->nullable();
            $table->string('trans_status')->nullable();
            $table->string('payout_trans_id')->nullable();
            $table->timestamp('trans_datetime')->nullable();
            $table->string('failure_remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_payout_history', function (Blueprint $table) {
            $table->dropColumn('merchant_ref_no');
            $table->dropColumn('trans_status');
            $table->dropColumn('payout_trans_id');
            $table->dropColumn('trans_datetime');
            $table->dropColumn('failure_remark');
        });
    }
}
