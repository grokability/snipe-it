<?php

namespace Tests\Feature\SyncAdapters\Levelio;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Levelio\LevelioAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Level.io adapter through SyncAdapter.
 * Mocks the Level.io v2 REST API, asserts assets + asset_external_sources
 * land correctly, and that cursor pagination terminates on `has_more=false`.
 */
class LevelioAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_level_and_creates_assets(): void
    {
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::sequence()
                ->push([
                    'data' => [
                        $this->levelDevice(id: 'dev-aaa', hostname: 'workstation-01', model: 'MacBook Pro'),
                        $this->levelDevice(id: 'dev-bbb', hostname: 'workstation-02', model: 'MacBook Air'),
                    ],
                    'has_more' => false,
                ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'levelio', 'external_id' => 'dev-aaa']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'levelio', 'external_id' => 'dev-bbb']);
        $this->assertDatabaseHas('assets', ['name' => 'workstation-01']);
    }

    public function test_normalized_record_carries_expected_fields(): void
    {
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::sequence()
                ->push([
                    'data' => [
                        $this->levelDevice(
                            id: 'dev-ccc',
                            hostname: 'design-mbp',
                            model: 'MacBook Pro (16-inch, M3)',
                            serial_number: 'ABC123',
                            manufacturer: 'Apple Inc.',
                            platform: 'Mac',
                            operating_system: [
                                'full_operating_system' => 'macOS 14.2.1 (23C71)',
                                'major_version' => '14.0',
                                'minor_version' => '14.2.1',
                            ],
                            network_interfaces: [
                                ['mac_address' => '00:11:22:33:44:55', 'ip_addresses' => ['192.168.1.10']],
                            ],
                            last_seen_at: '2026-01-15T10:00:00.000Z',
                            last_logged_in_user: 'jane.doe',
                            group_id: 'grp-eng',
                            tags: ['Engineering', 'Laptops'],
                        ),
                    ],
                    'has_more' => false,
                ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('levelio', $record->sourceKey);
        $this->assertSame('dev-ccc', $record->sourceId);
        $this->assertSame('design-mbp', $record->hostname);
        $this->assertSame('MacBook Pro (16-inch, M3)', $record->hardwareModel);
        $this->assertSame('ABC123', $record->hardwareSerial);
        $this->assertSame('Apple Inc.', $record->manufacturer);
        $this->assertSame('macOS 14.2.1 (23C71)', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertSame('00:11:22:33:44:55', $record->primaryMac);
        $this->assertSame('192.168.1.10', $record->primaryIp);
        $this->assertSame('jane.doe', $record->assignedUserName);
        $this->assertSame('grp-eng', $record->vendorGroupId);
        $this->assertSame('Engineering, Laptops', $record->extra['levelio_tags']);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_follows_cursor_pagination_until_has_more_false(): void
    {
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::sequence()
                ->push([
                    'data' => [
                        $this->levelDevice(id: 'page1-a', hostname: 'host-a'),
                        $this->levelDevice(id: 'page1-b', hostname: 'host-b'),
                    ],
                    'has_more' => true,
                ])
                ->push([
                    'data' => [
                        $this->levelDevice(id: 'page2-a', hostname: 'host-c'),
                    ],
                    'has_more' => false,
                ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(3, $records);
        $this->assertSame(['page1-a', 'page1-b', 'page2-a'], array_map(fn ($r) => $r->sourceId, $records));

        // Second request carried the cursor from the tail of the first page.
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v2/devices')
                && str_contains($request->url(), 'starting_after=page1-b');
        });
    }

    public function test_falls_back_to_motherboard_fields_when_root_manufacturer_and_model_are_blank(): void
    {
        // Level.io's agent sources the top-level `manufacturer` and
        // `model` from either DMI or I/O Registry depending on the
        // platform, and one is sometimes empty on a given host. The
        // motherboard section carries the fallback pair so the
        // normalized record still has something to seed the AssetModel
        // from.
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::response([
                'data' => [
                    [
                        'id' => 'mb-fallback',
                        'hostname' => 'desktop-01',
                        'serial_number' => 'MB-SN-1',
                        'manufacturer' => null,
                        'model' => null,
                        'motherboard' => [
                            'manufacturer' => 'Dell Inc.',
                            'model' => 'OptiPlex 7090',
                        ],
                    ],
                ],
                'has_more' => false,
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('Dell Inc.', $records[0]->manufacturer);
        $this->assertSame('OptiPlex 7090', $records[0]->hardwareModel);
    }

    public function test_picks_interface_with_mac_and_ip_over_virtual_adapters(): void
    {
        // Level reports every NIC the host enumerates, including
        // virtual, VPN, and loopback adapters. The primary pick skips
        // interfaces without a usable IP so dashboards show the
        // real wired / wireless adapter rather than a Hyper-V vSwitch
        // or TAP interface that happens to come first in the list.
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::response([
                'data' => [
                    $this->levelDevice(
                        id: 'nic-pick',
                        hostname: 'vm-host',
                        network_interfaces: [
                            ['mac_address' => 'aa:aa:aa:aa:aa:aa', 'ip_addresses' => []],
                            ['mac_address' => 'bb:bb:bb:bb:bb:bb', 'ip_addresses' => ['10.0.0.5']],
                        ],
                    ),
                ],
                'has_more' => false,
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('bb:bb:bb:bb:bb:bb', $records[0]->primaryMac);
        $this->assertSame('10.0.0.5', $records[0]->primaryIp);
    }

    public function test_hardware_and_security_extras_populate_from_include_sections(): void
    {
        // Vendor payload mirrors the live Level.io v2 shape with
        // include_cpus / include_memory / include_disks /
        // include_security turned on. Verifies the adapter's hardware
        // extras pull from the expected spots so admins mapping them
        // to Snipe-IT custom fields see populated values rather than
        // null.
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::response([
                'data' => [
                    [
                        'id' => 'hw-1',
                        'hostname' => 'hw-host',
                        'serial_number' => 'HW-SN-1',
                        'manufacturer' => 'Dell',
                        'model' => 'OptiPlex 5070',
                        'platform' => 'Windows',
                        'total_memory' => 17179869184,
                        'cpu_cores' => 6,
                        'cpus' => [
                            [
                                'model' => 'Intel(R) Core(TM) i5-9500 CPU @ 3.00GHz',
                                'clock_speed' => 3000,
                                'cores' => 6,
                            ],
                        ],
                        'disks' => [
                            ['disk_type' => 'SSD', 'size' => 274877906944],
                            ['disk_type' => 'SSD', 'size' => 549755813888],
                        ],
                        'disk_partitions' => [
                            ['mount_point' => 'C:', 'size' => 274877906944, 'free_space' => 107374182400],
                        ],
                        'security' => [
                            'score' => 90,
                            'risk' => 'low_risk',
                            'firewall_enabled' => true,
                        ],
                    ],
                ],
                'has_more' => false,
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $extras = $records[0]->extra;

        $this->assertSame('Intel(R) Core(TM) i5-9500 CPU @ 3.00GHz', $extras['levelio_cpu_model']);
        $this->assertSame(6, $extras['levelio_cpu_cores']);
        $this->assertSame('16.0', $extras['levelio_total_memory_gb']);
        $this->assertSame('768.0', $extras['levelio_disk_total_gb']);
        $this->assertSame('100.0', $extras['levelio_disk_free_gb']);
        $this->assertSame(90, $extras['levelio_security_score']);
        $this->assertSame('low_risk', $extras['levelio_security_risk']);
    }

    public function test_sends_api_key_on_authorization_header_without_bearer_prefix(): void
    {
        $adapter = $this->configuredLevelio();

        Http::fake([
            '*/v2/devices*' => Http::response(['data' => [], 'has_more' => false]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'fake-level-api-key');
        });
    }

    private function configuredLevelio(): LevelioAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'levelio')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://api.level.io');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-level-api-key'));

        return new LevelioAdapter($instance->fresh());
    }

    /**
     * @param  array<string, mixed>|null  $operating_system
     * @param  array<int, array<string, mixed>>|null  $network_interfaces
     * @param  array<int, array<string, mixed>>|null  $tags
     * @return array<string, mixed>
     */
    private function levelDevice(
        string $id,
        string $hostname = 'host',
        string $model = 'MacBook',
        ?string $serial_number = null,
        ?string $manufacturer = 'Apple Inc.',
        ?string $platform = 'Mac',
        ?array $operating_system = null,
        ?array $network_interfaces = null,
        ?string $last_seen_at = null,
        ?string $last_logged_in_user = null,
        ?string $group_id = null,
        ?array $tags = null,
    ): array {
        return [
            'id' => $id,
            'hostname' => $hostname,
            'serial_number' => $serial_number,
            'manufacturer' => $manufacturer,
            'model' => $model,
            'platform' => $platform,
            'operating_system' => $operating_system,
            'network_interfaces' => $network_interfaces ?? [],
            'last_seen_at' => $last_seen_at,
            'last_logged_in_user' => $last_logged_in_user,
            'group_id' => $group_id,
            'tags' => $tags ?? [],
            'notes' => null,
            'role' => 'workstation',
            'online' => true,
        ];
    }
}
