<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixDefaultForUserNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement('ALTER TABLE `'.DB::getTablePrefix().'users` MODIFY `notes` varchar(255) DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        // DB::statement('ALTER TABLE `'.DB::getTablePrefix().'users` MODIFY `notes` varchar(255);');
    }
}
