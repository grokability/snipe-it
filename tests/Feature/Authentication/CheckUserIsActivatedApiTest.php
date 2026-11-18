<?php

use App\Models\User;
use Laravel\Passport\Passport;

test('activated user can use api', function () {
    Passport::actingAs(User::factory()->create(['activated' => 1]));

    $this->getJson(route('api.users.me'))->assertOk();
});

test('deactivated user api token is refused', function () {
    Passport::actingAs(User::factory()->create(['activated' => 0]));

    $this->getJson(route('api.users.me'))->assertUnauthorized();
});

test('deactivated user cannot write via api', function () {
    Passport::actingAs(User::factory()->superuser()->create(['activated' => 0]));

    $this->postJson(route('api.users.store'), [
        'first_name' => 'Should',
        'last_name' => 'Not Persist',
        'username' => 'shouldnotpersist',
        'password' => 'This-Is-A-Long-Password-123!',
        'password_confirmation' => 'This-Is-A-Long-Password-123!',
    ])->assertUnauthorized();

    $this->assertDatabaseMissing('users', ['username' => 'shouldnotpersist']);
});

test('deactivated admin cannot self reactivate via api', function () {
    $user = User::factory()->admin()->create(['activated' => 0]);

    Passport::actingAs($user);

    $this->patchJson(route('api.users.update', $user), [
        'activated' => 1,
    ])->assertUnauthorized();

    expect((int) $user->fresh()->activated)->toBe(0, 'Account must remain deactivated');
});

test('deactivated response is generic unauthorized', function () {
    Passport::actingAs(User::factory()->create(['activated' => 0]));

    $response = $this->getJson(route('api.users.me'))->assertUnauthorized();

    $body = $response->json();
    expect($body['status'] ?? null)->toBe('error');

    // The generic 'general.unauthorized' translation; must NOT
    // mention "activated" / "disabled" / "inactive".
    $this->assertStringNotContainsStringIgnoringCase('activat', (string) ($body['messages'] ?? ''));
    $this->assertStringNotContainsStringIgnoringCase('disabled', (string) ($body['messages'] ?? ''));
});

test('bearer authenticated request without accept header returns json not 500', function () {
    Passport::actingAs(User::factory()->create(['activated' => 0]));

    $response = $this
        ->withHeader('Authorization', 'Bearer fake-token-value')
        ->get(route('api.assets.index'));

    $response->assertUnauthorized();
    expect($response->json('status'))->toBe('error');
});
