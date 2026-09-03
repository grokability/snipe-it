<?php

namespace Tests\Feature\SyncAdapters\Addigy;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Addigy\AddigyAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Addigy adapter through
 * SyncHostFromAdapter. Mocks the Addigy API, asserts assets +
 * external ids land correctly, and that the client-id / client-secret
 * header pair (not bearer) goes out on the request.
 */
class AddigyAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_addigy_and_creates_assets()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            '*/api/devices' => Http::response([
                $this->addigyDevice(agentid: 'agent-1', name: 'design-mbp', hardware_model: 'MacBook Pro'),
                $this->addigyDevice(agentid: 'agent-2', name: 'staff-imac', hardware_model: 'iMac'),
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'addigy', 'external_id' => 'agent-1']);
        $this->assertDatabaseHas('assets', ['name' => 'design-mbp']);
    }

    public function test_client_id_and_client_secret_headers_are_sent_not_bearer()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            '*/api/devices' => Http::response([]),
        ]);

        iterator_to_array($adapter->pull());

        // Addigy's API uses a header pair, not bearer-token auth.
        // A regression to withToken() would silently 401 against a
        // real tenant.
        Http::assertSent(function ($request) {
            return $request->hasHeader('client-id', 'fake-addigy-client-id')
                && $request->hasHeader('client-secret', 'fake-addigy-client-secret')
                && ! $request->hasHeader('Authorization');
        });
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            '*/api/devices' => Http::response([
                $this->addigyDevice(
                    agentid: 'agent-7',
                    name: 'lab-mbp',
                    hardware_model: 'MacBookPro18,3',
                    serial_number: 'C02XYZ',
                    network_mac_address: 'aa:bb:cc:11:22:33',
                    network_ip_address: '10.0.0.42',
                    os_type: 'macOS',
                    os_version: '14.2.1',
                    last_online: '2026-01-15T10:00:00.000Z',
                ),
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('addigy', $record->sourceKey);
        $this->assertSame('agent-7', $record->sourceId);
        $this->assertSame('lab-mbp', $record->hostname);
        $this->assertSame('MacBookPro18,3', $record->hardwareModel);
        $this->assertSame('C02XYZ', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:11:22:33', $record->primaryMac);
        $this->assertSame('10.0.0.42', $record->primaryIp);
        $this->assertSame('macOS', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredAddigyAdapter(): AddigyAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://prod.addigy.com');
        // key_id is non-secret, stored as plain text.
        SyncAdapterConfig::put($instance->id, 'key_id', 'fake-addigy-client-id');
        SyncAdapterConfig::put($instance->id, 'key_secret', Crypt::encrypt('fake-addigy-client-secret'));

        return new AddigyAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function addigyDevice(
        string $agentid,
        string $name = 'device',
        string $hardware_model = 'MacBook',
        ?string $serial_number = null,
        ?string $network_mac_address = null,
        ?string $network_ip_address = null,
        ?string $os_type = 'macOS',
        ?string $os_version = null,
        ?string $last_online = null,
    ): array {
        return [
            'agentid' => $agentid,
            'name' => $name,
            'hardware_model' => $hardware_model,
            'serial_number' => $serial_number,
            'network_mac_address' => $network_mac_address,
            'network_ip_address' => $network_ip_address,
            'os_type' => $os_type,
            'os_version' => $os_version,
            'last_online' => $last_online,
            'policy_id' => 'policy-abc',
            'agent_version' => '4.0.0',
        ];
    }
}
