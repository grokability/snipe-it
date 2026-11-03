<?php

namespace Tests\Feature\SyncAdapters\JumpCloud;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\JumpCloud\JumpCloudAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the JumpCloud adapter through
 * SyncAdapter. Mocks the JumpCloud Systems API, asserts assets
 * + asset_external_sources land correctly, and that the x-api-key header
 * shape (not bearer) actually goes out on the request.
 */
class JumpCloudAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_systems_from_jumpcloud_and_creates_assets()
    {
        $adapter = $this->configuredJumpCloudAdapter();

        Http::fake([
            '*/v2/systeminsights/system_info*' => Http::response([]),
            '*/systems*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jumpcloudSystem(id: 'sys-1', hostname: 'workstation-01', systemType: 'Mac'),
                    $this->jumpcloudSystem(id: 'sys-2', hostname: 'workstation-02', systemType: 'Windows'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jumpcloud', 'external_id' => 'sys-1']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jumpcloud', 'external_id' => 'sys-2']);
        $this->assertDatabaseHas('assets', ['name' => 'workstation-01']);
    }

    public function test_x_api_key_header_is_sent_not_bearer()
    {
        $adapter = $this->configuredJumpCloudAdapter();

        Http::fake([
            '*/v2/systeminsights/system_info*' => Http::response([]),
            '*/systems*' => Http::response(['totalCount' => 0, 'results' => []]),
        ]);

        iterator_to_array($adapter->pull());

        // JumpCloud uses a non-standard x-api-key header instead of the
        // Authorization: Bearer convention the other adapters follow.
        // Regressing to withToken() would silently 401 against a real
        // tenant. this catches it before it ships.
        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'fake-jumpcloud-key')
                && ! $request->hasHeader('Authorization');
        });
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredJumpCloudAdapter();

        Http::fake([
            '*/v2/systeminsights/system_info*' => Http::response([]),
            '*/systems*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jumpcloudSystem(
                        id: 'sys-7',
                        hostname: 'dev-linux',
                        systemType: 'Linux',
                        osFamily: 'linux',
                        os: 'Ubuntu',
                        version: '24.04',
                        serialNumber: 'LNX-999',
                        lastContact: '2026-01-15T10:00:00.000Z',
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('jumpcloud', $record->sourceKey);
        $this->assertSame('sys-7', $record->sourceId);
        $this->assertSame('dev-linux', $record->hostname);
        $this->assertSame('Linux', $record->hardwareModel);
        $this->assertSame('LNX-999', $record->hardwareSerial);
        $this->assertNull($record->manufacturer); // only darwin infers to Apple
        $this->assertSame('Ubuntu', $record->os);
        $this->assertSame('24.04', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_v2_system_insights_populates_hardware_model_and_vendor()
    {
        // Regression for the Reddit report where V1 /systems returned
        // null systemType, leaving hardwareModel null and asset
        // creation failing on the model_id required rule. V2 System
        // Insights carries the real hardware_model and hardware_vendor
        // and the adapter prefers them over the V1 fallbacks.
        $adapter = $this->configuredJumpCloudAdapter();

        Http::fake([
            '*/v2/systeminsights/system_info*' => Http::sequence()
                ->push([
                    [
                        'system_id' => 'sys-win',
                        'hardware_model' => 'Latitude 5540',
                        'hardware_vendor' => 'Dell Inc.',
                    ],
                    [
                        'system_id' => 'sys-mac',
                        'hardware_model' => 'MacBookPro18,3',
                        'hardware_vendor' => 'Apple Inc.',
                    ],
                ])
                ->push([]),
            '*/systems*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jumpcloudSystem(id: 'sys-win', hostname: 'win-laptop', systemType: null, osFamily: 'windows', os: 'Windows'),
                    $this->jumpcloudSystem(id: 'sys-mac', hostname: 'mac-laptop', systemType: 'Mac', osFamily: 'darwin', os: 'Mac OS X'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(2, $records);

        $win = collect($records)->firstWhere('sourceId', 'sys-win');
        $this->assertSame('Latitude 5540', $win->hardwareModel);
        $this->assertSame('Dell Inc.', $win->manufacturer);

        $mac = collect($records)->firstWhere('sourceId', 'sys-mac');
        $this->assertSame('MacBookPro18,3', $mac->hardwareModel, 'V2 hardware_model wins over V1 systemType.');
        $this->assertSame('Apple Inc.', $mac->manufacturer, 'V2 hardware_vendor wins over the darwin => Apple guess.');
    }

    public function test_v2_system_insights_403_falls_back_to_v1_fields()
    {
        // An API key without System Insights permission returns 403.
        // The adapter must log and keep going with V1-only normalization
        // so the sync still produces usable records for assets that have
        // a non-null V1 systemType, instead of aborting the whole run.
        $adapter = $this->configuredJumpCloudAdapter();

        Http::fake([
            '*/v2/systeminsights/system_info*' => Http::response([], 403),
            '*/systems*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jumpcloudSystem(id: 'sys-fallback', hostname: 'fallback-mac', systemType: 'Mac', osFamily: 'darwin'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $this->assertSame('Mac', $records[0]->hardwareModel);
        $this->assertSame('Apple', $records[0]->manufacturer);
    }

    public function test_v2_insights_missing_for_a_system_falls_back_to_v1_for_that_record_only()
    {
        // Mixed scenario: one system is in both V1 and V2, the other
        // is only in V1 (newly enrolled, agent has not reported system
        // info yet). The second record must still produce a usable row
        // via the V1 fallbacks instead of a null model.
        $adapter = $this->configuredJumpCloudAdapter();

        Http::fake([
            '*/v2/systeminsights/system_info*' => Http::sequence()
                ->push([
                    [
                        'system_id' => 'sys-known',
                        'hardware_model' => 'Precision 5570',
                        'hardware_vendor' => 'Dell Inc.',
                    ],
                ])
                ->push([]),
            '*/systems*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jumpcloudSystem(id: 'sys-known', hostname: 'known-host', systemType: null, osFamily: 'linux'),
                    $this->jumpcloudSystem(id: 'sys-pending', hostname: 'pending-host', systemType: 'Mac', osFamily: 'darwin'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(2, $records);

        $known = collect($records)->firstWhere('sourceId', 'sys-known');
        $this->assertSame('Precision 5570', $known->hardwareModel);
        $this->assertSame('Dell Inc.', $known->manufacturer);

        $pending = collect($records)->firstWhere('sourceId', 'sys-pending');
        $this->assertSame('Mac', $pending->hardwareModel);
        $this->assertSame('Apple', $pending->manufacturer);
    }

    private function configuredJumpCloudAdapter(): JumpCloudAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jumpcloud')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://console.jumpcloud.com/api');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-jumpcloud-key'));

        return new JumpCloudAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function jumpcloudSystem(
        string $id,
        string $hostname = 'system',
        ?string $systemType = 'Mac',
        ?string $osFamily = 'darwin',
        ?string $os = 'Mac OS X',
        ?string $version = null,
        ?string $serialNumber = null,
        ?string $lastContact = null,
    ): array {
        return [
            '_id' => $id,
            'hostname' => $hostname,
            'displayName' => $hostname,
            'systemType' => $systemType,
            'osFamily' => $osFamily,
            'os' => $os,
            'version' => $version,
            'arch' => 'arm64',
            'serialNumber' => $serialNumber,
            'agentVersion' => '1.0.0',
            'lastContact' => $lastContact,
        ];
    }
}
