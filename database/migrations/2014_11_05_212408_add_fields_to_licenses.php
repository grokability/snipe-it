<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsToLicenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licenses', function ($table) {
            $table->integer('supplier_id')->nullable()->default(null);
            $table->date('expiration_date')->nullable();
            $table->string('purchase_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenses', function ($table) {
            $table->dropColumn('supplier_id');
            $table->dropColumn('expiration_date');
            $table->dropColumn('purchase_order');
        });
    }
}
