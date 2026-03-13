<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConsumables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('consumables', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);
            $table->integer('location_id')->nullable()->default(null);
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('qty')->default(0);
            $table->boolean('requestable')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });

        Schema::table('asset_logs', function ($table) {
            $table->integer('consumable_id')->nullable()->default(null);
        });

        Schema::create('consumables_users', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('consumable_id')->nullable()->default(null);
            $table->integer('assigned_to')->nullable()->default(null);
            $table->timestamps();
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
        Schema::drop('consumables');

        Schema::table('asset_logs', function ($table) {
            $table->dropColumn('consumable_id');
        });

        Schema::drop('consumables_users');
    }
}
