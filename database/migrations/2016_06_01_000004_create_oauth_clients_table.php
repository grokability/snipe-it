<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('oauth_clients')) {
            Schema::create('oauth_clients', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('name');
                $table->string('secret', 100)->nullable();
                $table->text('redirect');
                $table->boolean('personal_access_client');
                $table->boolean('password_client');
                $table->boolean('revoked');
                $table->timestamps();
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
        Schema::dropIfExists('oauth_clients');
    }
}
