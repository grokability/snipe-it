<?php

namespace Tests\Feature\SyncAdapters\JamfSchool;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\JamfSchool\JamfSchoolAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Jamf School adapter. Mocks the Jamf
 * School (ex-ZuluDesk) API, asserts assets + external ids land
 * correctly, and that HTTP Basic auth (network_id + api_key) goes out
 * on the request instead of the bearer-token pattern Jamf Pro uses.
 */
class JamfSchoolAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_jamf_school_and_creates_assets()
    {
        $adapter = $this->configuredJamfSchoolAdapter();

        Http::fake([
            '*/devices*' => Http::response([
                'count' => 2,
                'devices' => [
                    $this->jamfSchoolDevice(udid: 'edu-udid-1', name: 'classroom-ipad-01', modelName: 'iPad (10th generation)'),
                    $this->jamfSchoolDevice(udid: 'edu-udid-2', name: 'classroom-ipad-02', modelName: 'iPad Air'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf_school', 'external_id' => 'edu-udid-1']);
        $this->assertDatabaseHas('assets', ['name' => 'classroom-ipad-01']);
    }

    public function test_basic_auth_is_sent_not_bearer()
    {
        $adapter = $this->configuredJamfSchoolAdapter();

        Http::fake([
            '*/devices*' => Http::response(['count' => 0, 'devices' => []]),
        ]);

        iterator_to_array($adapter->pull());

        // Jamf School's API uses HTTP Basic auth with network_id as
        // username and api_key as password. Distinct from Jamf Pro's
        // bearer-token model. a copy-paste regression to withToken()
        // would 401 against a real tenant.
        Http::assertSent(function ($request) {
            $expected = 'Basic '.base64_encode('school-network-id:fake-jamf-school-key');

            return $request->hasHeader('Authorization', $expected);
        });
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredJamfSchoolAdapter();

        Http::fake([
            '*/devices*' => Http::response([
                'count' => 1,
                'devices' => [
                    $this->jamfSchoolDevice(
                        udid: 'edu-udid-7',
                        name: 'chem-lab-ipad',
                        modelName: 'iPad Pro 12.9',
                        serial: 'JSCHOOL-XYZ',
                        wifiMac: 'aa:bb:cc:11:22:33',
                        os: 'iPadOS',
                        osVersion: '17.2',
                        lastCheckin: '2026-01-15 10:00:00',
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('jamf_school', $record->sourceKey);
        $this->assertSame('edu-udid-7', $record->sourceId);
        $this->assertSame('chem-lab-ipad', $record->hostname);
        $this->assertSame('iPad Pro 12.9', $record->hardwareModel);
        $this->assertSame('JSCHOOL-XYZ', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:11:22:33', $record->primaryMac);
        $this->assertSame('iPadOS', $record->os);
        $this->assertSame('17.2', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_network_information_surfaces_mac_and_ip(): void
    {
        // Regression test for issue #19856. Jamf School nests
        // Wi-Fi MAC and IP under `networkInformation` on every
        // device class. The earlier adapter read a top-level
        // `wifiMacAddress` key that is never present in a Jamf
        // School payload, so primaryMac + primaryIp both landed
        // null across every synced device.
        $adapter = $this->configuredJamfSchoolAdapter();

        Http::fake([
            '*/devices*' => Http::response([
                'count' => 1,
                'devices' => [
                    $this->jamfSchoolDevice(
                        udid: 'edu-udid-net',
                        wifiMac: '02:5c:a1:72:8d:30',
                        ipAddress: '192.0.2.42',
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('02:5c:a1:72:8d:30', $records[0]->primaryMac);
        $this->assertSame('192.0.2.42', $records[0]->primaryIp);
    }

    public function test_owner_block_populates_assigned_user_fields()
    {
        // Regression test for issue #19856. Jamf School carries the
        // device's assigned user under `owner`. The adapter has to
        // surface email + username so the pipeline's user-lookup can
        // resolve them.
        $adapter = $this->configuredJamfSchoolAdapter();

        Http::fake([
            '*/devices*' => Http::response([
                'count' => 1,
                'devices' => [
                    $this->jamfSchoolDevice(
                        udid: 'edu-udid-9',
                        owner: [
                            'username' => 'edu-staff-1',
                            'email' => 'edu-staff-1@edu.invalid',
                        ],
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('edu-staff-1@edu.invalid', $records[0]->assignedUserEmail);
        $this->assertSame('edu-staff-1', $records[0]->assignedUserName);
    }

    public function test_absent_owner_block_leaves_assigned_user_null()
    {
        $adapter = $this->configuredJamfSchoolAdapter();

        Http::fake([
            '*/devices*' => Http::response([
                'count' => 1,
                'devices' => [
                    $this->jamfSchoolDevice(udid: 'edu-udid-10'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertNull($records[0]->assignedUserEmail);
        $this->assertNull($records[0]->assignedUserName);
    }

    private function configuredJamfSchoolAdapter(): JamfSchoolAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jamf_school')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.jamfcloud.com/api');
        // Non-secret field stored as plain text (matches Intune's
        // tenant_id / client_id shape).
        SyncAdapterConfig::put($instance->id, 'network_id', 'school-network-id');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-jamf-school-key'));

        return new JamfSchoolAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>|null  $owner
     */
    private function jamfSchoolDevice(
        string $udid,
        string $name = 'device',
        string $modelName = 'iPad',
        ?string $serial = null,
        ?string $wifiMac = null,
        ?string $ipAddress = null,
        ?string $os = 'iPadOS',
        ?string $osVersion = null,
        ?string $lastCheckin = null,
        ?array $owner = null,
    ): array {
        $device = [
            'UDID' => $udid,
            'name' => $name,
            'serialNumber' => $serial,
            'model' => [
                'name' => $modelName,
                'identifier' => 'iPad12,1',
            ],
            'os' => [
                'prefix' => $os,
                'version' => $osVersion,
            ],
            // Jamf School nests network info under this key on every
            // device class (iPhone / iPad / Mac).
            'networkInformation' => [
                'WiFiMAC' => $wifiMac,
                'IPAddress' => $ipAddress,
            ],
            'lastCheckin' => $lastCheckin,
            'isSupervised' => true,
            'assetTag' => null,
            'locationId' => 1,
        ];

        if ($owner !== null) {
            $device['owner'] = $owner;
        }

        return $device;
    }
}
