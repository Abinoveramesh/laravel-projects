<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNewStateIdColumnInAddZoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('add_zone', function (Blueprint $table) {
            $table->integer('new_state_id')->default(0)->after('country_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('add_zone', function (Blueprint $table) {
            $table->dropColumn('new_state_id');
        });
    }
}
