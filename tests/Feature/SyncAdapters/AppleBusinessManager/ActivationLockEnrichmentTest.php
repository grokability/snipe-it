<?php

namespace Tests\Feature\SyncAdapters\AppleBusinessManager;

use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\AppleBusinessManager\AppleBusinessManagerAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ActivationLockEnrichmentTest extends TestCase
{
    private string $privateKeyPem;

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'private_key_bits' => 2048,
        ]);
        $pem = '';
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;
    }

    public function test_pull_enriches_with_activation_lock_when_field_is_mapped()
    {
        $customField = CustomField::factory()->create(['element' => 'checkbox', 'name' => 'Activation Lock']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_activation_lock_enabled', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/abm-guid-a/activationLockStatus' => Http::response([
                'data' => [
                    'id' => 'abm-guid-a',
                    'type' => 'orgDevice',
                    'attributes' => ['activationLockEnabled' => true],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertTrue($record->extra['abm_activation_lock_enabled']);
    }

    public function test_pull_does_not_fetch_activation_lock_when_field_is_unmapped()
    {
        // Sync should skip the per-device activationLockStatus fetch
        // when nothing is mapped, so admins that don't care about AL
        // don't pay the N+1 cost.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertNull($record->extra['abm_activation_lock_enabled']);

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/activationLockStatus');
        });
    }

    public function test_pull_survives_activation_lock_endpoint_failure()
    {
        // Transient AL endpoint issues should not stall the sync run.
        // Record still comes through with a null AL extra.
        $customField = CustomField::factory()->create(['element' => 'checkbox', 'name' => 'AL']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_activation_lock_enabled', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/abm-guid-a/activationLockStatus' => Http::response(['error' => 'boom'], 500),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertNull($record->extra['abm_activation_lock_enabled']);
    }

    public function test_normalize_populates_device_capacity_wifi_mac_and_cellular_identifiers()
    {
        // Tier 1 coverage: fields that already come back on
        // /v1/orgDevices land in the record's extras without any
        // additional API call. Wi-Fi MAC also fills the normalized
        // primaryMac slot since it's the one every enrolled device
        // reports.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'iphone-1',
                    'attributes' => [
                        'serialNumber' => 'SN-iphone-1',
                        'partNumber' => 'MHDX3LL/A',
                        'productFamily' => 'iPhone',
                        'deviceCapacity' => '256GB',
                        'wifiMacAddress' => 'AA:BB:CC:00:11:22',
                        'bluetoothMacAddress' => 'AA:BB:CC:00:11:23',
                        'ethernetMacAddress' => null,
                        'imei' => '351234567890123',
                        'meid' => '35123456789012',
                        'eid' => '89012345678901234567890123456789',
                        'addedToOrgDateTime' => '2024-11-01T14:22:00Z',
                        'releasedFromOrgDateTime' => null,
                        'status' => 'ACTIVATED',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('AA:BB:CC:00:11:22', $record->primaryMac);
        $this->assertSame('256GB', $record->extra['abm_device_capacity']);
        $this->assertSame('AA:BB:CC:00:11:22', $record->extra['abm_wifi_mac']);
        $this->assertSame('AA:BB:CC:00:11:23', $record->extra['abm_bluetooth_mac']);
        $this->assertNull($record->extra['abm_ethernet_mac']);
        $this->assertSame('351234567890123', $record->extra['abm_imei']);
        $this->assertSame('35123456789012', $record->extra['abm_meid']);
        $this->assertSame('89012345678901234567890123456789', $record->extra['abm_eid']);
        $this->assertSame('2024-11-01', $record->extra['abm_added_to_org']);
        $this->assertNull($record->extra['abm_released_from_org']);
        $this->assertSame('ACTIVATED', $record->extra['abm_org_status']);
    }

    private function configuredAdapter(): AppleBusinessManagerAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mode', 'business');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client-id');
        SyncAdapterConfig::put($instance->id, 'key_id', 'stub-key-id');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));

        return new AppleBusinessManagerAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function minimalAbmDevice(string $id): array
    {
        return [
            'id' => $id,
            'attributes' => [
                'serialNumber' => 'SN-'.$id,
                'partNumber' => 'MK1E3LL/A',
                'productFamily' => 'Mac',
            ],
        ];
    }
}
