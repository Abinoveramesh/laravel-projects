<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOfferRelatedColumnInRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->tinyInteger('offer_type')->nullable()->after('offer_amount')->default(1);
            $table->decimal('offer_value',11,2)->after('offer_amount')->default(0.00)->nullable();
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
            $table->dropColumn('offer_type');
            $table->dropColumn('offer_value');
        });
    }
}
