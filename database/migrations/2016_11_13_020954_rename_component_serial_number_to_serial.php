<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameComponentSerialNumberToSerial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function ($table) {
            $table->renameColumn('serial_number', 'serial');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('components', function ($table) {
            $table->renameColumn('serial', 'serial_number');
        });
    }
}
