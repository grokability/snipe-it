<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckoutAcceptancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('checkout_acceptances')) {
            Schema::create('checkout_acceptances', function (Blueprint $table) {
                $table->increments('id');

                $table->morphs('checkoutable');
                $table->integer('assigned_to_id')->nullable();

                $table->string('signature_filename')->nullable();

                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('declined_at')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('checkout_acceptances')) {
            Schema::dropIfExists('checkout_acceptances');
        }
    }
}
