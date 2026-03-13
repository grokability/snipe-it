<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSamlNonceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('saml_nonces') ) {
            Schema::create('saml_nonces', function (Blueprint $table) {
                $table->id();
                $table->string('nonce')->index();
                $table->datetime('not_valid_after')->index();
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
        Schema::dropIfExists('saml_nonces');
    }
}
