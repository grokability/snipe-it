<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAccessoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('accessories', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('qty')->default(0);
            $table->boolean('requestable')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });

        Schema::table('asset_logs', function ($table) {
            $table->integer('accessory_id')->nullable()->default(null);
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
        Schema::drop('accessories');

        Schema::table('asset_logs', function ($table) {
            $table->dropColumn('accessory_id');
        });
    }
}
