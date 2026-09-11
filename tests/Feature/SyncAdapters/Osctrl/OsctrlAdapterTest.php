<?php

namespace Tests\Feature\SyncAdapters\Osctrl;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Osctrl\OsctrlAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the osctrl adapter through
 * SyncHostFromAdapter. Mocks the osctrl nodes response, asserts assets
 * + external ids land correctly, and that the environment schema
 * field flows into the request URL path (osctrl's API is environment-
 * scoped).
 */
class OsctrlAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_nodes_from_osctrl_and_creates_assets()
    {
        $adapter = $this->configuredOsctrlAdapter(environment: 'prod');

        Http::fake([
            '*/api/v1/nodes/prod/all' => Http::response([
                $this->osctrlNode(uuid: 'node-1', hostname: 'edge-01', hardware_model: 'Dell OptiPlex'),
                $this->osctrlNode(uuid: 'node-2', hostname: 'edge-02', hardware_model: 'HP EliteDesk'),
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'osctrl', 'external_id' => 'node-1']);
        $this->assertDatabaseHas('assets', ['name' => 'edge-01']);
    }

    public function test_environment_schema_field_flows_into_the_request_path()
    {
        $adapter = $this->configuredOsctrlAdapter(environment: 'staging');

        Http::fake([
            '*/api/v1/nodes/*' => Http::response([]),
        ]);

        iterator_to_array($adapter->pull());

        // Regression: if the adapter forgot to pass the environment
        // through, the request would hit the wrong path and return
        // nothing (or 404), silently no-op'ing the sync.
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/nodes/staging/all');
        });
    }

    public function test_isenabled_returns_false_without_environment_field()
    {
        $instance = SyncAdapterInstance::where('slug', 'osctrl')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://osctrl.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('t'));
        $instance->active = true;
        $instance->save();

        $adapter = new OsctrlAdapter($instance->fresh());

        // Environment field is required (default in credentialSchema),
        // so isEnabled() must refuse until it's present. Prevents the
        // Sync Now button from becoming clickable on a half-configured
        // adapter.
        $this->assertFalse($adapter->isEnabled());

        SyncAdapterConfig::put($instance->id, 'environment', 'prod');
        $this->assertTrue((new OsctrlAdapter($instance->fresh()))->isEnabled());
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredOsctrlAdapter(environment: 'prod');

        Http::fake([
            '*/api/v1/nodes/prod/all' => Http::response([
                $this->osctrlNode(
                    uuid: 'node-7',
                    hostname: 'db-primary',
                    hardware_model: 'PowerEdge R650',
                    hardware_serial: 'PE-XYZ',
                    hardware_vendor: 'Dell Inc.',
                    platform: 'ubuntu',
                    platform_version: '24.04',
                    ip_address: '10.0.0.7',
                    last_seen: '2026-01-15T10:00:00Z',
                ),
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('osctrl', $record->sourceKey);
        $this->assertSame('node-7', $record->sourceId);
        $this->assertSame('db-primary', $record->hostname);
        $this->assertSame('PowerEdge R650', $record->hardwareModel);
        $this->assertSame('PE-XYZ', $record->hardwareSerial);
        $this->assertSame('Dell Inc.', $record->manufacturer);
        $this->assertSame('10.0.0.7', $record->primaryIp);
        $this->assertSame('ubuntu', $record->os);
        $this->assertSame('24.04', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredOsctrlAdapter(string $environment): OsctrlAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'osctrl')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://osctrl.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-osctrl-token'));
        SyncAdapterConfig::put($instance->id, 'environment', $environment);

        return new OsctrlAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function osctrlNode(
        string $uuid,
        string $hostname = 'node',
        string $hardware_model = 'Generic Server',
        ?string $hardware_serial = null,
        ?string $hardware_vendor = null,
        ?string $platform = 'ubuntu',
        ?string $platform_version = null,
        ?string $ip_address = null,
        ?string $last_seen = null,
    ): array {
        return [
            'uuid' => $uuid,
            'hostname' => $hostname,
            'localname' => $hostname,
            'hardware_model' => $hardware_model,
            'hardware_serial' => $hardware_serial,
            'hardware_vendor' => $hardware_vendor,
            'platform' => $platform,
            'platform_version' => $platform_version,
            'ip_address' => $ip_address,
            'last_seen' => $last_seen,
            'environment' => 'prod',
            'osquery_version' => '5.11.0',
        ];
    }
}
