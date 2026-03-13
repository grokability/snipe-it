<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixDefaultsAccessories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function (Blueprint $table) {
            $table->string('order_number')->nullable()->change();
        });

        Schema::table('consumables', function (Blueprint $table) {
            $table->string('order_number')->nullable()->change();
        });

        //
        // DB::statement('ALTER TABLE `'.DB::getTablePrefix().'accessories` MODIFY `order_number` varchar(255) DEFAULT NULL;');
    //
        // DB::statement('ALTER TABLE `'.DB::getTablePrefix().'consumables` MODIFY `order_number` varchar(255) DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('ALTER TABLE `'.DB::getTablePrefix().'accessories` MODIFY `order_number` varchar(255);');
        DB::statement('ALTER TABLE `'.DB::getTablePrefix().'consumables` MODIFY `order_number` varchar(255);');
    }
}
