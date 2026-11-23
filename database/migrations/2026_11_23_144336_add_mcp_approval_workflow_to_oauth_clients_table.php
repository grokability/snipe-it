<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approval workflow for OAuth clients, primarily for the MCP connector
 * self-registration (Dynamic Client Registration) flow.
 *
 * Every existing row gets status=approved so pre-upgrade clients keep
 * working untouched. New DCR registrations start pending unless their
 * redirect URI is in a narrow safe-to-auto-approve set (loopback, the
 * first-party mobile app scheme, or an admin-configured trusted entry).
 * Pending clients can't complete the authorize or token exchange, so
 * an attacker self-registering a client with an off-box HTTPS redirect
 * can't do anything with it until a superuser approves it in the admin
 * UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            // approved: fully usable. pending: DCR-registered, awaiting
            // superuser review. rejected: superuser denied, kept for
            // audit until the pending-client pruner deletes it.
            $table->string('status', 20)->default('approved')->after('revoked')->index();
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->uuid('approved_by_id')->nullable()->after('approved_at');
            $table->string('registered_ip', 45)->nullable()->after('approved_by_id');
            $table->string('rejected_reason', 255)->nullable()->after('registered_ip');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'approved_at', 'approved_by_id', 'registered_ip', 'rejected_reason']);
        });
    }
};
