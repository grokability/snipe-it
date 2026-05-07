<?php

use Illuminate\Database\Migrations\Migration;

class AddRequestableToAssetModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('models', function ($table) {
            $table->tinyInteger('requestable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('models', function ($table) {
            $table->dropColumn('requestable');
        });
    }
}
