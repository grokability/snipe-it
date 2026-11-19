<?php

use App\Models\Company;
use App\Models\User;

test('can filter companies with webhooks', function () {
    $user = User::factory()->superuser()->create();

    $companyWithWebhook = Company::factory()->create([
        'webhook_endpoint' => 'https://example.com/webhook',
    ]);

    Company::factory()->create([
        'webhook_endpoint' => null,
    ]);

    Company::factory()->create([
        'webhook_endpoint' => '',
    ]);

    $response = $this->actingAsForApi($user)
        ->getJson(route('api.companies.index', [
            'has_webhook' => true,
        ]))
        ->assertOk()
        ->assertJsonPath('total', 1);

    expect(collect($response->json('rows'))
        ->pluck('id')
        ->all())->toBe([$companyWithWebhook->id]);
});
