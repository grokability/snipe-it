<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('oidc_issuer', 191)->nullable();
            $table->string('oidc_subject', 191)->nullable();
            $table->index(['oidc_issuer', 'oidc_subject']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['oidc_issuer', 'oidc_subject']);
            $table->dropColumn(['oidc_issuer', 'oidc_subject']);
        });
    }
};
