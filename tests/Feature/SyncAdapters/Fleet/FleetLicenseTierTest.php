<?php

namespace Tests\Feature\SyncAdapters\Fleet;

use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for Fleet Premium detection. After the admin saves the
 * Fleet config, the adapter probes /api/latest/fleet/config, caches
 * the license tier, and hides the group-mapping UI on Free tier so
 * admins don't hit a 402 when they try to Refresh.
 */
class FleetLicenseTierTest extends TestCase
{
    /**
     * @return array<string, int>
     */
    private function requiredFields(string $slug): array
    {
        return [
            $slug.'_default_category_id' => Category::factory()->create()->id,
            $slug.'_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
        ];
    }

    public function test_saving_free_tier_fleet_caches_free_and_hides_group_scoping()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        Http::fake([
            '*/api/latest/fleet/config' => Http::response([
                'license' => ['tier' => 'free'],
            ]),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'https://example.com/free-fleet',
                'fleet_token' => 'plaintext-token',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('free', SyncAdapterConfig::get($fleet->id, 'fleet_license_tier'));
        $this->assertFalse($fleet->fresh()->adapter()->supportsGroupScoping());
    }

    public function test_saving_premium_tier_fleet_caches_premium_and_keeps_group_scoping()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        Http::fake([
            '*/api/latest/fleet/config' => Http::response([
                'license' => ['tier' => 'premium', 'device_count' => 500],
            ]),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'https://example.com/premium-fleet',
                'fleet_token' => 'plaintext-token',
            ])
            ->assertRedirect();

        $this->assertSame('premium', SyncAdapterConfig::get($fleet->id, 'fleet_license_tier'));
        $this->assertTrue($fleet->fresh()->adapter()->supportsGroupScoping());
    }

    public function test_unreachable_config_endpoint_leaves_cache_absent_and_defaults_group_scoping_on()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        // /config returns 500. licenseTier() catches and returns null,
        // afterSaveConfig() writes nothing, supportsGroupScoping()
        // stays permissive (unknown != free).
        Http::fake([
            '*/api/latest/fleet/config' => Http::response([], 500),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'https://example.com/broken-fleet',
                'fleet_token' => 'plaintext-token',
            ])
            ->assertRedirect();

        $this->assertNull(SyncAdapterConfig::get($fleet->id, 'fleet_license_tier'));
        $this->assertTrue($fleet->fresh()->adapter()->supportsGroupScoping());
    }

    public function test_free_tier_stays_hidden_across_subsequent_saves_until_upgrade()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($fleet->id, 'fleet_license_tier', 'free');
        SyncAdapterConfig::put($fleet->id, 'url', 'https://example.com/free-fleet');
        SyncAdapterConfig::put($fleet->id, 'token', Crypt::encrypt('t'));

        $this->assertFalse($fleet->fresh()->adapter()->supportsGroupScoping());

        // Simulate the admin upgrading Fleet to Premium and re-saving.
        Http::fake([
            '*/api/latest/fleet/config' => Http::response([
                'license' => ['tier' => 'premium'],
            ]),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'https://example.com/free-fleet',
                'fleet_token' => 'plaintext-token',
            ])
            ->assertRedirect();

        $this->assertSame('premium', SyncAdapterConfig::get($fleet->id, 'fleet_license_tier'));
        $this->assertTrue($fleet->fresh()->adapter()->supportsGroupScoping());
    }
}
