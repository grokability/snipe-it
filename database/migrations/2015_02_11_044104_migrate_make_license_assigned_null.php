<?php

use Illuminate\Database\Migrations\Migration;

class MigrateMakeLicenseAssignedNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::update('update '.DB::getTablePrefix().'license_seats SET assigned_to=NULL where assigned_to=0');
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
