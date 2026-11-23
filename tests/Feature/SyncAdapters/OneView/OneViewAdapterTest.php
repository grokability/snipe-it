<?php

namespace Tests\Feature\SyncAdapters\OneView;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\OneView\OneViewAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OneViewAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pull_exchanges_session_token_negotiates_api_version_and_streams_members(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400, 'currentVersion' => 5200]),
            '*/rest/server-hardware*' => Http::sequence()
                ->push($this->serverHardwarePage([$this->samplePayloadMembers()[0]], nextPageUri: '/rest/server-hardware?count=1&start=1'))
                ->push($this->serverHardwarePage([$this->samplePayloadMembers()[1]], nextPageUri: null)),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', [
            'source' => 'oneview',
            'external_id' => '00000000-0000-0000-0000-000000000001',
        ]);
        $this->assertDatabaseHas('asset_external_sources', [
            'source' => 'oneview',
            'external_id' => '00000000-0000-0000-0000-000000000002',
        ]);
        $this->assertDatabaseHas('assets', ['name' => 'DL320 Gen12 SN0000000001']);
        $this->assertDatabaseHas('assets', ['name' => 'DL320 Gen12 SN0000000002']);
    }

    public function test_auth_header_is_the_session_id_and_api_version_header_is_pinned(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400, 'currentVersion' => 5200]),
            '*/rest/server-hardware*' => Http::response($this->serverHardwarePage([])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/rest/server-hardware')) {
                return false;
            }

            return $request->hasHeader('Auth', 'fake-session-token')
                && $request->hasHeader('X-API-Version', '2400');
        });
    }

    public function test_login_fires_once_per_pull_regardless_of_page_count(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::sequence()
                ->push($this->serverHardwarePage([$this->samplePayloadMembers()[0]], nextPageUri: '/rest/server-hardware?count=1&start=1'))
                ->push($this->serverHardwarePage([$this->samplePayloadMembers()[1]], nextPageUri: null)),
        ]);

        iterator_to_array($adapter->pull());

        $loginCalls = 0;
        Http::assertSent(function ($request) use (&$loginCalls) {
            if (str_contains($request->url(), '/rest/login-sessions')) {
                $loginCalls++;
            }

            return true;
        });

        $this->assertSame(1, $loginCalls, 'Session token should be exchanged once per pull');
    }

    public function test_management_ip_lands_in_extras_not_primary_ip(): void
    {
        // Convention call from #20115: mpIpAddress is the iLO (out-of-
        // band management) IP, not the production NIC. Admins would be
        // surprised if "IP address" on the asset was the management IP,
        // so we route it through extras and leave primary_ip null.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::response($this->serverHardwarePage([$this->samplePayloadMembers()[0]])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $record = $records[0];

        $this->assertNull($record->primaryIp, 'primaryIp must stay null, iLO IP belongs in extras.');
        $this->assertSame('192.0.2.1', $record->extra['oneview_management_ip']);
        $this->assertSame('ILOSN0000000001.ipmi.example.org', $record->extra['oneview_management_dns']);
    }

    public function test_powered_off_cores_and_speed_are_dropped_not_overwritten_as_zero(): void
    {
        // Sample's second member is powered off. OneView reports
        // processorCoreCount=0 and processorSpeedMhz=null for it,
        // which we treat as "unknown" rather than letting a 0 wipe a
        // previously-known-good value.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::response($this->serverHardwarePage([$this->samplePayloadMembers()[1]])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $extra = $records[0]->extra;
        $this->assertNull($extra['oneview_cpu_cores'], 'cores=0 from a powered-off server must become null.');
        $this->assertNull($extra['oneview_cpu_speed_mhz'], 'speed=null from a powered-off server must stay null.');
        $this->assertSame(1, $extra['oneview_cpu_count'], 'positive cpu_count must survive.');
        $this->assertSame(131072, $extra['oneview_memory_mb'], 'positive memory must survive.');
    }

    public function test_empty_asset_tag_becomes_null_so_snipe_it_auto_generates(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::response($this->serverHardwarePage([$this->samplePayloadMembers()[0]])),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertNull($records[0]->assetTag);
    }

    public function test_first_non_zero_ethernet_mac_from_port_map_is_used(): void
    {
        // Sample has two ports on the same slot with the same MAC
        // (OneView assigns MACs to the slot even when neither physical
        // cable is wired). First usable MAC wins.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::response($this->serverHardwarePage([$this->samplePayloadMembers()[0]])),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('00:00:00:00:00:01', $records[0]->primaryMac);
    }

    public function test_all_zero_mac_placeholder_is_skipped(): void
    {
        $adapter = $this->configuredAdapter();

        $memberWithZeroMac = $this->samplePayloadMembers()[0];
        $memberWithZeroMac['portMap']['deviceSlots'][0]['physicalPorts'][0]['mac'] = '00:00:00:00:00:00';
        $memberWithZeroMac['portMap']['deviceSlots'][0]['physicalPorts'][1]['mac'] = 'aa:bb:cc:dd:ee:ff';

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::response($this->serverHardwarePage([$memberWithZeroMac])),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('aa:bb:cc:dd:ee:ff', $records[0]->primaryMac, 'Zero-MAC placeholder must be skipped.');
    }

    public function test_pagination_follows_next_page_uri_verbatim(): void
    {
        // OneView's nextPageUri is an absolute path. The client should
        // follow whatever the appliance hands back rather than
        // recomputing start+count on the client side, so appliance-
        // enforced page-size tweaks don't desync pagination.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/rest/login-sessions' => Http::response(['sessionID' => 'fake-session-token']),
            '*/rest/version' => Http::response(['minimumVersion' => 2400]),
            '*/rest/server-hardware*' => Http::sequence()
                ->push($this->serverHardwarePage([$this->samplePayloadMembers()[0]], nextPageUri: '/rest/server-hardware?count=25&start=25'))
                ->push($this->serverHardwarePage([$this->samplePayloadMembers()[1]], nextPageUri: null)),
        ]);

        iterator_to_array($adapter->pull());

        $secondPageSeen = false;
        Http::assertSent(function ($request) use (&$secondPageSeen) {
            if (str_contains($request->url(), 'count=25&start=25')) {
                $secondPageSeen = true;
            }

            return true;
        });

        $this->assertTrue($secondPageSeen, 'Client must follow the appliance-provided nextPageUri verbatim.');
    }

    private function configuredAdapter(): OneViewAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'oneview')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://oneview.example.com');
        SyncAdapterConfig::put($instance->id, 'username', 'snipe-reader');
        SyncAdapterConfig::put($instance->id, 'password', Crypt::encrypt('fake-password'));

        return new OneViewAdapter($instance->fresh());
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array<string, mixed>
     */
    private function serverHardwarePage(array $members, ?string $nextPageUri = null): array
    {
        return [
            'type' => 'server-hardware-list-1',
            'category' => 'server-hardware',
            'count' => count($members),
            'members' => $members,
            'nextPageUri' => $nextPageUri,
            'prevPageUri' => null,
            'start' => 0,
            'total' => count($members),
        ];
    }

    /**
     * Exactly the sanitized payload from GH #20115, minus the outer
     * wrapper. Kept verbatim so field shapes stay honest to what a
     * real HPE OneView appliance returns.
     *
     * @return array<int, array<string, mixed>>
     */
    private function samplePayloadMembers(): array
    {
        return [
            [
                'type' => 'server-hardware-1',
                'name' => 'ILOSN0000000001.ipmi.example.org',
                'state' => 'ProfileApplied',
                'stateReason' => 'NotApplicable',
                'assetTag' => '',
                'category' => 'server-hardware',
                'created' => '2026-06-08T13:46:09.485Z',
                'description' => null,
                'eTag' => '1795359921155',
                'formFactor' => '1U',
                'licensingIntent' => 'OneView',
                'locationUri' => null,
                'memoryMb' => 131072,
                'model' => 'HPE ProLiant Compute DL320 Gen12',
                'modified' => '2026-11-22T15:05:21.155Z',
                'mpDnsName' => 'ILOSN0000000001.ipmi.example.org',
                'mpFirmwareVersion' => '1.25.02 Oct 14 2026',
                'mpIpAddress' => '192.0.2.1',
                'mpModel' => 'iLO7',
                'partNumber' => 'P71437-B21',
                'portMap' => [
                    'deviceSlots' => [
                        [
                            'deviceName' => 'BCM57414',
                            'location' => 'Ocp',
                            'oaSlotNumber' => 1,
                            'physicalPorts' => [
                                ['interconnectPort' => 0, 'interconnectUri' => null, 'mac' => '00:00:00:00:00:01', 'portNumber' => 1, 'type' => 'Ethernet', 'virtualPorts' => []],
                                ['interconnectPort' => 0, 'interconnectUri' => null, 'mac' => '00:00:00:00:00:01', 'portNumber' => 2, 'type' => 'Ethernet', 'virtualPorts' => []],
                            ],
                            'slotNumber' => 20,
                        ],
                    ],
                ],
                'position' => 0,
                'powerLock' => false,
                'powerState' => 'Off',
                'processorCoreCount' => 12,
                'processorCount' => 1,
                'processorSpeedMhz' => 4000,
                'processorType' => 'Intel(R) Xeon(R) 6505P',
                'refreshState' => 'NotRefreshing',
                'romVersion' => 'U71 v1.90 (08/20/2026)',
                'serialNumber' => 'SN0000000001',
                'serverGroupUri' => null,
                'serverHardwareTypeUri' => '/rest/server-hardware-types/00000000-0000-0000-0000-00000000000T',
                'serverProfileUri' => '/rest/server-profiles/00000000-0000-0000-0000-0000000000P1',
                'shortModel' => 'DL320 Gen12',
                'signature' => null,
                'status' => 'Warning',
                'uri' => '/rest/server-hardware/00000000-0000-0000-0000-000000000001',
                'uuid' => '00000000-0000-0000-0000-000000000001',
                'virtualSerialNumber' => null,
                'virtualUuid' => null,
            ],
            [
                'type' => 'server-hardware-1',
                'name' => 'ILOSN0000000002.ipmi.example.org',
                'state' => 'ProfileApplied',
                'stateReason' => 'NotApplicable',
                'assetTag' => '',
                'category' => 'server-hardware',
                'created' => '2026-06-08T13:46:46.024Z',
                'description' => null,
                'eTag' => '1795393597016',
                'formFactor' => '1U',
                'licensingIntent' => 'OneView',
                'locationUri' => null,
                'memoryMb' => 131072,
                'model' => 'HPE ProLiant Compute DL320 Gen12',
                'modified' => '2026-11-23T00:26:37.016Z',
                'mpDnsName' => 'ILOSN0000000002.ipmi.example.org',
                'mpFirmwareVersion' => '1.25.02 Oct 14 2026',
                'mpIpAddress' => '192.0.2.2',
                'mpModel' => 'iLO7',
                'partNumber' => 'P71437-B21',
                'portMap' => [
                    'deviceSlots' => [
                        [
                            'deviceName' => 'BCM57414',
                            'location' => 'Ocp',
                            'oaSlotNumber' => 1,
                            'physicalPorts' => [
                                ['interconnectPort' => 0, 'interconnectUri' => null, 'mac' => '00:00:00:00:00:02', 'portNumber' => 1, 'type' => 'Ethernet', 'virtualPorts' => []],
                                ['interconnectPort' => 0, 'interconnectUri' => null, 'mac' => '00:00:00:00:00:02', 'portNumber' => 2, 'type' => 'Ethernet', 'virtualPorts' => []],
                            ],
                            'slotNumber' => 20,
                        ],
                    ],
                ],
                'position' => 0,
                'powerLock' => false,
                'powerState' => 'On',
                'processorCoreCount' => 0,
                'processorCount' => 1,
                'processorSpeedMhz' => null,
                'processorType' => 'Intel(R) Xeon(R) 6505P',
                'refreshState' => 'NotRefreshing',
                'romVersion' => 'U71 v1.90 (08/20/2026)',
                'serialNumber' => 'SN0000000002',
                'serverGroupUri' => null,
                'serverHardwareTypeUri' => '/rest/server-hardware-types/00000000-0000-0000-0000-00000000000T',
                'serverProfileUri' => '/rest/server-profiles/00000000-0000-0000-0000-0000000000P2',
                'shortModel' => 'DL320 Gen12',
                'signature' => null,
                'status' => 'Warning',
                'uri' => '/rest/server-hardware/00000000-0000-0000-0000-000000000002',
                'uuid' => '00000000-0000-0000-0000-000000000002',
                'virtualSerialNumber' => null,
                'virtualUuid' => null,
            ],
        ];
    }
}
