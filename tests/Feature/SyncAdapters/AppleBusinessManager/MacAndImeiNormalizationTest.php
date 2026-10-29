<?php

namespace Tests\Feature\SyncAdapters\AppleBusinessManager;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\AppleBusinessManager\AppleBusinessManagerAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Issue #19754: Apple returns MAC addresses without colons (which
 * Snipe-IT's `mac_address` custom-field validator rejects) and IMEIs
 * as a JSON array (dual-SIM iPhones carry two, older devices one).
 * The adapter normalizes both before yielding records.
 */
class MacAndImeiNormalizationTest extends TestCase
{
    private string $privateKeyPem;

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        $pem = '';
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;
    }

    public function test_bare_hex_mac_addresses_normalize_to_colon_separated_uppercase()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-dual-sim-iphone',
                    'attributes' => [
                        'serialNumber' => 'SN-IPHONE-1',
                        'partNumber' => 'MQ8T3LL/A',
                        'productFamily' => 'iPhone',
                        'wifiMacAddress' => '0123456789ab',
                        'bluetoothMacAddress' => 'aabbccddeeff',
                        'ethernetMacAddress' => '112233445566',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('01:23:45:67:89:AB', $record->primaryMac);
        $this->assertSame('01:23:45:67:89:AB', $record->extra['abm_wifi_mac']);
        $this->assertSame('AA:BB:CC:DD:EE:FF', $record->extra['abm_bluetooth_mac']);
        $this->assertSame('11:22:33:44:55:66', $record->extra['abm_ethernet_mac_1']);
        $this->assertNull($record->extra['abm_ethernet_mac_2']);
    }

    public function test_already_colon_separated_mac_round_trips_unchanged()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-already-formatted',
                    'attributes' => [
                        'serialNumber' => 'SN-ALREADY',
                        'partNumber' => 'MQ8T3LL/A',
                        'productFamily' => 'iPhone',
                        'wifiMacAddress' => 'aa:bb:cc:00:11:22',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('AA:BB:CC:00:11:22', $record->primaryMac);
    }

    public function test_malformed_mac_falls_through_as_null()
    {
        // A short / junk value shouldn't crash and shouldn't poison the
        // custom field with an invalid value either. Null is the right
        // outcome so the record saves and the admin sees the empty slot.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-bad-mac',
                    'attributes' => [
                        'serialNumber' => 'SN-BAD-MAC',
                        'partNumber' => 'MQ8T3LL/A',
                        'productFamily' => 'iPhone',
                        'wifiMacAddress' => 'not-a-mac',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertNull($record->primaryMac);
        $this->assertNull($record->extra['abm_wifi_mac']);
    }

    public function test_dual_sim_iphone_populates_both_imei_slots()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-dual-imei',
                    'attributes' => [
                        'serialNumber' => 'SN-DUAL-IMEI',
                        'partNumber' => 'MQ8T3LL/A',
                        'productFamily' => 'iPhone',
                        'imei' => ['353912345678901', '353912345678902'],
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('353912345678901', $record->extra['abm_imei_1']);
        $this->assertSame('353912345678902', $record->extra['abm_imei_2']);
    }

    public function test_single_imei_iphone_populates_only_slot_one()
    {
        // Older single-SIM iPhones still return a one-element array.
        // _1 fills, _2 stays null.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-single-imei',
                    'attributes' => [
                        'serialNumber' => 'SN-SINGLE-IMEI',
                        'partNumber' => 'MQ8T3LL/A',
                        'productFamily' => 'iPhone',
                        'imei' => ['353912345678901'],
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('353912345678901', $record->extra['abm_imei_1']);
        $this->assertNull($record->extra['abm_imei_2']);
    }

    public function test_scalar_imei_falls_back_to_slot_one()
    {
        // Defensive: Apple's documented shape is an array, but if a
        // response ever drops back to a bare scalar we populate slot 1
        // rather than silently losing the value.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-scalar-imei',
                    'attributes' => [
                        'serialNumber' => 'SN-SCALAR-IMEI',
                        'partNumber' => 'MQ8T3LL/A',
                        'productFamily' => 'iPhone',
                        'imei' => '353912345678901',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('353912345678901', $record->extra['abm_imei_1']);
        $this->assertNull($record->extra['abm_imei_2']);
    }

    public function test_dual_nic_mac_populates_both_ethernet_mac_slots()
    {
        // Regression for #19877. Mac Pro, iMac Pro, and other dual-
        // Ethernet Macs return ethernetMacAddress as a two-element
        // array. Before the fix normalizeMacAddress saw the array,
        // failed its is_string gate, and the extra silently dropped
        // to null.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-dual-nic-mac',
                    'attributes' => [
                        'serialNumber' => 'SN-MAC-PRO',
                        'partNumber' => 'Z175LL/A',
                        'productFamily' => 'Mac',
                        'ethernetMacAddress' => ['008A76F01E74', '008A76E815A2'],
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('00:8A:76:F0:1E:74', $record->extra['abm_ethernet_mac_1']);
        $this->assertSame('00:8A:76:E8:15:A2', $record->extra['abm_ethernet_mac_2']);
    }

    public function test_single_ethernet_mac_scalar_populates_only_slot_one()
    {
        // iMacs and MacBook Pros with a single NIC (or an Apple USB-C
        // Ethernet adapter) still return a scalar. That path keeps
        // working after the fix via the extractMacAtIndex scalar
        // branch.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-single-nic-mac',
                    'attributes' => [
                        'serialNumber' => 'SN-IMAC',
                        'partNumber' => 'MKFH3LL/A',
                        'productFamily' => 'Mac',
                        'ethernetMacAddress' => '008A76F01E74',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('00:8A:76:F0:1E:74', $record->extra['abm_ethernet_mac_1']);
        $this->assertNull($record->extra['abm_ethernet_mac_2']);
    }

    public function test_non_cellular_device_leaves_both_imei_slots_null()
    {
        // Macs and non-cellular iPads don't carry an IMEI at all.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [[
                    'id' => 'abm-mac',
                    'attributes' => [
                        'serialNumber' => 'SN-MAC',
                        'partNumber' => 'MK1E3LL/A',
                        'productFamily' => 'Mac',
                    ],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertNull($record->extra['abm_imei_1']);
        $this->assertNull($record->extra['abm_imei_2']);
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
}
