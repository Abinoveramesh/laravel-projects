<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRequestIdToCcAvenueTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cc_avenue_transactions', function (Blueprint $table) {
            $table->string('order_status')->nullable()->after('payment_mode');
            $table->string('card_name')->nullable()->after('order_status');
            $table->timestamp('trans_date')->nullable()->after('card_name');
            $table->string('request_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cc_avenue_transactions', function (Blueprint $table) {
            $table->dropColumn('order_status');
            $table->dropColumn('card_name');
            $table->dropColumn('trans_date');
            $table->dropColumn('request_id');
        });
    }
}
