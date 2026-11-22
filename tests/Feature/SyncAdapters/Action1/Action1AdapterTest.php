<?php

namespace Tests\Feature\SyncAdapters\Action1;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Action1\Action1Adapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Action1 adapter. Fakes the OAuth2
 * client-credentials token exchange and the /endpoints/managed/{org}
 * list endpoint, and asserts assets + asset_external_sources rows land
 * correctly.
 */
class Action1AdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_managed_endpoints_and_creates_assets()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/oauth2/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/endpoints/managed/*' => Http::response($this->pagedResponse([
                $this->endpoint(id: 'guid-42', name: 'action1-host-1'),
                $this->endpoint(id: 'guid-99', name: 'action1-host-2'),
            ])),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'action1', 'external_id' => 'guid-42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'action1', 'external_id' => 'guid-99']);
        $this->assertDatabaseHas('assets', ['name' => 'action1-host-1']);
        $this->assertDatabaseHas('assets', ['name' => 'action1-host-2']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/oauth2/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/endpoints/managed/*' => Http::response($this->pagedResponse([
                [
                    'id' => 'A1-1234',
                    'name' => 'accounting-laptop-01',
                    'serial' => 'SN-ABC-123',
                    'manufacturer' => 'Dell Inc.',
                    'model' => 'Latitude 7440',
                    'OS' => 'Windows 11',
                    'os_version' => '10.0.22631',
                    'user' => 'alice',
                    'last_seen' => '2026-01-15T10:00:00Z',
                    'status' => 'Connected',
                    'reboot_required' => 'No',
                    'comment' => 'Finance department laptop',
                ],
            ])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('action1', $record->sourceKey);
        $this->assertSame('A1-1234', $record->sourceId);
        $this->assertSame('accounting-laptop-01', $record->hostname);
        $this->assertSame('SN-ABC-123', $record->hardwareSerial);
        $this->assertSame('Dell Inc.', $record->manufacturer);
        $this->assertSame('Latitude 7440', $record->hardwareModel);
        $this->assertSame('Windows 11', $record->os);
        $this->assertSame('10.0.22631', $record->osVersion);
        $this->assertSame('alice', $record->assignedUserName);
        $this->assertNotNull($record->lastSeen);
        $this->assertSame('Connected', $record->extra['action1_status']);
        $this->assertSame('No', $record->extra['action1_reboot_required']);
        $this->assertSame('Finance department laptop', $record->extra['action1_comment']);
    }

    public function test_pagination_walks_every_page()
    {
        $adapter = $this->configuredAdapter();

        $firstPage = array_map(
            fn (int $i) => $this->endpoint(id: 'p1-'.$i, name: 'host-'.$i),
            range(1, 200),
        );
        $secondPage = [$this->endpoint(id: 'p2-1', name: 'tail-host')];

        Http::fake([
            '*/oauth2/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/endpoints/managed/*' => Http::sequence()
                ->push(['items' => $firstPage, 'from' => 0, 'limit' => 200, 'total_items' => 201])
                ->push(['items' => $secondPage, 'from' => 200, 'limit' => 200, 'total_items' => 201]),
        ]);

        $records = iterator_to_array($adapter->pull(), preserve_keys: false);
        $this->assertCount(201, $records);
        $this->assertSame('p2-1', $records[200]->sourceId);
    }

    public function test_region_resolves_to_the_correct_regional_base_url()
    {
        $adapter = $this->configuredAdapter(region: 'europe');

        Http::fake([
            'https://app.eu.action1.com/*' => Http::response($this->pagedResponse([$this->endpoint()])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.eu.action1.com/api/3.0/'));
    }

    public function test_unknown_region_falls_back_to_north_america()
    {
        // Older instances or hand-edited config might carry a region
        // slug that no longer maps to a shipped URL. Falling back to
        // North America keeps the pull working instead of crashing.
        $adapter = $this->configuredAdapter(region: 'atlantis');

        Http::fake([
            'https://app.action1.com/*' => Http::response($this->pagedResponse([$this->endpoint()])),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.action1.com/api/3.0/'));
    }

    private function configuredAdapter(string $region = 'north_america'): Action1Adapter
    {
        $instance = SyncAdapterInstance::where('slug', 'action1')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'region', $region);
        SyncAdapterConfig::put($instance->id, 'org_id', '00000000-0000-0000-0000-000000000001');
        SyncAdapterConfig::put($instance->id, 'client_id', 'fake-client-id');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-client-secret'));

        return new Action1Adapter($instance->fresh());
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function pagedResponse(array $items): array
    {
        return [
            'items' => $items,
            'from' => 0,
            'limit' => 200,
            'total_items' => count($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function endpoint(string $id = 'stub-id', string $name = 'stub-host'): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'serial' => 'SN-'.$id,
            'manufacturer' => 'Generic',
            'model' => 'Generic Model',
            'OS' => 'Windows 11',
            'status' => 'Connected',
            'reboot_required' => 'No',
        ];
    }
}
