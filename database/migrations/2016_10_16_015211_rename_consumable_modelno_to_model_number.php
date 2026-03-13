<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameConsumableModelnoToModelNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumables', function (Blueprint $table) {
            //
            $table->renameColumn('model_no', 'model_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumables', function (Blueprint $table) {
            //
            $table->renameColumn('model_number', 'model_no');
        });
    }
}
