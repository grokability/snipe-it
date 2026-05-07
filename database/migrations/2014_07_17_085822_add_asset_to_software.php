<?php

use Illuminate\Database\Migrations\Migration;

class AddAssetToSoftware extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('license_seats', function ($table) {
            $table->integer('asset_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('license_seats', function ($table) {
            $table->dropColumn('asset_id');
        });
    }
}
