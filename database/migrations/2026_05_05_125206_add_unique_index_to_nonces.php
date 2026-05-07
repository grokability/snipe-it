<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Artisan::call('saml:clear_expired_nonces');
        Schema::table('saml_nonces', function (Blueprint $table) {
            $table->dropIndex(['nonce']);
            $table->unique('nonce');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saml_nonces', function (Blueprint $table) {
            $table->dropUnique(['nonce']);
            $table->index('nonce');
        });
    }
};
