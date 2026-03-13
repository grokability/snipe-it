<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeLicenceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // $prefix = DB::getTablePrefix();
        //  DB::statement('ALTER TABLE '.$prefix.'licenses MODIFY COLUMN serial TEXT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        //  $prefix = DB::getTablePrefix();
        //  DB::statement('ALTER TABLE '.$prefix.'licenses MODIFY COLUMN serial VARCHAR(255)');
    }
}
