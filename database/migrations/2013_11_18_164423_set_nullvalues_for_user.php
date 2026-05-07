<?php

use Illuminate\Database\Migrations\Migration;

class SetNullvaluesForUser extends Migration
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
        //     DB::statement('ALTER TABLE '.$prefix.'users MODIFY phone varchar(20) null');
        //     DB::statement('ALTER TABLE '.$prefix.'users MODIFY jobtitle varchar(50) null');
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
