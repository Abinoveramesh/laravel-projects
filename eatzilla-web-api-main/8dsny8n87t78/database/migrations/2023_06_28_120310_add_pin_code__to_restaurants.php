<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPinCodeToRestaurants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->integer('pin_code')->nullable();
            $table->string('vendor_reference_no')->nullable();
            $table->string('vendor_id')->nullable();
            $table->string('vendor_reg_id')->nullable();
            $table->string('vendor_virtual_accno')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('pin_code');
            $table->dropColumn('vendor_reference_no');
            $table->dropColumn('vendor_id');
            $table->dropColumn('vendor_reg_id');
            $table->dropColumn('vendor_virtual_accno');
        });
    }
}
