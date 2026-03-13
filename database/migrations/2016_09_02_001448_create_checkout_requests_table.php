<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCheckoutRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkout_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('requestable_id');
            $table->string('requestable_type');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'requestable_id', 'requestable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checkout_requests');
    }
}
