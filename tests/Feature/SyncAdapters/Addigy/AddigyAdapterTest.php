<?php

namespace Tests\Feature\SyncAdapters\Addigy;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Addigy\AddigyAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Addigy adapter against the Addigy API v2
 * surface. Mocks the paginated POST /devices endpoint and asserts that
 * assets + external ids land correctly, that the single x-api-key
 * header goes out on the request, and that device-fact values are read
 * out of the v2 `facts` map rather than top-level v1 field names
 * (#19955).
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
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [
                    $this->addigyDevice(agentid: 'agent-1', device_name: 'design-mbp', device_model_name: 'MacBook Pro'),
                    $this->addigyDevice(agentid: 'agent-2', device_name: 'staff-imac', device_model_name: 'iMac'),
                ],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 2, 'total' => 2],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'addigy', 'external_id' => 'agent-1']);
        $this->assertDatabaseHas('assets', ['name' => 'design-mbp']);
    }

    public function test_x_api_key_header_is_sent_not_bearer_or_v1_pair()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [],
                'metadata' => ['page' => 1, 'page_count' => 0, 'per_page' => 100, 'result_count' => 0, 'total' => 0],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        // Addigy API v2 authenticates with a single x-api-key header.
        // A regression to withToken() (Authorization: Bearer) or to the
        // retired v1 client-id + client-secret pair would silently
        // 401 against a real tenant.
        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'fake-addigy-api-key')
                && ! $request->hasHeader('Authorization')
                && ! $request->hasHeader('client-id')
                && ! $request->hasHeader('client-secret');
        });
    }

    public function test_devices_endpoint_posts_with_desired_facts_and_pagination()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [],
                'metadata' => ['page' => 1, 'page_count' => 0, 'per_page' => 100, 'result_count' => 0, 'total' => 0],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            if ($request->method() !== 'POST') {
                return false;
            }
            $body = $request->data();

            return is_array($body['desired_fact_identifiers'] ?? null)
                && in_array('serial_number', $body['desired_fact_identifiers'], true)
                && in_array('device_model_name', $body['desired_fact_identifiers'], true)
                && isset($body['page'], $body['per_page']);
        });
    }

    public function test_normalized_record_reads_fact_values_out_of_the_v2_facts_map()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [
                    $this->addigyDevice(
                        agentid: 'agent-7',
                        device_name: 'lab-mbp',
                        device_model_name: 'MacBookPro18,3',
                        serial_number: 'C02XYZ',
                        network_mac_address: 'aa:bb:cc:11:22:33',
                        network_ip_address: '10.0.0.42',
                        os_type: 'macOS',
                        os_version: '14.2.1',
                        last_online_at: '2026-01-15T10:00:00.000Z',
                    ),
                ],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 1, 'total' => 1],
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

    public function test_fact_with_error_msg_falls_through_to_null()
    {
        // A tenant that doesn't have a built-in fact (or doesn't grant
        // the API key read access to it) returns the fact entry with an
        // error_msg set. normalize() must treat that as absent.
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [[
                    'agentid' => 'agent-err',
                    'facts' => [
                        'device_name' => ['type' => 'string', 'value' => 'present-host'],
                        'serial_number' => ['type' => 'string', 'error_msg' => 'fact unavailable'],
                    ],
                ]],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 1, 'total' => 1],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertSame('present-host', $records[0]->hostname);
        $this->assertNull($records[0]->hardwareSerial);
    }

    public function test_pagination_follows_metadata_page_count_until_exhausted()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::sequence()
                ->push([
                    'items' => [$this->addigyDevice(agentid: 'p1-a', device_name: 'page1-a')],
                    'metadata' => ['page' => 1, 'page_count' => 2, 'per_page' => 100, 'result_count' => 1, 'total' => 2],
                ])
                ->push([
                    'items' => [$this->addigyDevice(agentid: 'p2-a', device_name: 'page2-a')],
                    'metadata' => ['page' => 2, 'page_count' => 2, 'per_page' => 100, 'result_count' => 1, 'total' => 2],
                ])
                ->push([
                    'items' => [],
                    'metadata' => ['page' => 3, 'page_count' => 2, 'per_page' => 100, 'result_count' => 0, 'total' => 2],
                ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(2, $records);
        $this->assertSame('page1-a', $records[0]->hostname);
        $this->assertSame('page2-a', $records[1]->hostname);
    }

    public function test_unix_epoch_last_online_at_is_parsed_as_epoch_seconds()
    {
        // Addigy occasionally emits last_online_at as a Unix epoch int.
        // Carbon::parse mis-reads that as a bare year, so parseTimestamp
        // routes numerics to createFromTimestamp.
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [[
                    'agentid' => 'agent-epoch',
                    'facts' => [
                        'device_name' => ['type' => 'string', 'value' => 'epoch-host'],
                        'last_online_at' => ['type' => 'number', 'value' => 1737112800],
                    ],
                ]],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 1, 'total' => 1],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertSame(2025, $records[0]->lastSeen->year);
    }

    public function test_fetch_groups_posts_to_v2_policies_query_endpoint()
    {
        $adapter = $this->configuredAddigyAdapter();

        Http::fake([
            'api.addigy.com/api/v2/oa/policies/query' => Http::response([
                ['policyId' => 'pol-1', 'name' => 'Engineering'],
                ['policyId' => 'pol-2', 'name' => 'Executive'],
            ]),
        ]);

        $groups = $adapter->fetchGroups();

        $this->assertSame([
            ['id' => 'pol-1', 'label' => 'Engineering'],
            ['id' => 'pol-2', 'label' => 'Executive'],
        ], $groups);
    }

    public function test_pull_captures_orgid_from_device_audit_response_for_later_push_use()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instanceId = $adapter->instance()->id;

        Http::fake([
            'api.addigy.com/api/v2/devices' => Http::response([
                'items' => [[
                    'agentid' => 'agent-cache',
                    'orgid' => 'org-cached-123',
                    'facts' => ['device_name' => ['type' => 'string', 'value' => 'orgcache-host']],
                ]],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 1, 'total' => 1],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        $this->assertSame('org-cached-123', SyncAdapterConfig::get($instanceId, 'cached_orgid'));
    }

    public function test_push_set_device_name_fires_the_mdm_command_when_hostname_is_push_directed()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instance = $adapter->instance();

        // Direct hostname toward push.
        SyncAdapterConfig::put($instance->id, 'direction.hostname', 'push');

        $asset = \App\Models\Asset::factory()->create(['name' => 'pushed-mbp']);
        $this->seedExternalSource($asset->id, 'agent-push-1');

        Http::fake([
            'api.addigy.com/api/v2/mdm/settings/device/name' => Http::response([], 200),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/mdm/settings/device/name')) {
                return false;
            }
            $body = $request->data();

            return ($body['agent_id'] ?? null) === 'agent-push-1'
                && ($body['device_name'] ?? null) === 'pushed-mbp';
        });
    }

    public function test_push_skips_hostname_when_direction_is_not_push()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instance = $adapter->instance();

        // No direction stored: default is 'pull' for standard fields.
        $asset = \App\Models\Asset::factory()->create(['name' => 'quiet-host']);
        $this->seedExternalSource($asset->id, 'agent-quiet');

        Http::fake();

        $this->assertFalse($adapter->push($asset));

        Http::assertNothingSent();
    }

    public function test_push_end_user_assignment_resolves_by_email_and_posts_the_assignment()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instance = $adapter->instance();

        SyncAdapterConfig::put($instance->id, 'push_end_user_assignment', '1');
        SyncAdapterConfig::put($instance->id, 'cached_orgid', 'org-xyz');

        $user = \App\Models\User::factory()->create(['email' => 'alice@acme.test']);
        $asset = \App\Models\Asset::factory()->create([
            'assigned_to' => $user->id,
            'assigned_type' => \App\Models\User::class,
        ]);
        $this->seedExternalSource($asset->id, 'agent-user-1');

        Http::fake([
            'api.addigy.com/api/v2/o/org-xyz/end-users/query' => Http::response([
                'items' => [[
                    'id' => 'end-user-42',
                    'emails' => [['value' => 'alice@acme.test', 'type' => 'work']],
                ]],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 1, 'total' => 1],
            ]),
            'api.addigy.com/api/v2/o/org-xyz/end-users/devices' => Http::response([], 200),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/o/org-xyz/end-users/devices')
                && ($request->data()['agent_id'] ?? null) === 'agent-user-1'
                && ($request->data()['end_user_id'] ?? null) === 'end-user-42';
        });
    }

    public function test_push_end_user_assignment_skips_when_cached_orgid_is_missing()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instance = $adapter->instance();

        SyncAdapterConfig::put($instance->id, 'push_end_user_assignment', '1');
        // No cached_orgid set.

        $user = \App\Models\User::factory()->create(['email' => 'bob@acme.test']);
        $asset = \App\Models\Asset::factory()->create([
            'assigned_to' => $user->id,
            'assigned_type' => \App\Models\User::class,
        ]);
        $this->seedExternalSource($asset->id, 'agent-user-2');

        Http::fake();

        $this->assertFalse($adapter->push($asset));

        Http::assertNothingSent();
    }

    public function test_push_end_user_assignment_skips_when_no_unique_match_for_email()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instance = $adapter->instance();

        SyncAdapterConfig::put($instance->id, 'push_end_user_assignment', '1');
        SyncAdapterConfig::put($instance->id, 'cached_orgid', 'org-xyz');

        $user = \App\Models\User::factory()->create(['email' => 'ambig@acme.test']);
        $asset = \App\Models\Asset::factory()->create([
            'assigned_to' => $user->id,
            'assigned_type' => \App\Models\User::class,
        ]);
        $this->seedExternalSource($asset->id, 'agent-user-3');

        Http::fake([
            'api.addigy.com/api/v2/o/org-xyz/end-users/query' => Http::response([
                'items' => [
                    ['id' => 'eu-1', 'emails' => [['value' => 'ambig@acme.test']]],
                    ['id' => 'eu-2', 'emails' => [['value' => 'ambig@acme.test']]],
                ],
                'metadata' => ['page' => 1, 'page_count' => 1, 'per_page' => 100, 'result_count' => 2, 'total' => 2],
            ]),
        ]);

        $this->assertFalse($adapter->push($asset));

        // The query ran, but the assignment did not.
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/end-users/devices');
        });
    }

    public function test_push_end_user_assignment_is_gated_by_the_opt_in_flag()
    {
        $adapter = $this->configuredAddigyAdapter();
        $instance = $adapter->instance();

        // Opt-in flag off.
        SyncAdapterConfig::put($instance->id, 'cached_orgid', 'org-xyz');
        // Direct hostname so the push isn't a no-op across the board.
        SyncAdapterConfig::put($instance->id, 'direction.hostname', 'push');

        $user = \App\Models\User::factory()->create(['email' => 'ignored@acme.test']);
        $asset = \App\Models\Asset::factory()->create([
            'name' => 'host-with-user',
            'assigned_to' => $user->id,
            'assigned_type' => \App\Models\User::class,
        ]);
        $this->seedExternalSource($asset->id, 'agent-gated');

        Http::fake([
            'api.addigy.com/api/v2/mdm/settings/device/name' => Http::response([], 200),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/end-users/');
        });
    }

    public function test_can_push_returns_true_and_notes_target_is_null()
    {
        $adapter = $this->configuredAddigyAdapter();

        $this->assertTrue($adapter->canPush());
        $this->assertNull($adapter->notesFieldTarget(), 'Addigy exposes no device-level notes field.');
    }

    public function test_pushable_fields_only_includes_hostname()
    {
        $adapter = $this->configuredAddigyAdapter();

        // Narrowing hides push/both from the direction picker for
        // every other standard field. A regression that widens this
        // set would let admins opt into pushes that silently drop.
        $this->assertSame(['hostname'], $adapter->pushableFields());
    }

    private function configuredAddigyAdapter(): AddigyAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-addigy-api-key'));

        return new AddigyAdapter($instance->fresh());
    }

    private function seedExternalSource(int $assetId, string $externalId): void
    {
        \App\Models\AssetExternalSource::query()->create([
            'asset_id' => $assetId,
            'source' => 'addigy',
            'external_id' => $externalId,
            'company_id' => null,
        ]);
    }

    /**
     * Build a v2 DeviceAudit payload with the given fact values. Null
     * values are omitted from the facts map so tests can exercise the
     * "fact missing on this tenant" case.
     *
     * @return array<string, mixed>
     */
    private function addigyDevice(
        string $agentid,
        string $device_name = 'device',
        string $device_model_name = 'MacBook',
        ?string $serial_number = null,
        ?string $network_mac_address = null,
        ?string $network_ip_address = null,
        ?string $os_type = 'macOS',
        ?string $os_version = null,
        ?string $last_online_at = null,
    ): array {
        $facts = [];
        foreach ([
            'device_name' => $device_name,
            'device_model_name' => $device_model_name,
            'serial_number' => $serial_number,
            'network_mac_address' => $network_mac_address,
            'network_ip_address' => $network_ip_address,
            'os_type' => $os_type,
            'os_version' => $os_version,
            'last_online_at' => $last_online_at,
            'policy_id' => 'policy-abc',
            'agent_version' => '4.0.0',
        ] as $identifier => $value) {
            if ($value !== null) {
                $facts[$identifier] = ['type' => 'string', 'value' => $value];
            }
        }

        return [
            'agentid' => $agentid,
            'orgid' => 'org-test',
            'audit_date' => '2026-01-15T10:00:00Z',
            'facts' => $facts,
        ];
    }
}
