<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-configurable trust lists for MCP OAuth client registration.
 *
 * mcp_trusted_custom_schemes holds native URI schemes (one per line)
 * that bypass the pending-approval queue when a DCR-registered client
 * uses them as a redirect URI. Example line: com.company.appname.
 *
 * mcp_trusted_redirect_domains holds http/https URI prefixes (one per
 * line) that bypass the pending-approval queue. Example line:
 * https://clienta.example.com (any path on that host). The prefix
 * match mirrors laravel/mcp's redirect_domains semantics.
 *
 * Both lists are optional. An admin that leaves them blank still gets
 * the hardcoded safe defaults (loopback redirects, the first-party
 * mobile app scheme) auto-approved. Anything else lands in the pending
 * queue for superuser review.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('mcp_trusted_custom_schemes')->nullable()->after('privacy_policy_link');
            $table->text('mcp_trusted_redirect_domains')->nullable()->after('mcp_trusted_custom_schemes');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['mcp_trusted_custom_schemes', 'mcp_trusted_redirect_domains']);
        });
    }
};
