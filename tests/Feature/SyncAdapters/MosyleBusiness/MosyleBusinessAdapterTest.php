<?php

namespace Tests\Feature\SyncAdapters\MosyleBusiness;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\MosyleBusiness\MosyleBusinessAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Mosyle Business adapter. Mocks the
 * Business v1 response shapes: JWT returned in the Authorization
 * header on /login, /devices dispatcher POST with
 * operation=list, accessToken required in the request HEADER (not
 * body) on every call, and the array-wrapped response at
 * response[0].devices.
 */
class MosyleBusinessAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_mosyle_business_and_creates_assets(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::sequence()
                // First mac page returns two devices.
                ->push($this->devicesResponse([
                    $this->businessDevice(udid: 'biz-mac-1', name: 'biz-mbp-01', model: 'MacBook Pro'),
                    $this->businessDevice(udid: 'biz-mac-2', name: 'biz-mbp-02', model: 'MacBook Air'),
                ]))
                // Terminator for mac + empty responses for ios / tvos / visionos
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'mosyle_business', 'external_id' => 'biz-mac-1']);
        $this->assertDatabaseHas('assets', ['name' => 'biz-mbp-01']);
    }

    public function test_devices_request_carries_access_token_in_header_not_body(): void
    {
        // Business keeps accessToken header-only. Manager keeps it
        // body-only. Both patterns coexist in the codebase, so
        // asserting the header position catches a Business-to-Manager
        // regression where the adapter accidentally moves the token
        // into the body.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response($this->devicesResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/devices')) {
                return true;
            }
            $body = $request->data();

            return $request->method() === 'POST'
                && ($request->header('accessToken')[0] ?? null) === 'fake-access-token'
                && ($request->header('Authorization')[0] ?? null) === 'Bearer fake-jwt'
                && ! array_key_exists('accessToken', $body)
                && ($body['operation'] ?? null) === 'list';
        });
    }

    public function test_devices_request_iterates_each_business_os(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response($this->devicesResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        $osesRequested = [];
        Http::assertSent(function ($request) use (&$osesRequested) {
            if (str_ends_with($request->url(), '/devices')) {
                $osesRequested[] = $request->data()['options']['os'] ?? null;
            }

            return true;
        });

        // Business uses 'mac', not Manager's 'macos'.
        $this->assertSame(['mac', 'ios', 'tvos', 'visionos'], $osesRequested);
    }

    public function test_login_posts_access_token_as_header_and_credentials_as_body(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response($this->devicesResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/login')) {
                return true;
            }
            $body = $request->data();

            return $request->method() === 'POST'
                && ($request->header('accessToken')[0] ?? null) === 'fake-access-token'
                && ($body['email'] ?? null) === 'sync-service@example.com'
                && ($body['password'] ?? null) === 'fake-password'
                && ! array_key_exists('accessToken', $body);
        });
    }

    public function test_response_is_unwrapped_from_the_array_wrapper(): void
    {
        // Business returns response[0].devices. Manager returns
        // response.devices. The adapter must unwrap the array form.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::sequence()
                ->push($this->devicesResponse([
                    $this->businessDevice(udid: 'wrap-1', name: 'wrapped'),
                ]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame('wrap-1', $records[0]->sourceId);
    }

    public function test_normalized_record_populates_business_specific_field_names(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::sequence()
                ->push($this->devicesResponse([[
                    'deviceudid' => 'biz-full',
                    'serial_number' => 'C02-BIZ',
                    'device_name' => 'biz-full-device',
                    'device_model' => 'iPad12,1',
                    'device_model_name' => 'iPad',
                    'os' => 'ios',
                    'osversion' => '16.6.1',
                    'wifi_mac_address' => '60:99:ab:1a:a6:c9',
                    'last_lan_ip' => '192.168.1.42',
                    'username' => 'ada',
                    'useremail' => 'ada@example.com',
                    'date_info' => '1694210840',
                    'battery' => '0.81',
                    'is_supervised' => '1',
                    'enrollment_type' => 'GENERAL',
                    'status' => 'IN',
                ]]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('mosyle_business', $record->sourceKey);
        $this->assertSame('biz-full', $record->sourceId);
        $this->assertSame('biz-full-device', $record->hostname);
        $this->assertSame('iPad', $record->hardwareModel);
        $this->assertSame('C02-BIZ', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('192.168.1.42', $record->primaryIp);
        $this->assertSame('ada', $record->assignedUserName);
        $this->assertSame('ada@example.com', $record->assignedUserEmail);
        $this->assertSame('0.81', $record->extra['mosyle_battery']);
        $this->assertSame('GENERAL', $record->extra['mosyle_enrollment_type']);
        $this->assertNotNull($record->lastSeen);
        $this->assertSame(1694210840, $record->lastSeen->getTimestamp());
    }

    public function test_falls_back_to_device_model_when_device_model_name_is_absent(): void
    {
        // Business response in the real API returns `device_model`
        // (e.g. 'iPad12,1') but may omit `device_model_name` on some
        // devices. Manager v2 returns both. Normalize prefers
        // device_model_name but falls back to device_model so no
        // record lands without a model.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::sequence()
                ->push($this->devicesResponse([[
                    'deviceudid' => 'fallback',
                    'serial_number' => 'XZ',
                    'device_model' => 'iPad12,1',
                    'os' => 'ios',
                ]]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([]))
                ->push($this->devicesResponse([])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertSame('iPad12,1', $records[0]->hardwareModel);
    }

    public function test_login_missing_authorization_header_throws(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/login' => Http::response(['status' => 'OK'], 200),
            '*/devices' => Http::response($this->devicesResponse([])),
        ]);

        $this->expectExceptionMessage('Mosyle Business /login did not return a Bearer token');

        iterator_to_array($adapter->pull());
    }

    /**
     * @return array<string, mixed>
     */
    private function devicesResponse(array $devices): array
    {
        // Business response shape: `response` is an ARRAY wrapper.
        return [
            'status' => 'OK',
            'response' => [[
                'devices' => $devices,
                'rows' => count($devices),
                'page' => 1,
                'page_size' => 200,
            ]],
        ];
    }

    private function configuredAdapter(): MosyleBusinessAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle_business')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://businessapi.mosyle.com/v1');
        SyncAdapterConfig::put($instance->id, 'access_token', Crypt::encrypt('fake-access-token'));
        SyncAdapterConfig::put($instance->id, 'email', 'sync-service@example.com');
        SyncAdapterConfig::put($instance->id, 'password', Crypt::encrypt('fake-password'));

        return new MosyleBusinessAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function businessDevice(
        string $udid,
        string $name = 'device',
        string $model = 'iPad',
        ?string $serial = null,
        ?string $os = 'ios',
    ): array {
        return [
            'deviceudid' => $udid,
            'device_name' => $name,
            'device_model_name' => $model,
            'serial_number' => $serial,
            'os' => $os,
        ];
    }
}
