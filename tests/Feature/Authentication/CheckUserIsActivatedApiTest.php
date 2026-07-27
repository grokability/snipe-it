<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Confirms that Personal Access Tokens (or any Passport-authenticated API
 * request) can't be used to call the API when the token's owner has been
 * deactivated. The web session path has always been protected by
 * CheckUserIsActivated in the `web` middleware group; the `api` group
 * was missing it, so a deactivated user's already-issued token kept
 * full permission-level access to the API - defeating "Activated" as an
 * offboarding control. A deactivated account with users.edit could
 * additionally re-activate itself via PATCH /api/v1/users/{own_id}.
 * This file pins the fix (CheckUserIsActivated added to the api group).
 */
class CheckUserIsActivatedApiTest extends TestCase
{
    public function test_activated_user_can_use_api(): void
    {
        Passport::actingAs(User::factory()->create(['activated' => 1]));

        $this->getJson(route('api.users.me'))->assertOk();
    }

    public function test_deactivated_user_api_token_is_refused(): void
    {
        Passport::actingAs(User::factory()->create(['activated' => 0]));

        $this->getJson(route('api.users.me'))->assertUnauthorized();
    }

    public function test_deactivated_user_cannot_write_via_api(): void
    {
        Passport::actingAs(User::factory()->superuser()->create(['activated' => 0]));

        $this->postJson(route('api.users.store'), [
            'first_name' => 'Should',
            'last_name' => 'Not Persist',
            'username' => 'shouldnotpersist',
            'password' => 'This-Is-A-Long-Password-123!',
            'password_confirmation' => 'This-Is-A-Long-Password-123!',
        ])->assertUnauthorized();

        $this->assertDatabaseMissing('users', ['username' => 'shouldnotpersist']);
    }

    /**
     * Escalation regression pin from the researcher's report: a
     * deactivated account with users.edit used to be able to
     * PATCH /api/v1/users/{own_id} {"activated": 1} and re-enable
     * itself, permanently defeating the deactivation. The middleware
     * now refuses the request before it reaches the controller.
     */
    public function test_deactivated_admin_cannot_self_reactivate_via_api(): void
    {
        $user = User::factory()->admin()->create(['activated' => 0]);

        Passport::actingAs($user);

        $this->patchJson(route('api.users.update', $user), [
            'activated' => 1,
        ])->assertUnauthorized();

        $this->assertSame(0, (int) $user->fresh()->activated, 'Account must remain deactivated');
    }

    /**
     * Response body deliberately does NOT distinguish "known token,
     * deactivated account" from a generic 401. Locks in the generic
     * message so a later change doesn't leak the specific reason (which
     * would help an attacker validate that a token they hold is
     * otherwise correct and just points at a disabled account).
     */
    public function test_deactivated_response_is_generic_unauthorized(): void
    {
        Passport::actingAs(User::factory()->create(['activated' => 0]));

        $response = $this->getJson(route('api.users.me'))->assertUnauthorized();

        $body = $response->json();
        $this->assertSame('error', $body['status'] ?? null);
        // The generic 'general.unauthorized' translation; must NOT
        // mention "activated" / "disabled" / "inactive".
        $this->assertStringNotContainsStringIgnoringCase('activat', (string) ($body['messages'] ?? ''));
        $this->assertStringNotContainsStringIgnoringCase('disabled', (string) ($body['messages'] ?? ''));
    }
}
