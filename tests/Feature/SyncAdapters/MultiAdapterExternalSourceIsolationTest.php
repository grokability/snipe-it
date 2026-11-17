<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression test for issue #19880. Before the fix,
 * upsertExternalSource and loadExternalSourceInventory only keyed on
 * asset_id, so when adapter B synced an asset already owned by
 * adapter A, B's inventory columns (mac / ip / os / os_version /
 * last_seen) overwrote A's row and B's own row stayed empty.
 */
class MultiAdapterExternalSourceIsolationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_second_adapter_writes_to_its_own_row_not_the_first_adapters()
    {
        $fleet = $this->configuredAdapter('fleet');
        $sophos = $this->configuredAdapter('sophos_central');

        // Adapter 1 creates the asset and seeds its own aes row.
        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'fleet-ext-1',
            hostname: 'shared-mac',
            hardwareSerial: 'SHARED-SN',
            hardwareModel: 'MacBook Pro',
            primaryMac: 'AA:AA:AA:AA:AA:AA',
            primaryIp: '10.0.0.1',
            os: 'macOS',
            osVersion: '14.5',
        ));

        $asset = Asset::where('serial', 'SHARED-SN')->firstOrFail();

        // Adapter 2 opts into adopt_by_serial so it matches the existing
        // Snipe-IT asset by serial instead of creating a new one.
        SyncAdapterConfig::put($sophos->id, 'adopt_by_serial', '1');

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'sophos_central',
            sourceId: 'sophos-ext-1',
            hostname: 'shared-mac',
            hardwareSerial: 'SHARED-SN',
            hardwareModel: 'MacBook Pro',
            primaryMac: 'BB:BB:BB:BB:BB:BB',
            primaryIp: '10.0.0.2',
            os: 'macOS',
            osVersion: '15.0',
        ));

        $fleetRow = DB::table('asset_external_sources')
            ->where('asset_id', $asset->id)
            ->where('source', 'fleet')
            ->first();
        $sophosRow = DB::table('asset_external_sources')
            ->where('asset_id', $asset->id)
            ->where('source', 'sophos_central')
            ->first();

        // Fleet's row keeps Fleet's values. Pre-fix, Sophos clobbered
        // these to BB:... / 10.0.0.2 / 15.0.
        $this->assertSame('AA:AA:AA:AA:AA:AA', $fleetRow->primary_mac);
        $this->assertSame('10.0.0.1', $fleetRow->primary_ip);
        $this->assertSame('14.5', $fleetRow->os_version);

        // Sophos's row has Sophos's values. Pre-fix, these were null.
        $this->assertSame('BB:BB:BB:BB:BB:BB', $sophosRow->primary_mac);
        $this->assertSame('10.0.0.2', $sophosRow->primary_ip);
        $this->assertSame('15.0', $sophosRow->os_version);
    }

    private function configuredAdapter(string $slug): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/'.$slug);
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return $instance->fresh();
    }
}
