<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

/**
 * Regression for the Passport /oauth/clients* authorization bypass.
 * Passport's default client-management routes are auto-registered by
 * its service provider with only web + auth:web middleware, so any
 * session-authenticated user could POST /oauth/clients with an
 * attacker-controlled redirect URI. If a phished admin then approved
 * the resulting consent screen, the attacker exchanged the auth code
 * for a long-lived bearer token carrying the admin's full API
 * permissions.
 *
 * The fix registers overriding routes at the same URIs in
 * routes/web.php (loaded before Passport's package service provider
 * boots, so FIFO route resolution picks ours) gated with
 * can:superuser to match the /admin/oauth surfaces Snipe-IT
 * already treats as superuser-only.
 */
class PassportOAuthClientGateTest extends TestCase
{
    public function test_normal_user_cannot_list_oauth_clients(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/oauth/clients')
            ->assertForbidden();
    }

    public function test_normal_user_cannot_create_oauth_client(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/oauth/clients', [
                'name' => 'attacker-client',
                'redirect' => 'https://attacker.example/callback',
            ])
            ->assertForbidden();
    }

    public function test_normal_user_cannot_update_oauth_client(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/oauth/clients/any-client-id', [
                'name' => 'renamed',
                'redirect' => 'https://attacker.example/callback',
            ])
            ->assertForbidden();
    }

    public function test_normal_user_cannot_delete_oauth_client(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/oauth/clients/any-client-id')
            ->assertForbidden();
    }

    public function test_admin_without_superuser_cannot_manage_oauth_clients(): void
    {
        // Snipe-IT's /admin/oauth surfaces are gated on `superuser`,
        // not `admin`. Admins get 403 too, matching the intent.
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/oauth/clients', [
                'name' => 'admin-attempt',
                'redirect' => 'https://example.test/callback',
            ])
            ->assertForbidden();
    }

    public function test_superuser_passes_the_gate(): void
    {
        // Superusers bypass every gate via AuthServiceProvider's
        // Gate::before hook, so they must reach the controller. The
        // Passport ClientController may fail later in the test env
        // for its own reasons; we only assert the gate lets them
        // through (status != 403).
        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAs($superuser)
            ->post('/oauth/clients', [
                'name' => 'authorized-client',
                'redirect' => 'https://example.test/callback',
            ]);

        $this->assertNotSame(403, $response->status(), 'Superuser must not be blocked by the gate.');
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->post('/oauth/clients', [
            'name' => 'unauth-attempt',
            'redirect' => 'https://attacker.example/callback',
        ]);

        $this->assertContains($response->status(), [302, 401, 403], 'Unauthenticated requests must not receive a 2xx.');
    }
}
