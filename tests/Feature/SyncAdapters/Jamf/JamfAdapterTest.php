<?php

namespace Tests\Feature\SyncAdapters\Jamf;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

/**
 * End-to-end coverage for the Jamf Pro adapter through
 * SyncAdapter. Mocks the Jamf Pro API, asserts assets +
 * asset_external_sources land correctly, and that the totalCount /
 * pagination termination works.
 */
class JamfAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_computers_from_jamf_and_creates_assets()
    {
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jamfComputer(id: 42, name: 'lab-mac-01', model: 'iMac'),
                    $this->jamfComputer(id: 99, name: 'lab-mac-02', model: 'Mac mini'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => '42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => '99']);
        $this->assertDatabaseHas('assets', ['name' => 'lab-mac-01']);

        // Laravel's default array-shaped query encoding produces the bracketed
        // form and Jamf silently drops it, leaving every asset with
        // no hardware section (and no model id). The client hand-builds
        // the query string to force the API shape Jamf actually reads.
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/v1/computers-inventory')) {
                return false;
            }
            $url = $request->url();

            return str_contains($url, 'section=GENERAL')
                && str_contains($url, 'section=HARDWARE')
                && str_contains($url, 'section=OPERATING_SYSTEM')
                && str_contains($url, 'section=USER_AND_LOCATION')
                && ! str_contains($url, 'section%5B')
                && ! str_contains($url, 'section[');
        });
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jamfComputer(
                        id: 7,
                        name: 'design-mbp',
                        model: 'MacBook Pro (16-inch, M3)',
                        serialNumber: 'C02XYZ',
                        macAddress: 'aa:bb:cc:11:22:33',
                        osName: 'macOS',
                        osVersion: '14.2.1',
                        lastContactTime: '2026-01-15T10:00:00.000Z',
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('jamf', $record->sourceKey);
        $this->assertSame('7', $record->sourceId);
        $this->assertSame('design-mbp', $record->hostname);
        $this->assertSame('MacBook Pro (16-inch, M3)', $record->hardwareModel);
        $this->assertSame('C02XYZ', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:11:22:33', $record->primaryMac);
        $this->assertSame('macOS', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_computer_ram_and_boot_disk_storage_are_extracted()
    {
        /*
         * Jamf STORAGE section can list more than one disk when an
         * external drive is plugged in at inventory time. The boot disk
         * is the one whose partitions contain a partitionType=BOOT
         * entry. The adapter must land on the internal boot disk's size,
         * not the external drive's or the sum of both.
         */
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jamfComputer(
                        id: 42,
                        name: 'design-mbp',
                        model: 'MacBook Pro',
                        totalRamMegabytes: 32768,
                        storageDisks: [
                            [
                                'device' => 'disk0',
                                'sizeMegabytes' => 1000000,
                                'partitions' => [
                                    ['name' => 'Macintosh HD', 'partitionType' => 'BOOT'],
                                    ['name' => 'Recovery', 'partitionType' => 'RECOVERY'],
                                ],
                            ],
                            [
                                'device' => 'disk2',
                                'sizeMegabytes' => 4000000,
                                'partitions' => [
                                    ['name' => 'External SSD', 'partitionType' => 'OTHER'],
                                ],
                            ],
                        ],
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame(32.77, $records[0]->extra['jamf_ram_gb']);
        $this->assertSame(
            1000,
            $records[0]->extra['jamf_storage_gb'],
            'Boot disk (disk0, with partitionType=BOOT) wins over the larger external drive.',
        );
    }

    public function test_storage_falls_back_to_first_disk_when_no_boot_partition_tagged()
    {
        /*
         * Older Jamf versions don't always tag a partition as BOOT. In
         * that case fall back to disks[0] because on every modern Mac
         * the internal SSD reports as disk0 and external drives come
         * later. The fallback keeps RAM + storage reporting useful
         * without pretending we have signal we don't.
         */
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jamfComputer(
                        id: 42,
                        storageDisks: [
                            [
                                'device' => 'disk0',
                                'sizeMegabytes' => 500000,
                                'partitions' => [
                                    ['name' => 'Untagged', 'partitionType' => 'OTHER'],
                                ],
                            ],
                        ],
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame(500, $records[0]->extra['jamf_storage_gb']);
    }

    public function test_storage_is_null_when_jamf_returns_no_storage_section()
    {
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jamfComputer(id: 42, totalRamMegabytes: null, storageDisks: null),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertNull($records[0]->extra['jamf_storage_gb']);
        $this->assertNull($records[0]->extra['jamf_ram_gb']);
    }

    public function test_mobile_record_includes_storage_capacity()
    {
        $adapter = $this->configuredJamfAdapter(includeMobile: true);

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response(['totalCount' => 0, 'results' => []]),
            '*/api/v2/mobile-devices*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    [
                        'id' => '77',
                        'name' => 'law-ipad-01',
                        'udid' => 'jamf-mobile-udid-77',
                        'serialNumber' => 'DMPXYZ',
                        'assetTag' => null,
                        'model' => 'iPad Pro',
                        'modelIdentifier' => 'iPad13,1',
                        'deviceType' => 'ios',
                        'osType' => 'iOS',
                        'osVersion' => '17.4',
                        'wifiMacAddress' => null,
                        'ipAddress' => null,
                        'lastInventoryUpdateTimestamp' => null,
                        'managed' => true,
                        'supervised' => true,
                        'capacityMb' => 256000,
                        'location' => ['username' => null, 'emailAddress' => null],
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame(256, $records[0]->extra['jamf_mobile_storage_gb']);
    }

    public function test_mobile_devices_pulled_when_toggle_is_on()
    {
        $adapter = $this->configuredJamfAdapter(includeMobile: true);

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [$this->jamfComputer(id: 42, name: 'lab-mac-01', model: 'iMac')],
            ]),
            '*/api/v2/mobile-devices*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jamfMobileDevice(id: 10, name: 'kiosk-ipad-01', model: 'iPad Pro', serialNumber: 'DMPXYZ', osType: 'iOS', osVersion: '17.4'),
                    $this->jamfMobileDevice(id: 11, name: 'conf-apple-tv', model: 'Apple TV 4K', serialNumber: 'C07ABC', osType: 'tvOS', osVersion: '17.1', deviceType: 'tvos'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        // Three distinct external_sources rows: one bare-numeric for the
        // computer, two `mobile:`-prefixed for the devices. Prefix on
        // mobile keeps the two Jamf id namespaces from colliding on
        // the (source, external_id) uniqueness constraint.
        $this->assertDatabaseCount('asset_external_sources', 3);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => '42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => 'mobile:10']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => 'mobile:11']);

        $this->assertDatabaseHas('assets', ['name' => 'kiosk-ipad-01']);
        $this->assertDatabaseHas('assets', ['name' => 'conf-apple-tv']);
    }

    public function test_mobile_devices_skipped_when_toggle_is_off()
    {
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [$this->jamfComputer(id: 42, name: 'lab-mac-01', model: 'iMac')],
            ]),
            '*/api/v2/mobile-devices*' => Http::response([
                'totalCount' => 1,
                'results' => [$this->jamfMobileDevice(id: 10, name: 'should-not-sync')],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 1);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => '42']);
        $this->assertDatabaseMissing('assets', ['name' => 'should-not-sync']);

        // Toggle off means we don't bill Jamf for a mobile pull at all.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/v2/mobile-devices'));
    }

    public function test_mobile_record_carries_expected_fields()
    {
        $adapter = $this->configuredJamfAdapter(includeMobile: true);

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 0,
                'results' => [],
            ]),
            '*/api/v2/mobile-devices*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jamfMobileDevice(
                        id: 55,
                        name: 'ipad-55',
                        model: 'iPad Air',
                        serialNumber: 'C02ABC',
                        osType: 'iOS',
                        osVersion: '17.5',
                        wifiMacAddress: 'aa:bb:cc:dd:ee:ff',
                        ipAddress: '10.0.0.55',
                        lastInventoryUpdateTimestamp: '2026-02-15T10:00:00.000Z',
                        locationUsername: 'jdoe',
                        locationEmail: 'jdoe@example.com',
                        deviceType: 'ios',
                        managed: true,
                        supervised: true,
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('jamf', $record->sourceKey);
        $this->assertSame('mobile:55', $record->sourceId);
        $this->assertSame('ipad-55', $record->hostname);
        $this->assertSame('iPad Air', $record->hardwareModel);
        $this->assertSame('C02ABC', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('10.0.0.55', $record->primaryIp);
        $this->assertSame('iOS', $record->os);
        $this->assertSame('17.5', $record->osVersion);
        $this->assertSame('jdoe', $record->assignedUserName);
        $this->assertSame('jdoe@example.com', $record->assignedUserEmail);
        $this->assertNotNull($record->lastSeen);
        $this->assertSame('ios', $record->extra['jamf_mobile_device_type']);
        $this->assertTrue($record->extra['jamf_mobile_managed']);
        $this->assertTrue($record->extra['jamf_mobile_supervised']);
    }

    public function test_retries_connection_error_on_a_paginated_page()
    {
        // Regression test for #19853. A real customer's Jamf pull of
        // ~3,800 computers aborted on page 37 of 38 with
        // "cURL error 18: transfer closed with outstanding read data
        // remaining." Laravel surfaces that as ConnectionException from
        // the HTTP client, which the trait retries. Fake Sleep so the
        // backoff doesn't actually block the test.
        Sleep::fake();

        $adapter = $this->configuredJamfAdapter();

        $attempts = 0;
        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => function () use (&$attempts) {
                $attempts++;
                if ($attempts === 1) {
                    throw new ConnectionException('cURL error 18: transfer closed with outstanding read data remaining');
                }

                return Http::response([
                    'totalCount' => 1,
                    'results' => [$this->jamfComputer(id: 1, name: 'retried-mac')],
                ]);
            },
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame('retried-mac', $records[0]->hostname);
        $this->assertSame(2, $attempts, 'JamfClient should have retried the dropped page once.');
    }

    public function test_retries_on_429_and_honors_retry_after_header()
    {
        // Jamf Pro doesn't throttle with 429 by default, but Graph does
        // and the trait is shared, so prove Jamf's wrapper reads
        // Retry-After the same way.
        Sleep::fake();

        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::sequence()
                ->push(['detail' => 'rate limited'], 429, ['Retry-After' => '1'])
                ->push([
                    'totalCount' => 1,
                    'results' => [$this->jamfComputer(id: 2, name: 'throttled-mac')],
                ], 200),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame('throttled-mac', $records[0]->hostname);
    }

    public function test_group_filter_narrows_computers_by_smart_group_membership()
    {
        $adapter = $this->configuredJamfAdapter(groupFilter: 'Law School Macs, 7');

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            // Specific member-detail URLs first so Laravel's first-match
            // fake dispatch doesn't catch them in the broader
            // /computergroups list pattern below.
            '*/JSSResource/computergroups/id/3' => Http::response([
                'computer_group' => [
                    'id' => 3,
                    'name' => 'Law School Macs',
                    'computers' => [
                        ['id' => 42, 'name' => 'law-mac-01'],
                    ],
                ],
            ]),
            '*/JSSResource/computergroups/id/7' => Http::response([
                'computer_group' => [
                    'id' => 7,
                    'name' => 'Faculty Loaners',
                    'computers' => [
                        ['id' => 99, 'name' => 'loaner-mac-01'],
                    ],
                ],
            ]),
            '*/JSSResource/computergroups' => Http::response([
                'computer_groups' => [
                    ['id' => 3, 'name' => 'Law School Macs', 'is_smart' => true],
                    ['id' => 7, 'name' => 'Faculty Loaners', 'is_smart' => true],
                    ['id' => 11, 'name' => 'Medical School Macs', 'is_smart' => true],
                ],
            ]),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 3,
                'results' => [
                    $this->jamfComputer(id: 42, name: 'law-mac-01', model: 'MacBook Pro'),
                    $this->jamfComputer(id: 99, name: 'loaner-mac-01', model: 'MacBook Air'),
                    $this->jamfComputer(id: 500, name: 'med-mac-01', model: 'iMac'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(2, $records);
        $hostnames = array_map(fn ($r) => $r->hostname, $records);
        $this->assertContains('law-mac-01', $hostnames);
        $this->assertContains('loaner-mac-01', $hostnames);
        $this->assertNotContains('med-mac-01', $hostnames);
    }

    public function test_group_filter_also_narrows_mobile_devices_when_mobile_toggle_on()
    {
        $adapter = $this->configuredJamfAdapter(includeMobile: true, groupFilter: 'Law School');

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/JSSResource/computergroups/id/3' => Http::response([
                'computer_group' => [
                    'id' => 3,
                    'name' => 'Law School',
                    'computers' => [
                        ['id' => 42, 'name' => 'law-mac-01'],
                    ],
                ],
            ]),
            '*/JSSResource/computergroups' => Http::response([
                'computer_groups' => [
                    ['id' => 3, 'name' => 'Law School', 'is_smart' => true],
                ],
            ]),
            '*/JSSResource/mobiledevicegroups/id/8' => Http::response([
                'mobile_device_group' => [
                    'id' => 8,
                    'name' => 'Law School',
                    'mobile_devices' => [
                        ['id' => 10, 'name' => 'law-ipad-01'],
                    ],
                ],
            ]),
            '*/JSSResource/mobiledevicegroups' => Http::response([
                'mobile_device_groups' => [
                    ['id' => 8, 'name' => 'Law School', 'is_smart' => true],
                ],
            ]),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jamfComputer(id: 42, name: 'law-mac-01', model: 'MacBook Pro'),
                    $this->jamfComputer(id: 500, name: 'med-mac-01', model: 'iMac'),
                ],
            ]),
            '*/api/v2/mobile-devices*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jamfMobileDevice(id: 10, name: 'law-ipad-01', model: 'iPad Pro'),
                    $this->jamfMobileDevice(id: 20, name: 'med-ipad-01', model: 'iPad'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $hostnames = array_map(fn ($r) => $r->hostname, $records);
        $this->assertContains('law-mac-01', $hostnames);
        $this->assertContains('law-ipad-01', $hostnames);
        $this->assertNotContains('med-mac-01', $hostnames);
        $this->assertNotContains('med-ipad-01', $hostnames);
    }

    public function test_blank_group_filter_skips_group_resolution_calls()
    {
        $adapter = $this->configuredJamfAdapter(groupFilter: '');

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [$this->jamfComputer(id: 42, name: 'any-mac', model: 'iMac')],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/JSSResource/computergroups'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/JSSResource/mobiledevicegroups'));
    }

    public function test_group_filter_unresolvable_entry_does_not_crash_pull()
    {
        $adapter = $this->configuredJamfAdapter(groupFilter: 'Law School, Nonexistent Group');

        Http::fake([
            '*/api/oauth/token' => $this->jamfTokenResponse(),
            '*/JSSResource/computergroups/id/3' => Http::response([
                'computer_group' => [
                    'id' => 3,
                    'name' => 'Law School',
                    'computers' => [['id' => 42, 'name' => 'law-mac-01']],
                ],
            ]),
            '*/JSSResource/computergroups' => Http::response([
                'computer_groups' => [
                    ['id' => 3, 'name' => 'Law School', 'is_smart' => true],
                ],
            ]),
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [$this->jamfComputer(id: 42, name: 'law-mac-01', model: 'iMac')],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame('law-mac-01', $records[0]->hostname);
    }

    private function configuredJamfAdapter(bool $includeMobile = false, string $groupFilter = ''): JamfAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.jamfcloud.com');
        SyncAdapterConfig::put($instance->id, 'client_id', Crypt::encrypt('fake-jamf-client-id'));
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-jamf-client-secret'));
        SyncAdapterConfig::put($instance->id, 'include_mobile_devices', $includeMobile ? '1' : '0');
        SyncAdapterConfig::put($instance->id, 'group_filter', $groupFilter);

        return new JamfAdapter($instance->fresh());
    }

    /**
     * Jamf Pro's /api/oauth/token response shape. expires_in is
     * whatever the API Client is configured for. The client's bearer
     * cache honors it with a 30-second buffer before re-exchanging.
     * No return type hint because Http::response() inside a fake
     * handler resolves to a promise, not a bare Response.
     */
    private function jamfTokenResponse()
    {
        return Http::response([
            'access_token' => 'fake-jamf-access-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
    }

    /**
     * Shape of a /api/v2/mobile-devices list entry Jamf Pro returns.
     * Mobile responses are flatter than computer ones: no section
     * nesting, just a top-level field set per device.
     *
     * @return array<string, mixed>
     */
    private function jamfMobileDevice(
        int $id,
        string $name = 'ipad',
        string $model = 'iPad',
        ?string $serialNumber = null,
        ?string $osType = 'iOS',
        ?string $osVersion = null,
        ?string $wifiMacAddress = null,
        ?string $ipAddress = null,
        ?string $lastInventoryUpdateTimestamp = null,
        ?string $locationUsername = null,
        ?string $locationEmail = null,
        string $deviceType = 'ios',
        bool $managed = true,
        bool $supervised = false,
    ): array {
        return [
            'id' => (string) $id,
            'name' => $name,
            'udid' => 'jamf-mobile-udid-'.$id,
            'serialNumber' => $serialNumber,
            'assetTag' => null,
            'model' => $model,
            'modelIdentifier' => 'iPad'.$id,
            'deviceType' => $deviceType,
            'osType' => $osType,
            'osVersion' => $osVersion,
            'wifiMacAddress' => $wifiMacAddress,
            'ipAddress' => $ipAddress,
            'lastInventoryUpdateTimestamp' => $lastInventoryUpdateTimestamp,
            'managed' => $managed,
            'supervised' => $supervised,
            'location' => [
                'username' => $locationUsername,
                'emailAddress' => $locationEmail,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $storageDisks
     * @return array<string, mixed>
     */
    private function jamfComputer(
        int $id,
        string $name = 'computer',
        string $model = 'MacBook',
        ?string $serialNumber = null,
        ?string $macAddress = null,
        ?string $osName = 'macOS',
        ?string $osVersion = null,
        ?string $lastContactTime = null,
        ?int $totalRamMegabytes = null,
        ?array $storageDisks = null,
    ): array {
        return [
            'id' => (string) $id,
            'udid' => 'jamf-udid-'.$id,
            'general' => [
                'name' => $name,
                'lastContactTime' => $lastContactTime,
                'lastEnrolledDate' => null,
            ],
            'hardware' => [
                'make' => 'Apple',
                'model' => $model,
                'modelIdentifier' => 'Mac'.$id,
                'serialNumber' => $serialNumber,
                'macAddress' => $macAddress,
                'totalRamMegabytes' => $totalRamMegabytes,
            ],
            'operatingSystem' => [
                'name' => $osName,
                'version' => $osVersion,
            ],
            'storage' => $storageDisks !== null ? ['disks' => $storageDisks] : null,
        ];
    }
}
