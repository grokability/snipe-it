<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DenormCountersOnAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->integer('checkin_counter')->default(0);
            $table->integer('checkout_counter')->default(0);
            $table->integer('requests_counter')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('checkin_counter');
            $table->dropColumn('checkout_counter');
            $table->dropColumn('requests_counter');
        });
    }
}
