<?php

namespace Tests\Feature\SyncAdapters\Fleet;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the "Sync Now" button flow (POST /admin/adapters/fleet/sync)
 * and the snipeit:pull-inventory artisan command targeted at Fleet.
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
        $exit = Artisan::call('snipeit:pull-inventory', ['adapter' => 'fleet']);

        $this->assertSame(1, $exit);
    }

    public function test_artisan_command_fails_with_unknown_instance()
    {
        $exit = Artisan::call('snipeit:pull-inventory', ['adapter' => 'nope']);

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

        $exit = Artisan::call('snipeit:pull-inventory', ['adapter' => $fleet->slug]);

        $this->assertSame(0, $exit);
        $this->assertDatabaseCount('asset_external_sources', 1);
        $output = Artisan::output();
        $this->assertStringContainsString('fleet', $output);
        $this->assertStringContainsString('OK', $output);

        // CLI runs write the same status columns as the interactive
        // button so scheduled cron jobs surface in the UI.
        $fleet->refresh();
        $this->assertNotNull($fleet->last_synced_at);
        $this->assertStringContainsString('Synced 1', $fleet->last_sync_result);
    }

    public function test_sync_button_dry_run_logs_records_and_writes_nothing()
    {
        $fleet = $this->configureFleet();
        SyncAdapterConfig::put($fleet->id, 'pull_dry_run', '1');

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

        // The vendor fetch still ran, so the summary should count the
        // records seen - but no asset-side rows were written.
        $this->assertDatabaseCount('asset_external_sources', 0);
        $this->assertDatabaseCount('assets', 0);

        $fleet->refresh();
        $this->assertStringContainsString('Synced 2', $fleet->last_sync_result);
    }

    public function test_new_sync_run_purges_previous_runs_cached_log()
    {
        // Regression test: without the purge-on-new-run hook, each run
        // orphans the prior run's cache entry until its 10-min TTL
        // fires. The purge lives inside captureSyncRunLog(), which pulls
        // and forgets the persistent sync_run_log_last_token pointer
        // before capturing the new run.
        $fleet = $this->configureFleet();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push(['hosts' => [['id' => 1, 'hostname' => 'first']]])
                ->push(['hosts' => [['id' => 2, 'hostname' => 'second']]]),
        ]);

        $actor = User::factory()->superuser()->create();

        // Run A: capture the flashed token so we can verify it dies.
        $first = $this->actingAs($actor)
            ->post(route('settings.adapters.sync', $fleet->slug))
            ->assertRedirect();
        $firstToken = $first->getSession()->get('sync_run_log_last_token');
        $this->assertNotEmpty($firstToken);
        $this->assertNotNull(Cache::get('sync_run_log:'.$firstToken));

        // Run B: triggering a new sync should Cache::forget run A's
        // entry (via the sync_run_log_last_token persistent pointer
        // put() into session by captureSyncRunLog on the first run)
        // and flash a fresh token for run B. withSession() seeds the
        // pointer the way a real browser session would after run A.
        $second = $this->actingAs($actor)
            ->withSession(['sync_run_log_last_token' => $firstToken])
            ->post(route('settings.adapters.sync', $fleet->slug))
            ->assertRedirect();
        $secondToken = $second->getSession()->get('sync_run_log_last_token');
        $this->assertNotEmpty($secondToken);
        $this->assertNotSame($firstToken, $secondToken);

        $this->assertNull(
            Cache::get('sync_run_log:'.$firstToken),
            'Previous run cache entry must be purged when a new sync starts.',
        );
        $this->assertNotNull(
            Cache::get('sync_run_log:'.$secondToken),
            'The new run cache entry must be present after the sync.',
        );
    }

    public function test_download_sync_run_log_route_serves_captured_output_once()
    {
        // The flashed token resolves to the captured log on first hit
        // and 404s on the second (single-use semantics via
        // Cache::forget after serve). Pin so a future refactor can't
        // silently drop the single-use guarantee and leave downloadable
        // log entries sitting in cache past their first fetch.
        $fleet = $this->configureFleet();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response([
                'hosts' => [['id' => 42, 'hostname' => 'wksn-01']],
            ]),
        ]);

        $actor = User::factory()->superuser()->create();
        $response = $this->actingAs($actor)
            ->post(route('settings.adapters.sync', $fleet->slug))
            ->assertRedirect();
        $token = $response->getSession()->get('sync_run_log_last_token');
        $this->assertNotEmpty($token);

        $downloadA = $this->actingAs($actor)
            ->get(route('settings.adapters.sync_log.download', ['token' => $token]))
            ->assertOk();

        $this->assertStringContainsString('fleet-pull-', $downloadA->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.log', $downloadA->headers->get('Content-Disposition') ?? '');

        // Second fetch of the same token must 404.
        $this->actingAs($actor)
            ->get(route('settings.adapters.sync_log.download', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_artisan_command_dry_run_logs_records_and_writes_nothing()
    {
        $fleet = $this->configureFleet();
        SyncAdapterConfig::put($fleet->id, 'pull_dry_run', '1');

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response([
                'hosts' => [
                    ['id' => 42, 'hostname' => 'wksn-01', 'hardware_model' => 'MacBook Pro'],
                ],
            ]),
        ]);

        $exit = Artisan::call('snipeit:pull-inventory', ['adapter' => $fleet->slug]);

        $this->assertSame(0, $exit);
        $this->assertDatabaseCount('asset_external_sources', 0);
        $this->assertDatabaseCount('assets', 0);

        $fleet->refresh();
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
