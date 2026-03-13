<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixDefaultPurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('purchase_order')->nullable()->change();
        });

        // DB::statement('ALTER TABLE `'.DB::getTablePrefix().'licenses` MODIFY `purchase_order` varchar(255) DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        // DB::statement('ALTER TABLE `'.DB::getTablePrefix().'licenses` MODIFY `purchase_order` varchar(255);');
    }
}
