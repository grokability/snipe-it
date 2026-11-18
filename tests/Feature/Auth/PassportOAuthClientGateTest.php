<?php

use App\Models\User;

test('normal user cannot list oauth clients', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/oauth/clients')
        ->assertForbidden();
});

test('normal user cannot create oauth client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/oauth/clients', [
            'name' => 'attacker-client',
            'redirect' => 'https://attacker.example/callback',
        ])
        ->assertForbidden();
});

test('normal user cannot update oauth client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/oauth/clients/any-client-id', [
            'name' => 'renamed',
            'redirect' => 'https://attacker.example/callback',
        ])
        ->assertForbidden();
});

test('normal user cannot delete oauth client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/oauth/clients/any-client-id')
        ->assertForbidden();
});

test('admin without superuser cannot manage oauth clients', function () {
    // Snipe-IT's /admin/oauth surfaces are gated on `superuser`,
    // not `admin`. Admins get 403 too, matching the intent.
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/oauth/clients', [
            'name' => 'admin-attempt',
            'redirect' => 'https://example.test/callback',
        ])
        ->assertForbidden();
});

test('superuser passes the gate', function () {
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
});

test('unauthenticated request is rejected', function () {
    $response = $this->post('/oauth/clients', [
        'name' => 'unauth-attempt',
        'redirect' => 'https://attacker.example/callback',
    ]);

    $this->assertContains($response->status(), [302, 401, 403], 'Unauthenticated requests must not receive a 2xx.');
});
