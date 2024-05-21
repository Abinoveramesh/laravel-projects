<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRiderVendorDetailsToDeliveryPartnerDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_partner_details', function (Blueprint $table) {
            $table->string('account_type')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('rider_vendor_reference_no')->nullable();
            $table->string('rider_vendor_id')->nullable();
            $table->string('rider_vendor_reg_id')->nullable();
            $table->string('rider_vendor_virtual_accno')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_partner_details', function (Blueprint $table) {
            $table->dropColumn('account_type');
            $table->dropColumn('pin_code');
            $table->dropColumn('rider_vendor_reference_no');
            $table->dropColumn('rider_vendor_id');
            $table->dropColumn('rider_vendor_reg_id');
            $table->dropColumn('rider_vendor_virtual_accno');
        });
    }
}
