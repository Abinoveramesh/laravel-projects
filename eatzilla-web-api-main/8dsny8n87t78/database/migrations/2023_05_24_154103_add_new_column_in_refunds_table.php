<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnInRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('user_phone_number',20)->after('request_id');
            $table->string('user_name',20)->after('request_id');
            $table->string('order_id',20)->after('request_id');

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
            $table->dropColumn('user_phone_number');
            $table->dropColumn('user_name');
            $table->dropColumn('order_id');
        });
    }
}
