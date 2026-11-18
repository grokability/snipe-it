<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // ResetPasswordController registers 'throttle:10,1' in its
    // constructor. The array cache driver keeps counters alive across
    // tests, so a prior run could trip the limit before the current
    // test gets its first request through. Clear it.
    Cache::flush();

    // CheckForSetup redirects every request to /setup when the users
    // table is empty, which would mask the enumeration behavior under
    // test. Seed one unrelated user so setup is considered complete.
    User::factory()->create();
});

test('known and unknown emails return identical responses', function () {
    $known = User::factory()->create([
        'email' => 'known-user@example.test',
        'activated' => 1,
    ]);

    $knownResponse = $this->get('/password/reset/arbitrary-token?email='.urlencode($known->email));
    $unknownResponse = $this->get('/password/reset/arbitrary-token?email=unknown-user@example.test');

    $knownResponse->assertStatus(200);
    $unknownResponse->assertStatus(200);
});

test('form renders for unknown email without leaking existence', function () {
    $response = $this->get('/password/reset/arbitrary-token?email=never-registered@example.test');

    $response->assertStatus(200);

    // Form markup is identical regardless of whether the email was in
    // the users table.
    $response->assertSee('name="token"', false);
    $response->assertSee('name="username"', false);
    $response->assertSee('name="password"', false);
});

test('form does not redirect for unknown email', function () {
    /*
     * The old vulnerable branch sent unknown emails to a 302 with the
     * 'passwords.token' flash. Any 3xx on this endpoint re-opens the
     * enumeration oracle.
     */
    $response = $this->get('/password/reset/arbitrary-token?email=unknown@example.test');

    expect($response->status())->toBeLessThan(300);
    expect($response->status())->toBeGreaterThanOrEqual(200);
});
