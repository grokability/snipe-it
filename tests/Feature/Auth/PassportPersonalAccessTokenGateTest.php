<?php

use App\Models\User;

test('user without self api cannot create personal access token', function () {
    $user = User::factory()->create(); // default: no self.api permission

    $this->actingAs($user)
        ->post('/oauth/personal-access-tokens', [
            'name' => 'bypass-attempt',
            'scopes' => [],
        ])
        ->assertForbidden();
});

test('user without self api cannot list personal access tokens', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/oauth/personal-access-tokens')
        ->assertForbidden();
});

test('user without self api cannot delete personal access token', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/oauth/personal-access-tokens/any-token-id')
        ->assertForbidden();
});

test('unauthenticated request is rejected by auth middleware', function () {
    // Baseline: the outer `auth` middleware still fires before
    // the can:self.api gate. Unauthenticated requests get bounced
    // to login, not silently allowed.
    $response = $this->post('/oauth/personal-access-tokens', [
        'name' => 'unauth-attempt',
        'scopes' => [],
    ]);

    $this->assertContains($response->status(), [302, 401, 403], 'Unauthenticated requests must not receive a 200/2xx');
});

test('superuser passes the self api gate', function () {
    // Superusers bypass every gate via AuthServiceProvider's
    // Gate::before hook, so they must still be able to reach the
    // controller. The controller itself may fail later in the
    // test env if no Passport personal access client is
    // installed; that's a Passport-setup concern, not the gate's.
    // We only assert the gate lets them through (status != 403).
    $user = User::factory()->superuser()->create();

    $response = $this->actingAs($user)
        ->post('/oauth/personal-access-tokens', [
            'name' => 'authorized-attempt',
            'scopes' => [],
        ]);

    $this->assertNotSame(403, $response->status(), 'Superuser must not be blocked by the self.api gate.');
});
