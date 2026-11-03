<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/*
 * Regression coverage for GHSA-f7hg-289f-972m (CWE-203 Observable Discrepancy).
 * GET /password/reset/{token}?email=... used to render the form (200) when the
 * email matched an account and redirect (302) otherwise, giving an
 * unauthenticated caller a reliable existence oracle.
 */
class PasswordResetFormEnumerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // ResetPasswordController registers 'throttle:10,1' in its
        // constructor. The array cache driver keeps counters alive across
        // tests, so a prior run could trip the limit before the current
        // test gets its first request through. Clear it.
        Cache::flush();

        // CheckForSetup redirects every request to /setup when the users
        // table is empty, which would mask the enumeration behavior under
        // test. Seed one unrelated user so setup is considered complete.
        User::factory()->create();
    }

    public function test_known_and_unknown_emails_return_identical_responses(): void
    {
        $known = User::factory()->create([
            'email' => 'known-user@example.test',
            'activated' => 1,
        ]);

        $knownResponse = $this->get('/password/reset/arbitrary-token?email='.urlencode($known->email));
        $unknownResponse = $this->get('/password/reset/arbitrary-token?email=unknown-user@example.test');

        $knownResponse->assertStatus(200);
        $unknownResponse->assertStatus(200);
    }

    public function test_form_renders_for_unknown_email_without_leaking_existence(): void
    {
        $response = $this->get('/password/reset/arbitrary-token?email=never-registered@example.test');

        $response->assertStatus(200);
        // Form markup is identical regardless of whether the email was in
        // the users table.
        $response->assertSee('name="token"', false);
        $response->assertSee('name="username"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_form_does_not_redirect_for_unknown_email(): void
    {
        /*
         * The old vulnerable branch sent unknown emails to a 302 with the
         * 'passwords.token' flash. Any 3xx on this endpoint re-opens the
         * enumeration oracle.
         */
        $response = $this->get('/password/reset/arbitrary-token?email=unknown@example.test');

        $this->assertLessThan(300, $response->status());
        $this->assertGreaterThanOrEqual(200, $response->status());
    }
}
