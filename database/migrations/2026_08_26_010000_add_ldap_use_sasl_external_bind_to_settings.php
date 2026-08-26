<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opt-in toggle for LDAP SASL EXTERNAL bind. When on, bindAdminToLdap
 * uses ldap_sasl_bind() with the EXTERNAL mechanism (authenticates the
 * caller via the client TLS cert already configured via
 * ldap_client_tls_cert / ldap_client_tls_key) instead of a
 * username/password simple bind. Used by installs that authenticate to
 * their directory with a client cert, notably Google Workspace LDAP
 * (see GH #19518).
 *
 * Off by default so every existing install keeps the simple / anonymous
 * bind behavior it had before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('ldap_use_sasl_external_bind')->default(false)->after('ldap_client_tls_cert');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('ldap_use_sasl_external_bind');
        });
    }
};
