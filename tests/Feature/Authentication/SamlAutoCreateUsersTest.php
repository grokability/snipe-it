<?php

use App\Models\User;
use App\Services\Saml;

function samlData(string $username, array $attributes = []): array
{
    return [
        'nameId' => $username,
        'attributes' => $attributes ?: [
            Saml::CLAIM_GIVEN_NAME => ['Ada'],
            Saml::CLAIM_SURNAME => ['Lovelace'],
            Saml::CLAIM_EMAIL => ['ada@example.com'],
        ],
    ];
}

test('auto create setting can be saved', function () {
    $this->settings->set(['saml_auto_create_users' => 0]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('settings.saml.save'), [
            'saml_auto_create_users' => '1',
            'saml_sp_x509cert' => '',
        ])
        ->assertRedirect(route('settings.saml.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('settings', ['saml_auto_create_users' => 1]);
});

test('unknown user is rejected when auto create is disabled', function () {
    $this->settings->set(['saml_auto_create_users' => 0]);

    expect(app(Saml::class)->samlLogin(samlData('ada@example.com')))->toBeNull();
    $this->assertDatabaseMissing('users', ['username' => 'ada@example.com']);
});

test('unknown user is created from claims when auto create is enabled', function () {
    $this->settings->set(['saml_auto_create_users' => 1]);

    $user = app(Saml::class)->samlLogin(samlData('ada@example.com'));

    expect($user)->not->toBeNull();
    expect($user->username)->toBe('ada@example.com');
    expect($user->first_name)->toBe('Ada');
    expect($user->last_name)->toBe('Lovelace');
    expect($user->email)->toBe('ada@example.com');
    expect($user->activated)->toEqual(1);
    expect($user->isSuperUser())->toBeFalse();
    expect($user->isAdmin())->toBeFalse();
    expect($user->password)->toBe($user->noPassword());
});

test('first name falls back to username when claim is missing', function () {
    $this->settings->set(['saml_auto_create_users' => 1]);

    $user = app(Saml::class)->samlLogin(samlData('ada@example.com', ['unrelated' => ['x']]));

    expect($user->first_name)->toBe('ada@example.com');
    expect($user->email)->toBeNull();
});

test('existing user is returned without creating another', function () {
    $this->settings->set(['saml_auto_create_users' => 1]);
    $existing = User::factory()->create(['username' => 'ada@example.com']);

    $user = app(Saml::class)->samlLogin(samlData('ada@example.com'));

    expect($existing->is($user))->toBeTrue();
    expect(User::where('username', 'ada@example.com')->count())->toBe(1);
});

test('deactivated user is not recreated', function () {
    $this->settings->set(['saml_auto_create_users' => 1]);
    User::factory()->create(['username' => 'ada@example.com', 'activated' => 0]);

    expect(app(Saml::class)->samlLogin(samlData('ada@example.com')))->toBeNull();
    expect(User::withTrashed()->where('username', 'ada@example.com')->count())->toBe(1);
});

test('deleted user is not recreated', function () {
    $this->settings->set(['saml_auto_create_users' => 1]);
    User::factory()->create(['username' => 'ada@example.com'])->delete();

    expect(app(Saml::class)->samlLogin(samlData('ada@example.com')))->toBeNull();
    expect(User::withTrashed()->where('username', 'ada@example.com')->count())->toBe(1);
});
