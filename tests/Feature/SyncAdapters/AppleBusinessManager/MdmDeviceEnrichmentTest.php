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

class MdmDeviceEnrichmentTest extends TestCase
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

    public function test_mdm_list_endpoint_populates_hostname_and_os()
    {
        // The list endpoint is always fetched. Even without any MDM
        // detail extras mapped, we should populate hostname (deviceName)
        // and os (derived from productFamily).
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/mdmDevices*' => Http::response([
                'data' => [
                    [
                        'id' => 'SN-abm-guid-a',
                        'type' => 'mdmDevice',
                        'attributes' => [
                            'deviceName' => 'ada-mbp',
                            'productFamily' => 'Mac',
                            'serialNumber' => 'SN-abm-guid-a',
                        ],
                    ],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a', 'SN-abm-guid-a')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertSame('ada-mbp', $record->hostname);
        $this->assertSame('macOS', $record->os);
    }

    public function test_pull_does_not_fetch_mdm_detail_when_no_detail_field_is_mapped()
    {
        // The detail endpoint is one call per device. Skip it when the
        // admin hasn't mapped any of the MDM detail-only extras.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/mdmDevices?*' => Http::response([
                'data' => [[
                    'id' => 'SN-abm-guid-a',
                    'type' => 'mdmDevice',
                    'attributes' => ['deviceName' => 'ada-mbp', 'productFamily' => 'Mac'],
                ]],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a', 'SN-abm-guid-a')],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/mdmDevices/SN-abm-guid-a/details');
        });
    }

    public function test_pull_fetches_mdm_detail_when_a_detail_field_is_mapped()
    {
        $customField = CustomField::factory()->create(['element' => 'checkbox', 'name' => 'FileVault']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_mdm_file_vault_enabled', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/mdmDevices?*' => Http::response([
                'data' => [[
                    'id' => 'SN-abm-guid-a',
                    'type' => 'mdmDevice',
                    'attributes' => ['deviceName' => 'ada-mbp', 'productFamily' => 'Mac'],
                ]],
            ]),
            'api-business.apple.com/v1/mdmDevices/SN-abm-guid-a/details' => Http::response([
                'data' => [
                    'id' => 'SN-abm-guid-a',
                    'type' => 'mdmDeviceDetail',
                    'attributes' => [
                        'osVersion' => '15.1.1',
                        'lastCheckInDateTime' => '2026-09-20T14:22:00Z',
                        'wifiMacAddress' => 'AA:BB:CC:00:11:22',
                        'ethernetMacAddress' => 'AA:BB:CC:00:11:33',
                        'isFileVaultEnabled' => true,
                        'isFirewallEnabled' => false,
                        'deviceLockStatus' => 'UNLOCKED',
                        'lostModeStatus' => 'DISABLED',
                        'deviceEraseStatus' => 'NORMAL',
                        'storageTotalCapacity' => 494,
                        'storageFreeCapacity' => 353,
                    ],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a', 'SN-abm-guid-a')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        // Native slots overridden from MDM runtime data.
        $this->assertSame('15.1.1', $record->osVersion);
        $this->assertSame('AA:BB:CC:00:11:22', $record->primaryMac);
        $this->assertNotNull($record->lastSeen);
        $this->assertSame('2026-09-20', $record->lastSeen->toDateString());

        // MDM-only extras populated.
        $this->assertTrue($record->extra['abm_mdm_file_vault_enabled']);
        $this->assertFalse($record->extra['abm_mdm_firewall_enabled']);
        $this->assertSame('UNLOCKED', $record->extra['abm_mdm_device_lock_status']);
        $this->assertSame('DISABLED', $record->extra['abm_mdm_lost_mode_status']);
        $this->assertSame('NORMAL', $record->extra['abm_mdm_device_erase_status']);
        $this->assertSame(494, $record->extra['abm_mdm_storage_total']);
        $this->assertSame(353, $record->extra['abm_mdm_storage_free']);
    }

    public function test_ethernet_mac_used_when_wifi_missing()
    {
        // Desktops without Wi-Fi (Mac Studio, Mac Pro) still report an
        // Ethernet MAC. When Wi-Fi is null, fall back to Ethernet for
        // the native primaryMac slot.
        $customField = CustomField::factory()->create(['element' => 'checkbox', 'name' => 'FileVault']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_mdm_file_vault_enabled', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/mdmDevices?*' => Http::response([
                'data' => [[
                    'id' => 'SN-mac-studio',
                    'type' => 'mdmDevice',
                    'attributes' => ['deviceName' => 'studio-01', 'productFamily' => 'Mac'],
                ]],
            ]),
            'api-business.apple.com/v1/mdmDevices/SN-mac-studio/details' => Http::response([
                'data' => [
                    'id' => 'SN-mac-studio',
                    'type' => 'mdmDeviceDetail',
                    'attributes' => [
                        'wifiMacAddress' => null,
                        'ethernetMacAddress' => 'AA:BB:CC:00:11:99',
                    ],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('mac-studio-guid', 'SN-mac-studio')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertSame('AA:BB:CC:00:11:99', $record->primaryMac);
    }

    public function test_records_without_a_matching_mdm_device_stay_un_enriched()
    {
        // 146/152 in the reporter's tenant were MDM-enrolled. The
        // remaining 6 have no mdmDevice record. They should sync
        // cleanly with the orgDevices values instead of crashing or
        // dropping.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/mdmDevices?*' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('non-mdm-guid', 'SN-non-mdm')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertNull($record->hostname);
        $this->assertNull($record->os);
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
    private function minimalAbmDevice(string $id, string $serial): array
    {
        return [
            'id' => $id,
            'attributes' => [
                'serialNumber' => $serial,
                'partNumber' => 'MK1E3LL/A',
                'productFamily' => 'Mac',
            ],
        ];
    }
}
