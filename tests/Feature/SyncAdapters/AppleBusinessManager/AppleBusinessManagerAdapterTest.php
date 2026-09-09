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
 * End-to-end coverage for the Apple Business Manager adapter.
 * Generates a fresh EC P-256 keypair at test time so the JWT
 * signing path runs unmocked, and fakes Apple's token endpoint
 * plus /v1/orgDevices and /v1/mdmServers so the extraction logic
 * is exercised without any actual network calls.
 */
class AppleBusinessManagerAdapterTest extends TestCase
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
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;
    }

    public function test_pulls_devices_and_extracts_apple_specific_fields()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    [
                        'id' => 'abm-guid-1',
                        'attributes' => [
                            'serialNumber' => 'C02XL1234567',
                            'partNumber' => 'MK1E3LL/A',
                            'productFamily' => 'Mac',
                            'color' => 'SPACEBLACK',
                            'orderNumber' => 'W1234567',
                            'orderDateTime' => '2025-08-15T14:22:00.000Z',
                            'purchaseSourceType' => 'AppleCustomer',
                            'purchaseSourceId' => 'DEP12345',
                        ],
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('apple_business_manager', $record->sourceKey);
        $this->assertSame('abm-guid-1', $record->sourceId);
        $this->assertSame('C02XL1234567', $record->hardwareSerial);
        // Default hardwareModel is partNumber (always populated per
        // ABM device). Marketing name (deviceModel) flows via the
        // abm_marketing_name extra so admins can route it to
        // native:model instead when they prefer the friendly shape.
        $this->assertSame('MK1E3LL/A', $record->hardwareModel);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertNull($record->hostname);
        $this->assertNull($record->os);
        $this->assertSame('Mac', $record->extra['abm_product_family']);
        $this->assertSame('Spaceblack', $record->extra['abm_color']);
        $this->assertSame('W1234567', $record->extra['abm_order_number']);
        $this->assertSame('2025-08-15', $record->extra['abm_order_date']);
        $this->assertSame('AppleCustomer', $record->extra['abm_purchase_source_type']);
    }

    public function test_merges_mdm_server_name_from_linkages()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response([
                'data' => [
                    ['id' => 'server-A', 'attributes' => ['serverName' => 'Jamf Prod']],
                    ['id' => 'server-B', 'attributes' => ['serverName' => 'Kandji Loaner Fleet']],
                ],
            ]),
            'api-business.apple.com/v1/mdmServers/server-A/relationships/devices*' => Http::response([
                'data' => [['id' => 'device-1'], ['id' => 'device-2']],
            ]),
            'api-business.apple.com/v1/mdmServers/server-B/relationships/devices*' => Http::response([
                'data' => [['id' => 'device-3']],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'device-1', 'attributes' => ['serialNumber' => 'SN1', 'partNumber' => 'MK1', 'productFamily' => 'Mac']],
                    ['id' => 'device-3', 'attributes' => ['serialNumber' => 'SN3', 'partNumber' => 'MK3', 'productFamily' => 'iPhone']],
                    ['id' => 'device-99', 'attributes' => ['serialNumber' => 'SN99', 'partNumber' => 'MK99', 'productFamily' => 'iPad']],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $byId = collect($records)->keyBy('sourceId');
        $this->assertSame('Jamf Prod', $byId['device-1']->extra['abm_mdm_server']);
        $this->assertSame('Kandji Loaner Fleet', $byId['device-3']->extra['abm_mdm_server']);
        $this->assertNull($byId['device-99']->extra['abm_mdm_server']);
    }

    public function test_school_mode_targets_school_host()
    {
        $adapter = $this->configuredAdapter('school');

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-school.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-school.apple.com/v1/orgDevices*' => Http::response(['data' => []]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api-school.apple.com/'));
        Http::assertNotSent(fn ($request) => str_starts_with($request->url(), 'https://api-business.apple.com/'));
    }

    public function test_token_exchange_sends_signed_jwt_client_assertion()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/*' => Http::response(['data' => []]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), 'account.apple.com')) {
                return false;
            }
            $body = $request->body();
            // Body is form-encoded; check the assertion fields are present
            // and the JWT is well-formed (three base64url segments).
            if (! str_contains($body, 'client_assertion_type=urn')) {
                return false;
            }
            preg_match('/client_assertion=([^&]+)/', $body, $m);
            if (! isset($m[1])) {
                return false;
            }
            $parts = explode('.', urldecode($m[1]));

            return count($parts) === 3;
        });
    }

    public function test_product_family_filter_limits_pulled_devices()
    {
        $adapter = $this->configuredAdapter(productFamilies: 'Mac, iPad');

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-mac', 'attributes' => ['serialNumber' => 'MACSN', 'partNumber' => 'MK1', 'productFamily' => 'Mac']],
                    ['id' => 'g-iphone', 'attributes' => ['serialNumber' => 'IPSN', 'partNumber' => 'IP1', 'productFamily' => 'iPhone']],
                    ['id' => 'g-ipad', 'attributes' => ['serialNumber' => 'IPADSN', 'partNumber' => 'IPD1', 'productFamily' => 'iPad']],
                    ['id' => 'g-watch', 'attributes' => ['serialNumber' => 'WSN', 'partNumber' => 'WT1', 'productFamily' => 'Watch']],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $ids = array_map(fn ($r) => $r->sourceId, $records);
        sort($ids);
        $this->assertSame(['g-ipad', 'g-mac'], $ids);
    }

    public function test_product_family_filter_is_case_insensitive()
    {
        $adapter = $this->configuredAdapter(productFamilies: 'MAC');

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-mac', 'attributes' => ['serialNumber' => 'S1', 'partNumber' => 'M1', 'productFamily' => 'Mac']],
                    ['id' => 'g-phone', 'attributes' => ['serialNumber' => 'S2', 'partNumber' => 'P1', 'productFamily' => 'iPhone']],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $this->assertSame('g-mac', $records[0]->sourceId);
    }

    public function test_blank_product_family_filter_passes_every_device_through()
    {
        $adapter = $this->configuredAdapter(productFamilies: '');

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-1', 'attributes' => ['serialNumber' => 'S1', 'partNumber' => 'P1', 'productFamily' => 'Mac']],
                    ['id' => 'g-2', 'attributes' => ['serialNumber' => 'S2', 'partNumber' => 'P2', 'productFamily' => 'iPhone']],
                    ['id' => 'g-3', 'attributes' => ['serialNumber' => 'S3', 'partNumber' => 'P3', 'productFamily' => 'Vision']],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(3, $records);
    }

    public function test_saveconfig_clears_family_filter_on_blank_submit()
    {
        $adapter = $this->configuredAdapter(productFamilies: 'Mac,iPhone');

        // Simulate an admin re-saving the form with an empty filter
        // to switch back to "sync every family."
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'apple_business_manager_url' => '',
            'apple_business_manager_product_family_filter' => '',
        ]);
        $adapter->saveConfig($request);

        $stored = SyncAdapterConfig::get(
            SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail()->id,
            'product_family_filter',
        );
        $this->assertSame('', $stored);
    }

    public function test_survives_mdm_server_map_failure_and_still_yields_records()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['error' => 'boom'], 500),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'guid-x', 'attributes' => ['serialNumber' => 'SNX', 'partNumber' => 'MKX', 'productFamily' => 'Mac']],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        // Record still comes through; mdm_server just null.
        $this->assertCount(1, $records);
        $this->assertNull($records[0]->extra['abm_mdm_server']);
    }

    private function configuredAdapter(string $mode = 'business', ?string $productFamilies = null): AppleBusinessManagerAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mode', $mode);
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client-id');
        SyncAdapterConfig::put($instance->id, 'key_id', 'stub-key-id');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));
        if ($productFamilies !== null) {
            SyncAdapterConfig::put($instance->id, 'product_family_filter', $productFamilies);
        }

        return new AppleBusinessManagerAdapter($instance->fresh());
    }
}
