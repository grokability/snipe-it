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

class ReenrollmentReadoptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_re_enrollment_updates_stale_external_id_on_matching_serial()
    {
        $instance = $this->configuredAdapter();
        SyncAdapterConfig::put($instance->id, 'adopt_by_serial', '1');

        // First sync: establishes the asset and its initial external-source row.
        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'intune',
            sourceId: 'intune-managed-id-OLD',
            hostname: 'HMR-2240',
            hardwareSerial: '7MWDXL4',
            hardwareModel: 'Dell Pro 16 Plus PB16250',
        ));

        $asset = Asset::query()->where('serial', '7MWDXL4')->firstOrFail();
        $this->assertSame(1, Asset::query()->count());
        $this->assertDatabaseHas('asset_external_sources', [
            'asset_id' => $asset->id,
            'source' => 'intune',
            'external_id' => 'intune-managed-id-OLD',
        ]);

        // Second sync: the device re-enrolled, Intune issued a new managedDevice ID. Serial unchanged.
        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'intune',
            sourceId: 'intune-managed-id-NEW',
            hostname: 'HMR-2240',
            hardwareSerial: '7MWDXL4',
            hardwareModel: 'Dell Pro 16 Plus PB16250',
        ));

        $this->assertSame(1, Asset::query()->count(), 'No duplicate asset was created');
        $this->assertSame(
            1,
            DB::table('asset_external_sources')
                ->where('asset_id', $asset->id)
                ->where('source', 'intune')
                ->count(),
            'Exactly one external-source row for the asset',
        );
        $this->assertDatabaseHas('asset_external_sources', [
            'asset_id' => $asset->id,
            'source' => 'intune',
            'external_id' => 'intune-managed-id-NEW',
        ]);
        $this->assertDatabaseMissing('asset_external_sources', [
            'source' => 'intune',
            'external_id' => 'intune-managed-id-OLD',
        ]);
    }

    public function test_re_link_does_not_happen_when_adopt_by_serial_is_off()
    {
        // adopt_by_serial OFF. The existing asset's external-source row MUST stay on the OLD id (the fix never kicks in without the opt-in). Downstream behavior of the re-enrollment record (duplicate shell, validation failure) is out of scope for this test.
        $instance = $this->configuredAdapter();
        SyncAdapterConfig::put($instance->id, 'adopt_by_serial', '0');

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'intune',
            sourceId: 'intune-managed-id-OLD',
            hostname: 'HMR-2240',
            hardwareSerial: '7MWDXL4',
            hardwareModel: 'Dell Pro 16 Plus PB16250',
        ));

        $asset = Asset::query()->where('serial', '7MWDXL4')->firstOrFail();

        try {
            SyncAdapter::syncFromRecord(new HostInventoryRecord(
                sourceKey: 'intune',
                sourceId: 'intune-managed-id-NEW',
                hostname: 'HMR-2240',
                hardwareSerial: '7MWDXL4',
                hardwareModel: 'Dell Pro 16 Plus PB16250',
            ));
        } catch (\Throwable) {
            // Downstream fallout from the toggle being off is incidental.
        }

        $this->assertDatabaseHas('asset_external_sources', [
            'asset_id' => $asset->id,
            'source' => 'intune',
            'external_id' => 'intune-managed-id-OLD',
        ]);
        $this->assertDatabaseMissing('asset_external_sources', [
            'asset_id' => $asset->id,
            'external_id' => 'intune-managed-id-NEW',
        ]);
    }

    private function configuredAdapter(): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'tenant_id', 'fake-tenant-id');
        SyncAdapterConfig::put($instance->id, 'client_id', 'fake-client-id');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-client-secret'));

        return $instance->fresh();
    }
}
