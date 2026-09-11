<?php

namespace Tests\Feature\SyncAdapters\JumpCloud;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\JumpCloud\JumpCloudAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the JumpCloud adapter through
 * SyncHostFromAdapter. Mocks the JumpCloud Systems API, asserts assets
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
            '*/systems*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jumpcloudSystem(id: 'sys-1', hostname: 'workstation-01', systemType: 'Mac'),
                    $this->jumpcloudSystem(id: 'sys-2', hostname: 'workstation-02', systemType: 'Windows'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
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
        string $systemType = 'Mac',
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
