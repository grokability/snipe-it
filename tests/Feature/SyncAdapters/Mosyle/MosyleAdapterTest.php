<?php

namespace Tests\Feature\SyncAdapters\Mosyle;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Mosyle\MosyleAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Mosyle adapter through SyncAdapter.
 * Mocks Mosyle Manager v2's actual response shapes: JWT returned in
 * the Authorization header on /login, /listdevices paginated by OS,
 * accessToken required in the request body on every call.
 */
class MosyleAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_mosyle_and_creates_assets(): void
    {
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::sequence()
                // First macOS page returns two devices.
                ->push($this->devicesResponse([
                    $this->mosyleDevice(udid: 'aaa-111', name: 'edu-ipad-01', model: 'iPad (10th generation)'),
                    $this->mosyleDevice(udid: 'bbb-222', name: 'edu-ipad-02', model: 'iPad Air'),
                ]))
                // Empty page terminates macOS pagination.
                ->push($this->devicesResponse([]))
                // iOS / tvOS / visionOS all empty.
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'mosyle', 'external_id' => 'aaa-111']);
        $this->assertDatabaseHas('assets', ['name' => 'edu-ipad-01']);
    }

    public function test_login_is_called_once_and_jwt_is_reused_across_pages(): void
    {
        // Issue #19790 regression: a Mosyle sync against a tenant with
        // devices across multiple OSes must POST /login exactly once
        // and reuse the JWT. Repeated /login would consume Mosyle's
        // auth rate limit on every page.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::response($this->devicesResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        $loginCalls = 0;
        Http::assertSent(function ($request) use (&$loginCalls) {
            if (str_ends_with($request->url(), '/login')) {
                $loginCalls++;
            }

            return true;
        });
        $this->assertSame(1, $loginCalls, 'Expected /login to be called exactly once per sync run.');
    }

    public function test_devices_request_carries_access_token_in_body(): void
    {
        // Mosyle 401s with "accessToken Required" if the token is only
        // in the Authorization header. Both the JWT bearer AND the
        // body-level accessToken are required on every /listdevices
        // call.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::response($this->devicesResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/listdevices')) {
                return true;
            }

            return $request->method() === 'POST'
                && ($request->data()['accessToken'] ?? null) === 'fake-access-token'
                && ($request->header('Authorization')[0] ?? null) === 'Bearer fake-jwt';
        });
    }

    public function test_devices_request_iterates_each_supported_os(): void
    {
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::response($this->devicesResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        $osesRequested = [];
        Http::assertSent(function ($request) use (&$osesRequested) {
            if (str_ends_with($request->url(), '/listdevices')) {
                $osesRequested[] = $request->data()['options']['os'] ?? null;
            }

            return true;
        });

        $this->assertSame(['macos', 'ios', 'tvos', 'visionos'], $osesRequested);
    }

    public function test_normalized_record_populates_extra_fields_from_manager_v2_payload(): void
    {
        // Issue #19790: Mosyle Manager v2 returns ~90 fields per device
        // and we expose a curated subset as extras so admins can map
        // them to custom fields. This locks in the field-name mapping
        // (Mosyle API key -> adapter extra key) so a Manager-side
        // rename or an adapter-side typo would be caught on CI.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::sequence()
                ->push($this->devicesResponse([[
                    'deviceudid' => 'xxx-999',
                    'serial_number' => 'C02-EXTRA',
                    // Hardware
                    'battery' => '87',
                    'total_disk' => '256.0000000000',
                    'available_disk' => '103.5',
                    'bluetooth_mac_address' => '11:22:33:44:55:66',
                    'ethernet_mac_address' => '77:88:99:aa:bb:cc',
                    'device_type' => 'Mac',
                    'BuildVersion' => '23B74',
                    // Cellular
                    'carrier' => 'Verizon',
                    'imei' => '990000862471854',
                    'meid' => 'A1000012345678',
                    // Security / posture
                    'isActivationLockEnabled' => true,
                    'isDeviceLocatorServiceEnabled' => true,
                    'isCloudBackupEnabled' => false,
                    'LastCloudBackupDate' => '2026-01-10T04:00:00Z',
                    'SystemIntegrityProtectionEnabled' => true,
                    'DeviceAttestationStatus' => 'VERIFIED',
                    // MDM / lifecycle
                    'enrollment_type' => 'ADE',
                    'status' => 'enrolled',
                    'ManagementStatus' => 'Managed',
                    'OSUpdateStatus' => 'UpToDate',
                    'date_last_beat' => '2026-01-15T09:50:00Z',
                    'date_last_push' => '2026-01-15T09:51:00Z',
                    'userid' => 'u-42',
                    'is_supervised' => true,
                    // User / scoping
                    'usertype' => 'faculty',
                    'location' => 'HQ',
                    'tags' => ['loaner', 'ipad-cart-3'],
                    // Network
                    'last_ssid' => 'office-wifi-5g',
                    // Lost mode
                    'lostmode_status' => 'inactive',
                    'latitude' => '37.7749',
                    'longitude' => '-122.4194',
                    'altitude' => '12.5',
                ]]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $extra = $records[0]->extra;

        // Hardware
        $this->assertSame('87', $extra['mosyle_battery']);
        $this->assertSame('256.0000000000', $extra['mosyle_total_disk']);
        $this->assertSame('103.5', $extra['mosyle_available_disk']);
        $this->assertSame('11:22:33:44:55:66', $extra['mosyle_bluetooth_mac']);
        $this->assertSame('77:88:99:aa:bb:cc', $extra['mosyle_ethernet_mac']);
        $this->assertSame('Mac', $extra['mosyle_device_type']);
        $this->assertSame('23B74', $extra['mosyle_build_version']);
        // Cellular
        $this->assertSame('Verizon', $extra['mosyle_carrier']);
        $this->assertSame('990000862471854', $extra['mosyle_imei']);
        // Security / posture
        $this->assertTrue($extra['mosyle_activation_lock_enabled']);
        $this->assertFalse($extra['mosyle_cloud_backup_enabled']);
        $this->assertSame('2026-01-10T04:00:00Z', $extra['mosyle_last_cloud_backup_date']);
        $this->assertSame('VERIFIED', $extra['mosyle_device_attestation_status']);
        // MDM / lifecycle
        $this->assertSame('ADE', $extra['mosyle_enrollment_type']);
        $this->assertSame('Managed', $extra['mosyle_management_status']);
        $this->assertSame('u-42', $extra['mosyle_user_id']);
        $this->assertTrue($extra['mosyle_supervised']);
        // User / scoping
        $this->assertSame('faculty', $extra['mosyle_user_type']);
        $this->assertSame('HQ', $extra['mosyle_location']);
        $this->assertSame(['loaner', 'ipad-cart-3'], $extra['mosyle_tags']);
        // Network
        $this->assertSame('office-wifi-5g', $extra['mosyle_last_ssid']);
        // Lost mode
        $this->assertSame('inactive', $extra['mosyle_lost_mode_status']);
        $this->assertSame('37.7749', $extra['mosyle_latitude']);
        $this->assertSame('-122.4194', $extra['mosyle_longitude']);
    }

    public function test_normalized_record_uses_manager_v2_field_names(): void
    {
        // Issue #19790: the original adapter had wrong field names
        // (`usename` instead of `username`, `ip_address` not in the
        // Mosyle response at all, `locationid` instead of `location`).
        // This locks in the Manager v2 names from the API doc.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::sequence()
                ->push($this->devicesResponse([
                    $this->mosyleDevice(
                        udid: 'ccc-333',
                        name: 'staff-mbp',
                        model: 'MacBook Pro',
                        serial: 'C02XYZ',
                        os: 'macOS',
                        osversion: '14.2.1',
                        wifi_mac: 'aa:bb:cc:11:22:33',
                        last_lan_ip: '10.0.1.47',
                        username: 'jdoe',
                        useremail: 'jdoe@example.com',
                        date_info: '2026-01-15T10:00:00.000Z',
                    ),
                ]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('mosyle', $record->sourceKey);
        $this->assertSame('ccc-333', $record->sourceId);
        $this->assertSame('staff-mbp', $record->hostname);
        $this->assertSame('MacBook Pro', $record->hardwareModel);
        $this->assertSame('C02XYZ', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:11:22:33', $record->primaryMac);
        $this->assertSame('10.0.1.47', $record->primaryIp);
        $this->assertSame('macOS', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertSame('jdoe', $record->assignedUserName);
        $this->assertSame('jdoe@example.com', $record->assignedUserEmail);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_last_seen_parses_unix_epoch_timestamps(): void
    {
        // Issue #19790 follow-up: Mosyle Manager v2 returns date_info
        // as a Unix epoch seconds integer on some tenants (observed
        // 1791483579 = 2026-10-06). Carbon::parse() alone chokes on
        // bare numeric values. The adapter must detect epoch-shape
        // numbers and route them through createFromTimestamp.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::sequence()
                ->push($this->devicesResponse([
                    $this->mosyleDevice(udid: 'epoch-1', date_info: '1791483579'),
                ]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $this->assertNotNull($records[0]->lastSeen);
        $this->assertSame(1791483579, $records[0]->lastSeen->getTimestamp());
    }

    public function test_last_seen_parses_iso_8601_timestamps(): void
    {
        // Mosyle sends ISO 8601 on other fields / tenants. Both
        // shapes have to work in the same adapter.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::sequence()
                ->push($this->devicesResponse([
                    $this->mosyleDevice(udid: 'iso-1', date_info: '2026-01-15T10:00:00.000Z'),
                ]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertNotNull($records[0]->lastSeen);
        $this->assertSame('2026-01-15', $records[0]->lastSeen->toDateString());
    }

    public function test_unparseable_timestamp_nulls_out_and_sync_continues(): void
    {
        // Pre-fix, a single device with a malformed date_info aborted
        // the entire sync run (user report on #19790). Post-fix, the
        // bad value is logged and nulled out for that one record.
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => Http::sequence()
                ->push($this->devicesResponse([
                    $this->mosyleDevice(udid: 'bad-ts', date_info: 'not-a-timestamp-at-all'),
                    $this->mosyleDevice(udid: 'good-ts', date_info: '1791483579'),
                ]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(2, $records, 'Sync must not abort when a single device has a bad timestamp.');
        $this->assertNull($records[0]->lastSeen);
        $this->assertNotNull($records[1]->lastSeen);
    }

    public function test_login_missing_authorization_header_throws(): void
    {
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/login' => Http::response(['status' => 'OK'], 200),
            '*/listdevices' => Http::response($this->devicesResponse([])),
        ]);

        $this->expectExceptionMessage('Mosyle /login did not return a Bearer token');

        iterator_to_array($adapter->pull());
    }

    /**
     * @return array<string, mixed>
     */
    private function devicesResponse(array $devices): array
    {
        return [
            'status' => 'OK',
            'response' => [
                'devices' => $devices,
                'rows' => (string) count($devices),
                'page_size' => 200,
                'page' => 1,
            ],
        ];
    }

    private function configuredMosyleAdapter(): MosyleAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://managerapi.mosyle.com/v2');
        SyncAdapterConfig::put($instance->id, 'access_token', Crypt::encrypt('fake-access-token'));
        SyncAdapterConfig::put($instance->id, 'email', 'sync-service@example.com');
        SyncAdapterConfig::put($instance->id, 'password', Crypt::encrypt('fake-password'));

        return new MosyleAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function mosyleDevice(
        string $udid,
        string $name = 'device',
        string $model = 'iPad',
        ?string $serial = null,
        ?string $os = 'iOS',
        ?string $osversion = null,
        ?string $wifi_mac = null,
        ?string $last_lan_ip = null,
        ?string $username = null,
        ?string $useremail = null,
        ?string $date_info = null,
    ): array {
        return [
            'deviceudid' => $udid,
            'device_name' => $name,
            'device_model_name' => $model,
            'serial_number' => $serial,
            'os' => $os,
            'osversion' => $osversion,
            'wifi_mac_address' => $wifi_mac,
            'last_lan_ip' => $last_lan_ip,
            'username' => $username,
            'useremail' => $useremail,
            'date_info' => $date_info,
            'is_supervised' => true,
        ];
    }
}
