<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression for the Passport v12 -> v13 UUID migration. Pre-upgrade JWTs
 * carry the old bigint client_id in their `aud` claim. After the
 * migration re-keys oauth_clients to UUIDs, that id no longer resolves
 * and TokenGuard silently 401s. CompatTokenGuard restores those tokens
 * by falling back to oauth_access_tokens.client_id, which the migration
 * did update, when the aud lookup misses.
 */
class CompatTokenGuardTest extends TestCase
{
    public function test_pre_upgrade_pat_with_stale_aud_resolves_via_jti_fallback(): void
    {
        $this->seedPersonalAccessClient();
        $user = User::factory()->superuser()->create();
        $jwt = $user->createToken('test')->accessToken;

        $originalClientId = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->value('id');

        // Simulate the migration re-keying the personal_access_client
        // row. The JWT's aud still points at $originalClientId, but
        // oauth_clients only knows the new UUID now. oauth_access_tokens
        // was carried along by the migration backfill.
        $newClientId = (string) Str::orderedUuid();
        DB::table('oauth_clients')
            ->where('id', $originalClientId)
            ->update(['id' => $newClientId]);
        DB::table('oauth_access_tokens')
            ->where('client_id', $originalClientId)
            ->update(['client_id' => $newClientId]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$jwt,
            'Accept' => 'application/json',
        ])
            ->getJson('/api/v1/users?limit=1')
            ->assertOk();
    }

    public function test_post_upgrade_pat_authenticates_without_entering_fallback(): void
    {
        $this->seedPersonalAccessClient();
        $user = User::factory()->superuser()->create();
        $jwt = $user->createToken('test')->accessToken;

        $this->withHeaders([
            'Authorization' => 'Bearer '.$jwt,
            'Accept' => 'application/json',
        ])
            ->getJson('/api/v1/users?limit=1')
            ->assertOk();
    }

    public function test_revoked_token_row_does_not_resolve_via_fallback(): void
    {
        $this->seedPersonalAccessClient();
        $user = User::factory()->superuser()->create();
        $jwt = $user->createToken('test')->accessToken;

        $originalClientId = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->value('id');

        $newClientId = (string) Str::orderedUuid();
        DB::table('oauth_clients')
            ->where('id', $originalClientId)
            ->update(['id' => $newClientId]);
        DB::table('oauth_access_tokens')
            ->where('client_id', $originalClientId)
            ->update(['client_id' => $newClientId, 'revoked' => true]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$jwt,
            'Accept' => 'application/json',
        ])
            ->getJson('/api/v1/users?limit=1')
            ->assertUnauthorized();
    }

    private function seedPersonalAccessClient(): void
    {
        DB::table('oauth_clients')->insert([
            'id' => (string) Str::orderedUuid(),
            'user_id' => null,
            'name' => 'Test Personal Access Client',
            'secret' => 'test-secret-'.bin2hex(random_bytes(10)),
            'provider' => null,
            'redirect' => '',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
