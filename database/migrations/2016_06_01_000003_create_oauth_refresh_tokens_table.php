<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthRefreshTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('oauth_refresh_tokens')) {
            Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('access_token_id', 100);
                $table->boolean('revoked');
                $table->dateTime('expires_at')->nullable();
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
        Schema::dropIfExists('oauth_refresh_tokens');
    }
}
