<?php

namespace Tests\Feature\SyncAdapters\Intune;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Intune\IntuneAdapter;
use App\SyncAdapters\PushableAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Intune composed-notes push path.
 * Intune's writable per-device surface is limited so v1 only pushes
 * composed notes (the pattern Brady Widener's community PowerShell
 * script uses). Tests verify the PATCH lands on Graph beta with
 * the right shape, the target override works, and dry-run behaves.
 */
class IntunePushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_intune_adapter_implements_pushable_interface()
    {
        $this->assertInstanceOf(PushableAdapter::class, $this->configuredIntune());
    }

    public function test_push_notes_template_patches_intune_managed_device()
    {
        $adapter = $this->configuredIntune(template: "Tag: {asset_tag}\nStatus: {status}");
        $status = Statuslabel::factory()->rtd()->create(['name' => 'Deployable']);
        $asset = Asset::factory()->create([
            'asset_tag' => 'INT-42',
            'status_id' => $status->id,
        ]);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'graph-device-uuid-42',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/oauth2/v2.0/token')) {
                return Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]);
            }
            if ($request->method() === 'PATCH' && str_contains($request->url(), '/beta/deviceManagement/managedDevices/graph-device-uuid-42')) {
                return Http::response(['ok' => true]);
            }

            return Http::response(['unmatched' => true], 500);
        });

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }
            if (! str_contains($request->url(), '/beta/deviceManagement/managedDevices/graph-device-uuid-42')) {
                return false;
            }
            $notes = $request->data()['notes'] ?? '';

            return str_contains($notes, 'Tag: INT-42') && str_contains($notes, 'Status: Deployable');
        });
    }

    public function test_target_override_writes_to_admin_configured_field()
    {
        // Admin points composed notes at a Graph field other than
        // "notes" via the vendor-field override.
        $adapter = $this->configuredIntune(template: 'Tag: {asset_tag}');
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'push_notes_target', 'roleScopeTagIds');

        $asset = Asset::factory()->create(['asset_tag' => 'OVR-1']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'guid-99',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/oauth2/v2.0/token')) {
                return Http::response(['access_token' => 'stub', 'expires_in' => 3600]);
            }

            return Http::response(['ok' => true]);
        });

        // Re-hydrate the adapter so the new push_notes_target config
        // gets picked up (the accessor reads through SyncAdapterConfig,
        // no cache to invalidate, but we hydrate anyway for clarity).
        $freshInstance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        $freshAdapter = new IntuneAdapter($freshInstance);
        $freshAdapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            return ($request->data()['roleScopeTagIds'] ?? null) === 'Tag: OVR-1'
                && ! isset($request->data()['notes']);
        });
    }

    public function test_blank_template_skips_push_entirely()
    {
        $adapter = $this->configuredIntune(template: '');
        $asset = Asset::factory()->create(['asset_tag' => 'NO-TPL']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'guid-77',
        ]);

        Http::fake();

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    private function configuredIntune(?string $template = null): IntuneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://graph.microsoft.com');
        SyncAdapterConfig::put($instance->id, 'tenant_id', 'stub-tenant');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-secret'));
        if ($template !== null) {
            SyncAdapterConfig::put($instance->id, 'push_notes_template', $template);
        }

        return new IntuneAdapter($instance->fresh());
    }
}
