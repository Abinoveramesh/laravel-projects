<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStatusRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->integer('refund_status')->after('addedby')->nullable()->comment('0-Await, 1-Success, 2-Fail, 3-Decline');
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
            $table->dropColumn('refund_status')->after('addedby')->nullable()->comment('0-Await, 1-Success, 2-Fail, 3-Decline');
        });
    }
}
