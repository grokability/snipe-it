<?php

use Illuminate\Database\Migrations\Migration;

class AddZipToSupplier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function ($table) {
            $table->string('zip', 10)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function ($table) {
            $table->dropColumn('zip');
        });
    }
}
