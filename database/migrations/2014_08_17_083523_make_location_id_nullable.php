<?php

use Illuminate\Database\Migrations\Migration;

class MakeLocationIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // DB::statement('ALTER TABLE ' . DB::getTablePrefix() . 'users MODIFY column location_id int NULL');
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
