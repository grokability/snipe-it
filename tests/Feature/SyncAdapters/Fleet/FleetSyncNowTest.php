<?php

namespace Tests\Feature\SyncAdapters\Fleet;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the "Sync Now" button flow (POST /admin/adapters/fleet/sync)
 * and the snipeit:sync-inventory artisan command targeted at Fleet.
 * Both share the same adapter plumbing, but each entry point is tested
 * independently so a regression in either path is caught.
 */
class FleetSyncNowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_sync_button_returns_error_when_fleet_is_not_configured()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.sync', 'fleet'))
            ->assertRedirect(route('settings.adapters.index', ['adapter' => 'fleet']))
            ->assertSessionHas('error');
    }

    public function test_sync_button_runs_the_adapter_when_configured()
    {
        $fleet = $this->configureFleet();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response([
                'hosts' => [
                    ['id' => 42, 'hostname' => 'wksn-01', 'hardware_model' => 'MacBook Pro'],
                    ['id' => 99, 'hostname' => 'wksn-02', 'hardware_model' => 'ThinkPad X1'],
                ],
            ]),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.sync', $fleet->slug))
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $fleet->slug]))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseCount('assets', 2);

        // Status columns persist the last-run outcome so page reload
        // after a browser tab close mid-sync still shows what happened
        // server-side.
        $fleet->refresh();
        $this->assertNotNull($fleet->last_synced_at);
        $this->assertStringContainsString('Synced 2', $fleet->last_sync_result);
    }

    public function test_non_superuser_cannot_trigger_sync()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('settings.adapters.sync', 'fleet'))
            ->assertForbidden();
    }

    public function test_unknown_instance_returns_404()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.sync', 'does-not-exist'))
            ->assertNotFound();
    }

    public function test_artisan_command_fails_when_fleet_is_not_configured()
    {
        $exit = Artisan::call('snipeit:sync-inventory', ['adapter' => 'fleet']);

        $this->assertSame(1, $exit);
    }

    public function test_artisan_command_fails_with_unknown_instance()
    {
        $exit = Artisan::call('snipeit:sync-inventory', ['adapter' => 'nope']);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('Unknown adapter instance', Artisan::output());
    }

    public function test_artisan_command_pulls_and_upserts_hosts()
    {
        $fleet = $this->configureFleet();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response([
                'hosts' => [
                    ['id' => 42, 'hostname' => 'wksn-01', 'hardware_model' => 'MacBook Pro'],
                ],
            ]),
        ]);

        $exit = Artisan::call('snipeit:sync-inventory', ['adapter' => $fleet->slug]);

        $this->assertSame(0, $exit);
        $this->assertDatabaseCount('asset_external_sources', 1);
        // CLI + UI now share the sync_adapter_sync_complete phrasing so
        // scheduled runs render the same status admins see on the
        // settings page.
        $this->assertStringContainsString('Synced 1', Artisan::output());

        // CLI runs write the same status columns as the interactive
        // button so scheduled cron jobs surface in the UI.
        $fleet->refresh();
        $this->assertNotNull($fleet->last_synced_at);
        $this->assertStringContainsString('Synced 1', $fleet->last_sync_result);
    }

    /**
     * Set the built-in Fleet instance to active and populate its
     * credentials. Returns the fresh instance.
     */
    private function configureFleet(): SyncAdapterInstance
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        $fleet->active = true;
        $fleet->save();

        SyncAdapterConfig::put($fleet->id, 'url', 'https://fleet.example.test');
        SyncAdapterConfig::put($fleet->id, 'token', Crypt::encrypt('fake-token'));

        return $fleet->fresh();
    }
}
