<?php

use Illuminate\Database\Migrations\Migration;

class MakeAssetLogCheckedoutToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // DB::statement('ALTER TABLE ' . DB::getTablePrefix() . 'asset_logs MODIFY column checkedout_to int(11) NULL');
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
