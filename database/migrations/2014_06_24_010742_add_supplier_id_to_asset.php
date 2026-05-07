<?php

use Illuminate\Database\Migrations\Migration;

class AddSupplierIdToAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function ($table) {
            $table->integer('supplier_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function ($table) {
            $table->dropColumn('supplier_id');
        });
    }
}
