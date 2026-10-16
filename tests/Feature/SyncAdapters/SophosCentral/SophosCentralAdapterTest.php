<?php

namespace Tests\Feature\SyncAdapters\SophosCentral;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use App\SyncAdapters\SophosCentral\SophosCentralAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * End-to-end coverage for the Sophos Central adapter through
 * SyncAdapter. Fakes the token exchange at id.sophos.com, the whoami
 * tenant discovery on the global API host, and the endpoint list on the
 * discovered regional data host.
 */
class SophosCentralAdapterTest extends TestCase
{
    private const DATA_HOST = 'https://api-us03.central.sophos.com';

    private const TENANT_ID = '11111111-2222-3333-4444-555555555555';

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_endpoints_and_creates_assets_keyed_on_serial()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-1', serial: 'C02AAA111', hostname: 'mac-one'),
            $this->endpoint(id: 'ep-2', serial: 'C02BBB222', hostname: 'mac-two'),
        ]]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'sophos_central', 'external_id' => 'C02AAA111']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'sophos_central', 'external_id' => 'C02BBB222']);
        $this->assertDatabaseHas('assets', ['serial' => 'C02AAA111', 'name' => 'mac-one']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-7', serial: 'C02XYZ789', hostname: 'alice-mbp') + [
                'macAddresses' => ['3c:22:fb:00:11:22', '3c:22:fb:00:11:33'],
                'ipv4Addresses' => ['10.0.0.7'],
                'health' => ['overall' => 'good', 'threats' => ['status' => 'good'], 'services' => ['status' => 'good']],
                'tamperProtectionEnabled' => true,
                'encryption' => ['overallStatus' => 'encrypted'],
                'isolation' => ['status' => 'notIsolated'],
                'associatedPerson' => ['name' => 'Alice Example', 'viaLogin' => 'alice-mbp\\alice'],
                'assignedProducts' => [
                    ['code' => 'interceptX', 'status' => 'installed'],
                    ['code' => 'coreAgent', 'status' => 'installed'],
                    ['code' => 'xdr', 'status' => 'notInstalled'],
                ],
                'group' => ['name' => 'Laptops'],
            ],
        ]]);

        $records = iterator_to_array($adapter->pull(), false);
        $this->assertCount(1, $records);
        $record = $records[0];

        $this->assertSame('sophos_central', $record->sourceKey);
        $this->assertSame('C02XYZ789', $record->sourceId);
        $this->assertSame('C02XYZ789', $record->hardwareSerial);
        $this->assertSame('alice-mbp', $record->hostname);
        $this->assertSame('macOS computer (model unknown)', $record->hardwareModel);
        $this->assertNull($record->manufacturer);
        $this->assertSame('3c:22:fb:00:11:22', $record->primaryMac);
        $this->assertSame('10.0.0.7', $record->primaryIp);
        $this->assertSame('macOS Tahoe', $record->os);
        $this->assertSame('26.6', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
        $this->assertSame('ep-7', $record->extra['sophos_endpoint_id']);
        $this->assertSame('computer', $record->extra['sophos_endpoint_type']);
        $this->assertSame('good', $record->extra['sophos_health']);
        $this->assertTrue($record->extra['sophos_tamper_protection']);
        $this->assertSame('encrypted', $record->extra['sophos_encryption_status']);
        $this->assertSame('notIsolated', $record->extra['sophos_isolation_status']);
        $this->assertSame('Alice Example', $record->extra['sophos_last_user']);
        $this->assertSame('coreAgent, interceptX', $record->extra['sophos_installed_products']);
        $this->assertSame('Laptops', $record->extra['sophos_group']);
    }

    public function test_admin_defined_extras_overlay_a_dot_path_from_the_endpoint()
    {
        // An admin maps a Sophos field the adapter doesn't preset (the
        // login name, kept apart from the person's display name) to a
        // Snipe-IT custom field through the Custom Extras widget.
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Sophos login']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'sophos_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'field_paths', json_encode([
            'custom:'.$customField->id => 'associatedPerson.viaLogin',
        ]));
        SyncAdapterConfig::put($instance->id, 'mapping.custom:'.$customField->id, 'custom:'.$customField->id);
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-9', serial: 'C02LOGIN99') + [
                'associatedPerson' => ['name' => 'Alice Example', 'viaLogin' => 'alice-mbp\\alice'],
            ],
        ]]);

        $record = iterator_to_array($adapter->pull(), false)[0];

        $this->assertSame('alice-mbp\\alice', $record->extra['custom:'.$customField->id]);
        $this->assertSame('Alice Example', $record->extra['sophos_last_user']);
    }

    public function test_admin_defined_extras_join_the_presets_in_the_mapping_picker()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Sophos login']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'sophos_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'field_paths', json_encode([
            'custom:'.$customField->id => 'associatedPerson.viaLogin',
        ]));

        $extraFields = $adapter->extraFields();

        $this->assertTrue($adapter->supportsAdminDefinedExtras());
        $this->assertTrue($extraFields['custom:'.$customField->id]['admin_defined']);
        $this->assertArrayHasKey('sophos_health', $extraFields);
        $this->assertArrayHasKey('sophos_tamper_protection', $extraFields);
    }

    public function test_follows_next_key_across_pages()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([
            [$this->endpoint(id: 'ep-1', serial: 'SERIAL-1'), $this->endpoint(id: 'ep-2', serial: 'SERIAL-2')],
            [$this->endpoint(id: 'ep-3', serial: 'SERIAL-3')],
        ]);

        $records = iterator_to_array($adapter->pull(), false);

        $this->assertCount(3, $records);
        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), self::DATA_HOST.'/endpoint/v1/endpoints')
            && str_contains($request->url(), 'pageFromKey=page-2'));
    }

    public function test_sends_bearer_and_tenant_header_to_the_discovered_data_host()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[$this->endpoint(id: 'ep-1', serial: 'SERIAL-1')]]);

        iterator_to_array($adapter->pull(), false);

        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), self::DATA_HOST.'/endpoint/v1/endpoints')
            && $request->hasHeader('Authorization', 'Bearer stub-bearer')
            && $request->hasHeader('X-Tenant-ID', self::TENANT_ID)
            && str_contains($request->url(), 'view=full'));
    }

    public function test_refuses_a_data_host_outside_sophos()
    {
        $adapter = $this->configuredAdapter();
        Http::fake([
            'https://id.sophos.com/*' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            'https://api.central.sophos.com/whoami/v1' => Http::response([
                'id' => self::TENANT_ID,
                'idType' => 'tenant',
                'apiHosts' => ['global' => 'https://api.central.sophos.com', 'dataRegion' => 'https://evil.example.com'],
            ]),
            '*' => Http::response([], 500),
        ]);

        try {
            iterator_to_array($adapter->pull(), false);
            $this->fail('Expected the pull to refuse a non-Sophos data host.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('unexpected tenant id or data host', $e->getMessage());
        }

        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'evil.example.com'));
    }

    public function test_refuses_partner_level_credentials()
    {
        $adapter = $this->configuredAdapter();
        Http::fake([
            'https://id.sophos.com/*' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            'https://api.central.sophos.com/whoami/v1' => Http::response([
                'id' => 'partner-1',
                'idType' => 'partner',
                'apiHosts' => ['global' => 'https://api.central.sophos.com'],
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('partner-level');
        iterator_to_array($adapter->pull(), false);
    }

    public function test_endpoint_without_a_serial_falls_back_to_the_endpoint_id()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[$this->endpoint(id: 'ep-no-serial', serial: null, hostname: 'no-serial-box')]]);

        $records = iterator_to_array($adapter->pull(), false);

        $this->assertCount(1, $records);
        $this->assertSame('ep-no-serial', $records[0]->sourceId);
        $this->assertNull($records[0]->hardwareSerial);
    }

    public function test_duplicate_serials_keep_the_most_recently_seen_endpoint()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-old', serial: 'SAME-SERIAL', hostname: 'old-owner-mbp', lastSeen: '2025-03-27T10:00:00.000Z'),
            $this->endpoint(id: 'ep-new', serial: 'SAME-SERIAL', hostname: 'new-owner-mbp', lastSeen: '2026-09-23T10:00:00.000Z'),
        ]]);

        $records = iterator_to_array($adapter->pull(), false);

        $this->assertCount(1, $records);
        $this->assertSame('new-owner-mbp', $records[0]->hostname);
        $this->assertSame('ep-new', $records[0]->extra['sophos_endpoint_id']);
    }

    public function test_newest_record_is_chosen_by_date_not_by_text()
    {
        // As text, "12:00+02:00" sorts after "11:00Z", but as a moment in
        // time it is 10:00 UTC, an hour earlier. A record with no timestamp
        // counts as the oldest.
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-offset', serial: 'SAME-SERIAL', hostname: 'earlier', lastSeen: '2026-09-23T12:00:00+02:00'),
            $this->endpoint(id: 'ep-utc', serial: 'SAME-SERIAL', hostname: 'later', lastSeen: '2026-09-23T11:00:00.000Z'),
            $this->endpoint(id: 'ep-none', serial: 'SAME-SERIAL', hostname: 'never-seen', lastSeen: ''),
        ]]);

        $records = iterator_to_array($adapter->pull(), false);

        $this->assertCount(1, $records);
        $this->assertSame('later', $records[0]->hostname);
    }

    public function test_os_name_and_version_are_split_per_platform()
    {
        $adapter = $this->configuredAdapter(endpointTypes: ['computer', 'server']);
        $this->fakeSophos([[
            array_merge($this->endpoint(id: 'ep-mac', serial: 'MAC-1'),
                ['os' => ['platform' => 'macOS', 'name' => 'macOS Sonoma 14', 'majorVersion' => 14, 'minorVersion' => 6]]),
            array_merge($this->endpoint(id: 'ep-ubuntu', serial: 'LNX-1', type: 'server'),
                ['os' => ['platform' => 'linux', 'name' => 'Ubuntu 24.04.3 LTS', 'majorVersion' => 24, 'minorVersion' => 4]]),
            array_merge($this->endpoint(id: 'ep-ubuntu-bare', serial: 'LNX-2', type: 'server'),
                ['os' => ['platform' => 'linux', 'name' => 'Ubuntu 22.04.2 LTS']]),
            array_merge($this->endpoint(id: 'ep-win', serial: 'WIN-1'),
                ['os' => ['platform' => 'windows', 'name' => 'Windows 11 Pro ', 'majorVersion' => 11, 'minorVersion' => 0, 'build' => 26100]]),
        ]]);

        $byId = collect(iterator_to_array($adapter->pull(), false))->keyBy('sourceId');

        // macOS: the numbers are more precise than the name.
        $this->assertSame(['macOS Sonoma', '14.6'], [$byId['MAC-1']->os, $byId['MAC-1']->osVersion]);
        // Linux: the numbers drop Ubuntu's leading zero, so the name wins.
        $this->assertSame(['Ubuntu LTS', '24.04.3'], [$byId['LNX-1']->os, $byId['LNX-1']->osVersion]);
        $this->assertSame(['Ubuntu LTS', '22.04.2'], [$byId['LNX-2']->os, $byId['LNX-2']->osVersion]);
        // Windows: the 11 is part of the product name; the numbers carry the build.
        $this->assertSame(['Windows 11 Pro', '11.0.26100'], [$byId['WIN-1']->os, $byId['WIN-1']->osVersion]);
    }

    public function test_pulls_computers_only_by_default()
    {
        $adapter = $this->configuredAdapter();
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-mac', serial: 'MAC-1'),
            $this->endpoint(id: 'ep-srv', serial: 'SRV-1', type: 'server', platform: 'linux', osName: 'Ubuntu 24.04.3 LTS'),
        ]]);

        $records = iterator_to_array($adapter->pull(), false);

        $this->assertSame(['MAC-1'], array_map(fn ($r) => $r->sourceId, $records));
    }

    public function test_type_selection_and_cloud_skip_filter_endpoints()
    {
        $adapter = $this->configuredAdapter(endpointTypes: ['computer', 'server'], skipCloud: true);
        $this->fakeSophos([[
            $this->endpoint(id: 'ep-mac', serial: 'MAC-1'),
            $this->endpoint(id: 'ep-laptop', serial: 'LNX-LAPTOP', type: 'server', platform: 'linux', osName: 'Ubuntu 25.10'),
            $this->endpoint(id: 'ep-aws', serial: 'EC2-1', type: 'server', platform: 'linux', osName: 'Ubuntu 24.04.3 LTS')
                + ['cloud' => ['provider' => 'aws', 'instanceId' => 'i-0123']],
        ]]);

        $records = iterator_to_array($adapter->pull(), false);

        $this->assertEqualsCanonicalizing(['MAC-1', 'LNX-LAPTOP'], array_map(fn ($r) => $r->sourceId, $records));
        $linux = collect($records)->firstWhere('sourceId', 'LNX-LAPTOP');
        $this->assertSame('Linux server (model unknown)', $linux->hardwareModel);
    }

    public function test_adopts_an_existing_asset_by_serial_when_the_toggle_is_on()
    {
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'sophos_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'adopt_by_serial', '1');
        $existing = Asset::factory()->create(['serial' => 'ADOPT-ME-001', 'name' => 'pre-existing']);

        $this->fakeSophos([[$this->endpoint(id: 'ep-1', serial: 'ADOPT-ME-001', hostname: 'sophos-sees-it')]]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseHas('asset_external_sources', [
            'asset_id' => $existing->id,
            'source' => 'sophos_central',
            'external_id' => 'ADOPT-ME-001',
        ]);
    }

    public function test_model_mapping_on_skip_keeps_an_adopted_assets_real_model()
    {
        // The setup for when another adapter already supplies real models:
        // adopt by serial, with the model mapping on skip. The adopted asset
        // keeps its model, and a device only Sophos sees still comes in with
        // the placeholder.
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'sophos_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'adopt_by_serial', '1');
        SyncAdapterConfig::put($instance->id, 'mapping.model', 'skip');
        $existing = Asset::factory()->create(['serial' => 'ADOPT-ME-001']);
        $realModelId = $existing->model_id;

        $this->fakeSophos([[
            $this->endpoint(id: 'ep-1', serial: 'ADOPT-ME-001'),
            $this->endpoint(id: 'ep-2', serial: 'SOPHOS-ONLY-1'),
        ]]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertSame($realModelId, $existing->fresh()->model_id);
        $created = Asset::where('serial', 'SOPHOS-ONLY-1')->firstOrFail();
        $this->assertSame('macOS computer (model unknown)', $created->model->name);
    }

    public function test_a_reinstalled_endpoint_stays_on_the_same_asset()
    {
        // Sophos gives a reinstall a new endpoint id. Keying on the serial
        // keeps the machine on the asset the first sync created.
        $adapter = $this->configuredAdapter();

        // Two separate pulls: each page is its own run, so neither carries a nextKey.
        $this->fakeSophos([
            [$this->endpoint(id: 'ep-before', serial: 'REINSTALLED-1', hostname: 'before')],
            [$this->endpoint(id: 'ep-after', serial: 'REINSTALLED-1', hostname: 'after')],
        ], chained: false);
        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }
        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseCount('asset_external_sources', 1);
        $this->assertDatabaseHas('assets', ['serial' => 'REINSTALLED-1', 'name' => 'after']);
    }

    public function test_settings_page_renders_the_sophos_schema_fields()
    {
        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Sophos Central', $html);
        $this->assertMatchesRegularExpression('/name="sophos_central_client_id"/', $html);
        $this->assertMatchesRegularExpression('/name="sophos_central_client_secret"/', $html);
        // The Custom Extras widget renders, since the adapter opts in.
        $this->assertStringContainsString('sophos_central_field_paths', $html);
    }

    /**
     * @param  array<int, string>  $endpointTypes
     */
    private function configuredAdapter(array $endpointTypes = [], bool $skipCloud = false): SophosCentralAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'sophos_central')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-secret'));
        if ($endpointTypes !== []) {
            SyncAdapterConfig::put($instance->id, 'endpoint_types', json_encode($endpointTypes));
        }
        if ($skipCloud) {
            SyncAdapterConfig::put($instance->id, 'skip_cloud_instances', '1');
        }

        return new SophosCentralAdapter($instance->fresh());
    }

    /**
     * Fake the token, whoami and endpoint calls. Each element of $pages is
     * one page of endpoints. Chained pages carry a nextKey to the next one
     * (one pull, several pages); unchained pages each answer a separate pull.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $pages
     */
    private function fakeSophos(array $pages, bool $chained = true): void
    {
        $sequence = Http::sequence();
        foreach ($pages as $i => $items) {
            $isLast = ! $chained || $i === array_key_last($pages);
            $sequence->push([
                'items' => $items,
                'pages' => array_filter([
                    'fromKey' => ($i === 0 || ! $chained) ? null : 'page-'.($i + 1),
                    'nextKey' => $isLast ? null : 'page-'.($i + 2),
                    'size' => count($items),
                    'maxSize' => 500,
                ], fn ($value) => $value !== null),
            ]);
        }

        Http::fake([
            'https://id.sophos.com/*' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            'https://api.central.sophos.com/whoami/v1' => Http::response([
                'id' => self::TENANT_ID,
                'idType' => 'tenant',
                'apiHosts' => ['global' => 'https://api.central.sophos.com', 'dataRegion' => self::DATA_HOST],
            ]),
            self::DATA_HOST.'/endpoint/v1/endpoints*' => $sequence,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function endpoint(
        string $id,
        ?string $serial,
        string $hostname = 'host',
        string $type = 'computer',
        string $platform = 'macOS',
        string $osName = 'macOS Tahoe 26.6',
        string $lastSeen = '2026-09-23T10:00:00.000Z',
    ): array {
        return array_filter([
            'id' => $id,
            'type' => $type,
            'hostname' => $hostname,
            'serialNumber' => $serial,
            'os' => ['isServer' => $type === 'server', 'platform' => $platform, 'name' => $osName, 'majorVersion' => 26, 'minorVersion' => 6],
            'lastSeenAt' => $lastSeen,
        ], fn ($value) => $value !== null);
    }
}
