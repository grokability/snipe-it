<?php

namespace Tests\Feature\SyncAdapters\Mosyle;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Mosyle\MosyleAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Mosyle adapter through
 * SyncHostFromAdapter. Mocks the Mosyle Manager v2 response shape (the
 * nested response[0].response.rows form), asserts assets + external
 * ids land correctly, and that the client sends POST (not GET) since
 * Mosyle's reads use POST bodies.
 */
class MosyleAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_mosyle_and_creates_assets()
    {
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/devices' => Http::sequence()
                ->push([
                    'response' => [[
                        'response' => [
                            'rows' => [
                                $this->mosyleDevice(udid: 'aaa-111', name: 'edu-ipad-01', model: 'iPad (10th generation)'),
                                $this->mosyleDevice(udid: 'bbb-222', name: 'edu-ipad-02', model: 'iPad Air'),
                            ],
                        ],
                    ]],
                ])
                // Empty second page terminates the client's pagination loop.
                ->push([
                    'response' => [['response' => ['rows' => []]]],
                ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'mosyle', 'external_id' => 'aaa-111']);
        $this->assertDatabaseHas('assets', ['name' => 'edu-ipad-01']);
    }

    public function test_client_sends_post_not_get()
    {
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/devices' => Http::response([
                'response' => [['response' => ['rows' => []]]],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        // Mosyle's devices endpoint takes POST with a JSON body,
        // unlike every other adapter here. A regression to GET would
        // 405 against a real tenant. this catches it before it ships.
        Http::assertSent(function ($request) {
            return $request->method() === 'POST';
        });
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredMosyleAdapter();

        Http::fake([
            '*/devices' => Http::sequence()
                ->push([
                    'response' => [['response' => ['rows' => [
                        $this->mosyleDevice(
                            udid: 'ccc-333',
                            name: 'staff-mbp',
                            model: 'MacBook Pro',
                            serial: 'C02XYZ',
                            os: 'macOS',
                            osversion: '14.2.1',
                            wifi_mac: 'aa:bb:cc:11:22:33',
                            date_info: '2026-01-15T10:00:00.000Z',
                        ),
                    ]]]],
                ])
                ->push(['response' => [['response' => ['rows' => []]]]]),
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
        $this->assertSame('macOS', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredMosyleAdapter(): MosyleAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://managerapi.mosyle.com/v2');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-mosyle-token'));

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
            'date_info' => $date_info,
            'is_supervised' => true,
        ];
    }
}
