<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMinStockToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function (Blueprint $table) {
            $table->integer('min_amt')->nullable()->default(null);
        });
        Schema::table('consumables', function (Blueprint $table) {
            $table->integer('min_amt')->nullable()->default(null);
        });
        Schema::table('components', function (Blueprint $table) {
            $table->integer('min_amt')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accessories', function ($table) {
            $table->dropColumn('min_amt');
        });
        Schema::table('components', function ($table) {
            $table->dropColumn('min_amt');
        });
        Schema::table('consumables', function ($table) {
            $table->dropColumn('min_amt');
        });
    }
}
