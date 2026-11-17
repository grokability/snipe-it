<?php

namespace Tests\Feature\SyncAdapters\EndpointCentral;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\EndpointCentral\EndpointCentralAdapter;
use App\SyncAdapters\EndpointCentral\EndpointCentralClient;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EndpointCentralAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_cloud_mode_exchanges_refresh_token_and_pulls_computers()
    {
        $adapter = $this->configuredCloudAdapter();

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'fake-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]),
            '*/dcapi/som/computersInfo*' => Http::response($this->computersListResponse([
                $this->computer(id: 'ec-1', name: 'win-01', ip: '10.0.0.10'),
                $this->computer(id: 'ec-2', name: 'win-02', ip: '10.0.0.11'),
            ])),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'endpoint_central', 'external_id' => 'ec-1']);
        $this->assertDatabaseHas('assets', ['name' => 'win-01']);
    }

    public function test_access_token_is_cached_across_requests_within_a_pull()
    {
        $adapter = $this->configuredCloudAdapter();

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'fake-access-token',
                'expires_in' => 3600,
            ]),
            '*/dcapi/som/computersInfo*' => Http::response($this->computersListResponse([
                $this->computer(id: 'ec-1', name: 'host1'),
                $this->computer(id: 'ec-2', name: 'host2'),
            ])),
        ]);

        iterator_to_array($adapter->pull());

        $tokenCalls = 0;
        Http::assertSent(function ($request) use (&$tokenCalls) {
            if (str_contains($request->url(), 'accounts.zoho.com/oauth/v2/token')) {
                $tokenCalls++;
            }

            return true;
        });

        $this->assertSame(1, $tokenCalls, 'OAuth token endpoint should be hit at most once per pull');
    }

    public function test_zoho_data_center_setting_routes_to_regional_accounts_host()
    {
        $instance = SyncAdapterInstance::where('slug', 'endpoint_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://desktopcentralcloud.manageengine.eu');
        SyncAdapterConfig::put($instance->id, 'mode', EndpointCentralClient::MODE_CLOUD);
        SyncAdapterConfig::put($instance->id, 'data_center', 'eu');
        SyncAdapterConfig::put($instance->id, 'client_id', 'fake-client-id');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-client-secret'));
        SyncAdapterConfig::put($instance->id, 'refresh_token', Crypt::encrypt('fake-refresh-token'));

        $adapter = new EndpointCentralAdapter($instance->fresh());

        Http::fake([
            'accounts.zoho.eu/oauth/v2/token' => Http::response([
                'access_token' => 'eu-token',
                'expires_in' => 3600,
            ]),
            '*/dcapi/som/computersInfo*' => Http::response($this->computersListResponse([])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn($request) => str_contains($request->url(), 'accounts.zoho.eu/oauth/v2/token'));
        Http::assertNotSent(fn($request) => str_contains($request->url(), 'accounts.zoho.com/oauth/v2/token'));
    }

    public function test_onprem_mode_sends_authtoken_header_and_skips_oauth()
    {
        $instance = SyncAdapterInstance::where('slug', 'endpoint_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://desktopcentral.example.com');
        SyncAdapterConfig::put($instance->id, 'mode', EndpointCentralClient::MODE_ONPREM);
        SyncAdapterConfig::put($instance->id, 'auth_token', Crypt::encrypt('fake-onprem-token'));

        $adapter = new EndpointCentralAdapter($instance->fresh());

        Http::fake([
            '*/dcapi/som/computersInfo*' => Http::response($this->computersListResponse([
                $this->computer(id: 'ec-op-1', name: 'onprem-host'),
            ])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertNotSent(fn($request) => str_contains($request->url(), 'accounts.zoho'));
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Zoho-authtoken fake-onprem-token');
        });
    }

    public function test_fetch_hardware_details_toggle_resolves_serial_via_per_device_call()
    {
        $instance = SyncAdapterInstance::where('slug', 'endpoint_central')->firstOrFail();
        $this->seedCloudCredentials($instance);
        SyncAdapterConfig::put($instance->id, 'fetch_hardware_details', '1');

        $adapter = new EndpointCentralAdapter($instance->fresh());

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/dcapi/som/computersInfo*' => Http::response($this->computersListResponse([
                $this->computer(id: 'ec-1', name: 'mac-01'),
            ])),
            '*/dcapi/inventory/computers/ec-1/hardware' => Http::response([
                'message_response' => [
                    'biosDetails' => [
                        [
                            'serialNumber' => 'ABC123SERIAL',
                            'hardwareManufacturer' => 'Dell',
                        ]
                    ],
                    'motherBoardDetails' => [
                        [
                            'hardwareName' => 'Latitude 7420',
                            'hardwareManufacturer' => 'Dell',
                        ]
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('ABC123SERIAL', $records[0]->hardwareSerial);
        $this->assertSame('Latitude 7420', $records[0]->hardwareModel);
        $this->assertSame('Dell', $records[0]->manufacturer);
    }

    public function test_fetch_hardware_details_off_leaves_serial_null()
    {
        $adapter = $this->configuredCloudAdapter();

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/dcapi/som/computersInfo*' => Http::response($this->computersListResponse([
                $this->computer(id: 'ec-1', name: 'mac-01'),
            ])),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertNull($records[0]->hardwareSerial);
        Http::assertNotSent(fn($request) => str_contains($request->url(), '/hardware'));
    }

    public function test_push_sends_remarks_update_with_asset_tag()
    {
        $instance = SyncAdapterInstance::where('slug', 'endpoint_central')->firstOrFail();
        $this->seedCloudCredentials($instance);
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $adapter = new EndpointCentralAdapter($instance->fresh());

        $asset = \App\Models\Asset::factory()->create([
            'asset_tag' => 'SNIPE-9001',
            'serial' => 'ABC123',
        ]);
        \App\Models\AssetExternalSource::query()->create([
            'asset_id' => $asset->id,
            'source' => 'endpoint_central',
            'external_id' => 'ec-push-1',
            'company_id' => null,
        ]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/dcapi/som/computers/updateRemarks' => Http::response(['status' => 'success']),
        ]);

        $this->assertTrue($adapter->push($asset, ['asset_tag']));

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/updateRemarks')) {
                return false;
            }
            $body = json_decode($request->body(), true);

            return ($body['resourceIds'][0] ?? null) === 'ec-push-1'
                && ($body['remarks'] ?? null) === 'SNIPE-9001';
        });
    }

    private function configuredCloudAdapter(): EndpointCentralAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'endpoint_central')->firstOrFail();
        $this->seedCloudCredentials($instance);

        return new EndpointCentralAdapter($instance->fresh());
    }

    private function seedCloudCredentials(SyncAdapterInstance $instance): void
    {
        SyncAdapterConfig::put($instance->id, 'url', 'https://desktopcentralcloud.manageengine.com');
        SyncAdapterConfig::put($instance->id, 'mode', EndpointCentralClient::MODE_CLOUD);
        SyncAdapterConfig::put($instance->id, 'data_center', 'us');
        SyncAdapterConfig::put($instance->id, 'client_id', 'fake-client-id');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-client-secret'));
        SyncAdapterConfig::put($instance->id, 'refresh_token', Crypt::encrypt('fake-refresh-token'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $computers
     * @return array<string, mixed>
     */
    private function computersListResponse(array $computers): array
    {
        return ['message_response' => ['computers' => $computers]];
    }

    /**
     * @return array<string, mixed>
     */
    private function computer(
        string $id,
        string $name = 'host',
        ?string $ip = null,
        ?string $mac = null,
        ?string $os = 'Windows 10 Professional Edition (x64)',
        ?string $osPlatform = 'Windows',
        ?string $osVersion = '10.0.19045',
        ?string $lastContactTime = '2026-01-15T10:00:00Z',
    ): array {
        return [
            'computerId' => $id,
            'computerName' => $name,
            'computerFQDNName' => $name . '.example.com',
            'ipAddress' => $ip,
            'macAddress' => $mac,
            'osName' => $os,
            'osPlatform' => $osPlatform,
            'osVersion' => $osVersion,
            'lastContactTime' => $lastContactTime,
            'liveStatus' => 'Live',
            'agentVersion' => '11.0',
            'loggedUsers' => null,
            'domainName' => 'EXAMPLE',
            'remoteOfficeID' => '1',
            'remoteOfficeName' => 'HQ',
        ];
    }
}
