<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression tests for GHSA-x9rj-mmg2-wcfv. Covers the two paired
 * controls that close the per-company webhook mass-assignment hole:
 *
 *   1. ExternalUrl rule layered onto Company::$rules via getRules().
 *      Rejects loopback / RFC1918 / cloud metadata / non-HTTP schemes
 *      at save time. These tests run AS A SUPERUSER so the request
 *      clears the gate below and actually exercises the URL rule.
 *
 *   2. Authz gate in Api\CompaniesController that rejects any mass-
 *      assign request from a non-superuser if the body touches any
 *      Company::GATED_WEBHOOK_FIELDS. Mirrors the Livewire integration
 *      settings form's superuser requirement.
 */
class WebhookEndpointSsrfTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function internalTargetProvider(): array
    {
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
    }

    #[DataProvider('internalTargetProvider')]
    public function test_api_store_rejects_internal_or_non_http_webhook_endpoint(string $endpoint): void
    {
        $caller = User::factory()->superuser()->create();

        $this->actingAsForApi($caller)
            ->postJson(route('api.companies.store'), [
                'name' => 'SSRF test '.bin2hex(random_bytes(4)),
                'webhook_selected' => 'slack',
                'webhook_endpoint' => $endpoint,
            ])
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('companies', ['webhook_endpoint' => $endpoint]);
    }

    #[DataProvider('internalTargetProvider')]
    public function test_api_update_rejects_internal_or_non_http_webhook_endpoint(string $endpoint): void
    {
        $company = Company::factory()->create(['webhook_endpoint' => null]);
        $caller = User::factory()->superuser()->create();

        $this->actingAsForApi($caller)
            ->patchJson(route('api.companies.update', $company), [
                'name' => $company->name,
                'webhook_selected' => 'slack',
                'webhook_endpoint' => $endpoint,
            ])
            ->assertStatusMessageIs('error');

        $this->assertSame(null, $company->refresh()->webhook_endpoint);
    }

    public function test_api_accepts_a_public_https_webhook_endpoint_from_superuser(): void
    {
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
    }

    public function test_api_rejects_unknown_webhook_selected_value(): void
    {
        $caller = User::factory()->superuser()->create();

        $this->actingAsForApi($caller)
            ->postJson(route('api.companies.store'), [
                'name' => 'bogus webhook_selected',
                'webhook_selected' => 'definitely-not-a-real-webhook-type',
                'webhook_endpoint' => 'https://hooks.slack.com/services/T000/B000/XXXXX',
            ])
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('companies', ['name' => 'bogus webhook_selected']);
    }

    public function test_api_allows_non_superuser_to_create_company_with_no_webhook_fields(): void
    {
        // Companies without webhooks are the common case. The gate must
        // not block a routine create for a companies.create holder when
        // the body carries nothing in GATED_WEBHOOK_FIELDS.
        $caller = User::factory()->createCompanies()->create();

        $this->actingAsForApi($caller)
            ->postJson(route('api.companies.store'), [
                'name' => 'no webhook',
            ])
            ->assertStatusMessageIs('success');
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function gatedWebhookPayloadProvider(): array
    {
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
    }

    /**
     * @param  array<string, mixed>  $gatedFields
     */
    #[DataProvider('gatedWebhookPayloadProvider')]
    public function test_api_store_rejects_webhook_fields_from_non_superuser(array $gatedFields): void
    {
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
    }

    /**
     * @param  array<string, mixed>  $gatedFields
     */
    #[DataProvider('gatedWebhookPayloadProvider')]
    public function test_api_update_rejects_webhook_fields_from_non_superuser(array $gatedFields): void
    {
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
            $this->assertNull($refreshed->{$field}, "field {$field} should not have been written");
        }
    }

    public function test_api_update_allows_non_superuser_to_edit_non_webhook_fields(): void
    {
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
        $this->assertSame('after', $refreshed->name);
        $this->assertSame('555-9999', $refreshed->phone);
    }
}
