<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /**
         * This had to be changed back in time :(
         *
         * This migration was named stupidly in 2015, and we're now seeing weird namespace errors
         * popping up in 2023, likely due to different laravel or PHP versions. This migration will run again on
         * more updated systems, since the name of the migration has changed and therefore will look "new" to the
         * migrations table/migration system, which is why we need to check if the
         * field already exists. Thanks, I hate it. - snipe
         */
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'image')) {
                $table->text('image')->after('notes')->nullable()->default(null);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

       /**
        * I'm leaving this one out, since it could destroy data that was already long-existing. 
        */
    }
}
