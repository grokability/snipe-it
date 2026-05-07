<?php

use Illuminate\Database\Migrations\Migration;

class AddSuppliers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('address', 50)->nullable()->default(null);
            $table->string('address2', 50)->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state', 2)->nullable()->default(null);
            $table->string('country', 2)->nullable()->default(null);
            $table->string('phone', 20)->nullable()->default(null);
            $table->string('fax', 20)->nullable()->default(null);
            $table->string('email', 150)->nullable()->default(null);
            $table->string('contact', 100)->nullable()->default(null);
            $table->string('notes')->nullable()->default(null);
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->softDeletes();
            $table->engine = 'InnoDB';
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
        Schema::drop('suppliers');
    }
}
