<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-off remediation for installs that ran the 2026_11_10 UUID
 * migration between the day it was merged.
 *
 * Finds every oauth_clients row whose secret decodes as an empty string
 * via Hash::check('', $secret) and sets secret to null. Legitimate
 * confidential clients (real non-empty secrets) are untouched because
 * Hash::check('', $legitHash) always returns false.
 *
 * Fresh installs that only ever run the post-fix UUID migration path
 * never hit this state, so this migration is a no-op there.
 */
return new class extends Migration
{
    public function up(): void
    {
        $hasher = app('hash');

        foreach (DB::table('oauth_clients')->whereNotNull('secret')->cursor() as $client) {
            if ($hasher->check('', $client->secret)) {
                DB::table('oauth_clients')->where('id', $client->id)->update(['secret' => null]);
            }
        }
    }

    public function down(): void
    {
        // no-op. We cannot recover the pre-null empty-string
        // or bcrypt-of-empty value
    }
};
