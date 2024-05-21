<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRefundInitiateDateRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('reference_no');
            $table->string('refund_initiate_date');
            $table->string('refund_processing_date');
            $table->string('refund_completed_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('reference_no');
            $table->dropColumn('refund_initiate_date');
            $table->dropColumn('refund_processing_date');
            $table->dropColumn('refund_completed_date');
        });
    }
}
