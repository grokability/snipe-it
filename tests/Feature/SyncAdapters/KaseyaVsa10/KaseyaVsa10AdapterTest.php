<?php

namespace Tests\Feature\SyncAdapters\KaseyaVsa10;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\KaseyaVsa10\KaseyaVsa10Adapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Kaseya VSA 10 adapter through
 * SyncHostFromAdapter. Fakes the Basic-auth /api/v3/assets endpoint
 * and asserts extraction from the nested AssetInfo[] category shape.
 */
class KaseyaVsa10AdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_assets_from_vsa10_and_creates_snipe_it_assets()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                'Data' => [
                    $this->vsaAsset(identifier: 'guid-1', name: 'vsa-host-1', model: 'OptiPlex 7080'),
                    $this->vsaAsset(identifier: 'guid-2', name: 'vsa-host-2', model: 'Latitude 5420'),
                ],
                'Meta' => ['TotalCount' => 2, 'ResponseCode' => 200],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'kaseya_vsa10', 'external_id' => 'guid-1']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'kaseya_vsa10', 'external_id' => 'guid-2']);
    }

    public function test_normalized_record_extracts_hardware_and_os_from_asset_info_categories()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                'Data' => [
                    [
                        'Identifier' => 'guid-abc',
                        'Name' => 'vsa-desktop',
                        'GroupName' => 'Windows Workstations',
                        'Type' => 'windows',
                        'ClientVersion' => '5.1.2',
                        'LastSeenOnline' => '2026-08-11T15:10:06Z',
                        'PublicIpAddress' => '203.0.113.7',
                        'OrganizationId' => 42,
                        'Tags' => ['production'],
                        'LocalIpAddresses' => [
                            [
                                'Name' => 'Ethernet0',
                                'PhysicalAddress' => 'AA:BB:CC:DD:EE:FF',
                                'IpV4' => '10.0.0.5',
                                'IpV6' => null,
                            ],
                        ],
                        'AssetInfo' => [
                            [
                                'CategoryName' => 'System',
                                'CategoryData' => [
                                    'Manufacturer' => 'Dell Inc.',
                                    'Model' => 'OptiPlex 7080',
                                    'CPU' => 'Intel Core i7-10700',
                                    'Domain' => 'CORP',
                                ],
                            ],
                            [
                                'CategoryName' => 'BIOS',
                                'CategoryData' => [
                                    'Serial Number' => 'ABC123XYZ',
                                    'Manufacturer' => 'Dell Inc.',
                                    'Version' => '2.15.0',
                                ],
                            ],
                            [
                                'CategoryName' => 'Operating System',
                                'CategoryData' => [
                                    'Name' => 'Windows 11 Enterprise',
                                    'Version' => '10.0.22631.0',
                                ],
                            ],
                        ],
                    ],
                ],
                'Meta' => ['TotalCount' => 1, 'ResponseCode' => 200],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('kaseya_vsa10', $record->sourceKey);
        $this->assertSame('guid-abc', $record->sourceId);
        $this->assertSame('vsa-desktop', $record->hostname);
        $this->assertSame('ABC123XYZ', $record->hardwareSerial);
        $this->assertSame('OptiPlex 7080', $record->hardwareModel);
        $this->assertSame('Dell Inc.', $record->manufacturer);
        $this->assertSame('AA:BB:CC:DD:EE:FF', $record->primaryMac);
        $this->assertSame('203.0.113.7', $record->primaryIp);
        $this->assertSame('Windows 11 Enterprise', $record->os);
        $this->assertSame('10.0.22631.0', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
        $this->assertSame('42', $record->vendorGroupId);
        $this->assertSame('Intel Core i7-10700', $record->extra['kaseya_vsa10_cpu']);
        $this->assertSame('CORP', $record->extra['kaseya_vsa10_domain']);
        $this->assertSame('Windows Workstations', $record->extra['kaseya_vsa10_group']);
    }

    public function test_missing_asset_info_categories_yield_null_hardware_fields()
    {
        // An asset that hasn't reported a BIOS or Operating System
        // category yet (fresh agent install still auditing) should
        // still normalize cleanly. Fields that depend on missing
        // categories come back null and downstream saveOrFail paths
        // decide whether the record has enough to create a shell
        // asset.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                'Data' => [
                    [
                        'Identifier' => 'guid-fresh',
                        'Name' => 'freshly-enrolled',
                        'Type' => 'mac',
                        'AssetInfo' => [
                            [
                                'CategoryName' => 'System',
                                'CategoryData' => [
                                    'Manufacturer' => 'Apple Inc.',
                                    'Model' => 'MacBookPro18,3',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertSame('MacBookPro18,3', $record->hardwareModel);
        $this->assertSame('Apple Inc.', $record->manufacturer);
        $this->assertNull($record->hardwareSerial);
        $this->assertNull($record->osVersion);
        // OS falls back to the coarse Type when the OS category is absent.
        $this->assertSame('mac', $record->os);
    }

    public function test_falls_back_to_local_ip_when_public_missing()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                'Data' => [
                    [
                        'Identifier' => 'guid-lan-only',
                        'Name' => 'vsa-lan',
                        'LocalIpAddresses' => [
                            ['PhysicalAddress' => '11:22:33:44:55:66', 'IpV4' => '10.0.0.99', 'IpV6' => null],
                        ],
                        'AssetInfo' => [
                            [
                                'CategoryName' => 'System',
                                'CategoryData' => ['Manufacturer' => 'Foo', 'Model' => 'Bar'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertSame('10.0.0.99', $record->primaryIp);
    }

    public function test_sends_basic_auth_header_from_token_pair()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/assets*' => Http::response(['Data' => []]),
        ]);

        iterator_to_array($adapter->pull());

        $expected = 'Basic '.base64_encode('stub-token-id:fake-token-secret');
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', $expected));
    }

    public function test_handles_bare_array_response_shape()
    {
        // Belt-and-suspenders: if VSA ever returns a flat array
        // instead of {Data: [...]}, iterate it directly rather than
        // silently yielding zero records.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                $this->vsaAsset(identifier: 'guid-flat', name: 'flat-host', model: 'Generic'),
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $this->assertSame('guid-flat', $records[0]->sourceId);
    }

    private function configuredAdapter(): KaseyaVsa10Adapter
    {
        $instance = SyncAdapterInstance::where('slug', 'kaseya_vsa10')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://acme.vsax.net');
        SyncAdapterConfig::put($instance->id, 'token_id', Crypt::encrypt('stub-token-id'));
        SyncAdapterConfig::put($instance->id, 'token_secret', Crypt::encrypt('fake-token-secret'));

        return new KaseyaVsa10Adapter($instance->fresh());
    }

    /**
     * Minimal asset row with just enough fields for a shell Snipe-IT
     * asset save (Manufacturer + Model in the System category, and a
     * BIOS serial). Full field coverage is exercised in
     * test_normalized_record_extracts_hardware_and_os_from_asset_info_categories.
     *
     * @return array<string, mixed>
     */
    private function vsaAsset(string $identifier, string $name, string $model): array
    {
        return [
            'Identifier' => $identifier,
            'Name' => $name,
            'OrganizationId' => 1,
            'Type' => 'windows',
            'AssetInfo' => [
                [
                    'CategoryName' => 'System',
                    'CategoryData' => ['Manufacturer' => 'Test Corp', 'Model' => $model],
                ],
                [
                    'CategoryName' => 'BIOS',
                    'CategoryData' => ['Serial Number' => 'SN-'.$identifier],
                ],
            ],
        ];
    }
}
