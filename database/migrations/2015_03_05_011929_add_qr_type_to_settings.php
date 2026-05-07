<?php

use Illuminate\Database\Migrations\Migration;

class AddQrTypeToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('settings', function ($table) {
            $table->string('barcode_type')->nullable()->default('QRCODE');
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
        Schema::table('settings', function ($table) {
            $table->dropColumn('barcode_type');
        });
    }
}
