<?php

use App\Models\Company;
use App\Models\User;

/**
 * @return array<string, array{0: string}>
 */
dataset('internalTargetProvider', function () {
    return [
        'loopback v4' => ['http://127.0.0.1/attacker'],
        'loopback v6' => ['http://[::1]/attacker'],
        'aws imds' => ['http://169.254.169.254/latest/meta-data/'],
        'gcp metadata' => ['http://metadata.google.internal/computeMetadata/v1/'],
        'rfc1918 10' => ['http://10.0.0.5/attacker'],
        'rfc1918 172' => ['http://172.16.0.1/attacker'],
        'rfc1918 192' => ['http://192.168.1.1/attacker'],
        'non-http scheme ftp' => ['ftp://example.com/attacker'],
        'non-http scheme gopher' => ['gopher://example.com/attacker'],
    ];
});

test('api store rejects internal or non http webhook endpoint', function (string $endpoint) {
    $caller = User::factory()->superuser()->create();

    $this->actingAsForApi($caller)
        ->postJson(route('api.companies.store'), [
            'name' => 'SSRF test '.bin2hex(random_bytes(4)),
            'webhook_selected' => 'slack',
            'webhook_endpoint' => $endpoint,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('companies', ['webhook_endpoint' => $endpoint]);
})->with('internalTargetProvider');

test('api update rejects internal or non http webhook endpoint', function (string $endpoint) {
    $company = Company::factory()->create(['webhook_endpoint' => null]);
    $caller = User::factory()->superuser()->create();

    $this->actingAsForApi($caller)
        ->patchJson(route('api.companies.update', $company), [
            'name' => $company->name,
            'webhook_selected' => 'slack',
            'webhook_endpoint' => $endpoint,
        ])
        ->assertStatusMessageIs('error');

    expect($company->refresh()->webhook_endpoint)->toBe(null);
})->with('internalTargetProvider');

test('api accepts a public https webhook endpoint from superuser', function () {
    $caller = User::factory()->superuser()->create();

    $this->actingAsForApi($caller)
        ->postJson(route('api.companies.store'), [
            'name' => 'legit webhook',
            'webhook_selected' => 'slack',
            'webhook_endpoint' => 'https://hooks.slack.com/services/T000/B000/XXXXX',
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('companies', [
        'name' => 'legit webhook',
        'webhook_endpoint' => 'https://hooks.slack.com/services/T000/B000/XXXXX',
    ]);
});

test('api rejects unknown webhook selected value', function () {
    $caller = User::factory()->superuser()->create();

    $this->actingAsForApi($caller)
        ->postJson(route('api.companies.store'), [
            'name' => 'bogus webhook_selected',
            'webhook_selected' => 'definitely-not-a-real-webhook-type',
            'webhook_endpoint' => 'https://hooks.slack.com/services/T000/B000/XXXXX',
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('companies', ['name' => 'bogus webhook_selected']);
});

test('api allows non superuser to create company with no webhook fields', function () {
    // Companies without webhooks are the common case. The gate must
    // not block a routine create for a companies.create holder when
    // the body carries nothing in GATED_WEBHOOK_FIELDS.
    $caller = User::factory()->createCompanies()->create();

    $this->actingAsForApi($caller)
        ->postJson(route('api.companies.store'), [
            'name' => 'no webhook',
        ])
        ->assertStatusMessageIs('success');
});

/**
 * @return array<string, array{0: array<string, mixed>}>
 */
dataset('gatedWebhookPayloadProvider', function () {
    return [
        'webhook_endpoint only' => [['webhook_endpoint' => 'https://hooks.slack.com/x']],
        'webhook_selected only' => [['webhook_selected' => 'slack']],
        'webhook_channel only' => [['webhook_channel' => '#general']],
        'webhook_botname only' => [['webhook_botname' => 'bot']],
        'all four fields' => [[
            'webhook_endpoint' => 'https://hooks.slack.com/x',
            'webhook_selected' => 'slack',
            'webhook_channel' => '#general',
            'webhook_botname' => 'bot',
        ]],
    ];
});

test('api store rejects webhook fields from non superuser', function (array $gatedFields) {
    $caller = User::factory()->createCompanies()->create();

    $this->actingAsForApi($caller)
        ->postJson(route('api.companies.store'), array_merge(
            ['name' => 'blocked webhook write '.bin2hex(random_bytes(4))],
            $gatedFields,
        ))
        ->assertStatusMessageIs('error')
        ->assertJsonPath('messages', fn (string $m) => str_contains($m, 'superuser'));

    // Verify the row never got created. A silent-strip implementation
    // would have persisted the Company with the gated fields nulled,
    // which this assertion catches.
    $this->assertDatabaseMissing('companies', ['name' => 'blocked webhook write']);
})->with('gatedWebhookPayloadProvider');

test('api update rejects webhook fields from non superuser', function (array $gatedFields) {
    $company = Company::factory()->create([
        'webhook_endpoint' => null,
        'webhook_selected' => null,
        'webhook_channel' => null,
        'webhook_botname' => null,
    ]);
    $caller = User::factory()->editCompanies()->create();

    $this->actingAsForApi($caller)
        ->patchJson(route('api.companies.update', $company), array_merge(
            ['name' => $company->name],
            $gatedFields,
        ))
        ->assertStatusMessageIs('error')
        ->assertJsonPath('messages', fn (string $m) => str_contains($m, 'superuser'));

    // Verify every gated field in the payload stayed null on the
    // persisted row. A silent-strip implementation would also pass
    // the status-message check above but would have landed a
    // partial update with the gated fields nulled.
    $refreshed = $company->refresh();
    foreach (array_keys($gatedFields) as $field) {
        expect($refreshed->{$field})->toBeNull("field {$field} should not have been written");
    }
})->with('gatedWebhookPayloadProvider');

test('api update allows non superuser to edit non webhook fields', function () {
    // Non-superuser caller with companies.edit can still rename
    // a company, set its phone number, change tag_color. The gate
    // is specifically on GATED_WEBHOOK_FIELDS, not on the whole
    // update surface.
    $company = Company::factory()->create(['name' => 'before', 'phone' => '555-0000']);
    $caller = User::factory()->editCompanies()->create();

    $this->actingAsForApi($caller)
        ->patchJson(route('api.companies.update', $company), [
            'name' => 'after',
            'phone' => '555-9999',
        ])
        ->assertStatusMessageIs('success');

    $refreshed = $company->refresh();
    expect($refreshed->name)->toBe('after');
    expect($refreshed->phone)->toBe('555-9999');
});
