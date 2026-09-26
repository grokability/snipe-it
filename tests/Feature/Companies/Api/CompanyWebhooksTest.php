<?php


namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CompanyWebhooksTest extends TestCase
{
    public function test_can_filter_companies_with_webhooks(): void
    {
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

        $this->assertSame(
            [$companyWithWebhook->id],
            collect($response->json('rows'))
                ->pluck('id')
                ->all()
        );
    }
}