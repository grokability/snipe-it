<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Guards the refresh-sites endpoint against being used as a
 * request-forwarding primitive. The save path already validates
 * configured URLs through the ExternalUrl rule. refresh-sites also
 * accepts a URL override on the request so a brand-new adapter can
 * list sites before anything is persisted, and that same override
 * must clear the same rule.
 */
class RefreshSitesExternalUrlValidationTest extends TestCase
{
    public static function internalTargets(): array
    {
        return [
            'loopback v4' => ['http://127.0.0.1:8111'],
            'loopback v6' => ['http://[::1]:8111'],
            'rfc1918 10/8' => ['http://10.0.0.5'],
            'rfc1918 192.168' => ['http://192.168.1.1'],
            'link-local metadata' => ['http://169.254.169.254/latest/meta-data/'],
            'ftp scheme' => ['ftp://example.com'],
        ];
    }

    #[DataProvider('internalTargets')]
    public function test_refresh_sites_rejects_internal_url_overrides(string $url): void
    {
        config()->set('app.sync_adapter_allow_internal_targets', false);

        $instance = SyncAdapterInstance::where('slug', 'unifi')->firstOrFail();

        Http::fake();

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('settings.adapters.refresh_sites', ['instance' => $instance]), [
                'url' => $url,
                'api_key' => 'anything',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['error']);

        Http::assertNothingSent();
    }

    public function test_refresh_sites_accepts_public_url_override(): void
    {
        config()->set('app.sync_adapter_allow_internal_targets', false);

        $instance = SyncAdapterInstance::where('slug', 'unifi')->firstOrFail();

        Http::fake([
            '*/v1/sites*' => Http::response(['data' => [['id' => 'default', 'name' => 'Default']]]),
            '*/proxy/network/integration/v1/sites*' => Http::response(['data' => [['id' => 'default', 'name' => 'Default']]]),
        ]);

        // Hardcoded public IP so the test does not depend on DNS
        // resolution for a hostname. Http::fake short-circuits before
        // any socket is opened.
        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('settings.adapters.refresh_sites', ['instance' => $instance]), [
                'url' => 'https://8.8.8.8',
                'api_key' => 'fake-unifi-api-key',
            ])
            ->assertOk();
    }

    public function test_refresh_sites_opt_in_allows_internal_url_overrides(): void
    {
        config()->set('app.sync_adapter_allow_internal_targets', true);

        $instance = SyncAdapterInstance::where('slug', 'unifi')->firstOrFail();

        Http::fake([
            '*/v1/sites*' => Http::response(['data' => []]),
            '*/proxy/network/integration/v1/sites*' => Http::response(['data' => []]),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('settings.adapters.refresh_sites', ['instance' => $instance]), [
                'url' => 'http://127.0.0.1:8111',
                'api_key' => 'anything',
            ])
            ->assertOk();
    }
}
