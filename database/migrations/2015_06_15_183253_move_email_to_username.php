<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MoveEmailToUsername extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::update('UPDATE '.DB::getTablePrefix().'users SET username=email');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        //DB::statement('ALTER TABLE `users` MODIFY `notes` varchar(255);');
    }
}
