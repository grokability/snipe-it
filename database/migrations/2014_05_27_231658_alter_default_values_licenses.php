<?php

use Illuminate\Database\Migrations\Migration;

class AlterDefaultValuesLicenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // DB::statement('ALTER TABLE ' . DB::getTablePrefix() . 'license_seats MODIFY column notes text NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
