<?php

namespace Tests\Feature\SyncAdapters\CustomHttp;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\CustomHttp\CustomHttpAdapter;
use App\SyncAdapters\PushableSyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class CustomHttpAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pull_normalizes_records_via_configured_dot_paths()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'data.devices',
            'field_source_id' => 'id',
            'field_hostname' => 'name',
            'field_serial' => 'hardware.serial',
            'field_model' => 'hardware.model',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                'data' => [
                    'devices' => [
                        [
                            'id' => 'dev-1',
                            'name' => 'wksn-01',
                            'hardware' => ['serial' => 'SN-ABC', 'model' => 'MacBook Pro'],
                        ],
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame('dev-1', $records[0]->sourceId);
        $this->assertSame('wksn-01', $records[0]->hostname);
        $this->assertSame('SN-ABC', $records[0]->hardwareSerial);
        $this->assertSame('MacBook Pro', $records[0]->hardwareModel);
    }

    public function test_pull_handles_root_array_response_when_records_path_is_blank()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => '',
            'field_source_id' => 'uuid',
            'field_hostname' => 'hostname',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                ['uuid' => 'aaa', 'hostname' => 'host-a'],
                ['uuid' => 'bbb', 'hostname' => 'host-b'],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(2, $records);
        $this->assertSame(['aaa', 'bbb'], array_map(fn ($r) => $r->sourceId, $records));
    }

    public function test_pull_walks_numeric_dot_path_segments()
    {
        // Numeric segments index into sequential arrays, so an API
        // whose device list sits at a fixed position like
        // response.results.0.devices is representable without a
        // wrapper record-level accessor.
        $adapter = $this->configuredAdapter([
            'records_path' => 'results.0.devices',
            'field_source_id' => 'id',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                'results' => [
                    ['devices' => [['id' => 'first-only']]],
                    ['devices' => [['id' => 'never-yielded']]],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $this->assertSame('first-only', $records[0]->sourceId);
    }

    public function test_bearer_auth_sends_authorization_header()
    {
        $adapter = $this->configuredAdapter([
            'auth_method' => 'bearer',
            'bearer_token' => 'stub-bearer-secret',
            'field_source_id' => 'id',
        ]);

        Http::fake(['vendor.example/*' => Http::response([['id' => 'x']])]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer stub-bearer-secret'));
    }

    public function test_basic_auth_sends_basic_authorization_header()
    {
        $adapter = $this->configuredAdapter([
            'auth_method' => 'basic',
            'basic_username' => 'alice',
            'basic_password' => 'hunter2',
            'field_source_id' => 'id',
        ]);

        Http::fake(['vendor.example/*' => Http::response([['id' => 'x']])]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Basic '.base64_encode('alice:hunter2')));
    }

    public function test_api_key_auth_sends_configured_header_name()
    {
        $adapter = $this->configuredAdapter([
            'auth_method' => 'api_key',
            'api_key_header' => 'X-Vendor-Token',
            'api_key_value' => 'stub-api-key',
            'field_source_id' => 'id',
        ]);

        Http::fake(['vendor.example/*' => Http::response([['id' => 'x']])]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => $request->hasHeader('X-Vendor-Token', 'stub-api-key'));
    }

    public function test_none_auth_sends_no_authorization_header()
    {
        $adapter = $this->configuredAdapter([
            'auth_method' => 'none',
            'field_source_id' => 'id',
        ]);

        Http::fake(['vendor.example/*' => Http::response([['id' => 'x']])]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => ! $request->hasHeader('Authorization'));
    }

    public function test_custom_field_destinations_in_field_paths_route_values_through_extras()
    {
        // Under the unified Vendor Response Paths widget, admins pick
        // a custom-field destination directly in the field-map picker
        // and give it a dot-path. field_paths JSON stores the pair,
        // extraFields() derives from those custom: entries, and the
        // adapter's saveConfig hook writes mapping.<custom:X> = <same>
        // so the framework routes each extracted value.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_paths' => json_encode([
                'custom:5' => 'tags.asset_tag',
                'custom:6' => 'location.room',
            ]),
            'mapping.custom:5' => 'custom:5',
            'mapping.custom:6' => 'custom:6',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                [
                    'id' => 'dev-1',
                    'tags' => ['asset_tag' => 'ACME-123'],
                    'location' => ['room' => 'Server Room A'],
                ],
            ]),
        ]);

        $this->assertSame(
            [
                'custom:5' => ['label' => 'custom:5', 'admin_defined' => true],
                'custom:6' => ['label' => 'custom:6', 'admin_defined' => true],
            ],
            $adapter->extraFields(),
        );

        $records = iterator_to_array($adapter->pull());
        $this->assertSame('ACME-123', $records[0]->extra['custom:5']);
        $this->assertSame('Server Room A', $records[0]->extra['custom:6']);
    }

    public function test_saveconfig_persists_widget_picker_row_without_click_add()
    {
        $adapter = $this->configuredAdapter([]);
        $slug = $adapter->name();
        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();

        $adapter->saveConfig(\Illuminate\Http\Request::create('/', 'POST', [
            $slug.'_url' => 'https://vendor.example/api',
            $slug.'_source_id_path' => 'id',
            $slug.'_field_paths' => [],
            $slug.'_field_paths_pending' => ['key' => 'hostname', 'value' => 'device.name'],
            $slug.'_direction_pending' => ['value' => 'push'],
        ]));

        $stored = json_decode(SyncAdapterConfig::get($instance->id, 'field_paths'), true);
        $this->assertSame(['hostname' => 'device.name'], $stored);
        $this->assertSame('push', SyncAdapterConfig::get($instance->id, 'direction.hostname'));
    }

    public function test_saveconfig_persists_picker_row_for_custom_field_destination()
    {
        // Same save-without-click path, but the picker row targets a
        // custom-field destination. The unified widget lets admins
        // choose custom:X directly, and CustomHttp's saveConfig
        // override writes the self-routing mapping so the framework
        // routes the extracted value at sync time.
        $adapter = $this->configuredAdapter([]);
        $slug = $adapter->name();
        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();

        $adapter->saveConfig(\Illuminate\Http\Request::create('/', 'POST', [
            $slug.'_url' => 'https://vendor.example/api',
            $slug.'_source_id_path' => 'id',
            $slug.'_field_paths' => [],
            $slug.'_field_paths_pending' => ['key' => 'custom:9', 'value' => 'location.room'],
            $slug.'_direction_pending' => ['value' => 'pull'],
        ]));

        $stored = json_decode(SyncAdapterConfig::get($instance->id, 'field_paths'), true);
        $this->assertSame(['custom:9' => 'location.room'], $stored);
        $this->assertSame('custom:9', SyncAdapterConfig::get($instance->id, 'mapping.custom:9'));
        $this->assertSame('pull', SyncAdapterConfig::get($instance->id, 'direction.custom:9'));
    }

    public function test_saveconfig_skips_incomplete_picker_row()
    {
        // Half-filled picker rows (destination picked but path empty,
        // or vice versa) submit without both values. Base save drops
        // them so the admin doesn't get a mystery blank entry after
        // hitting Save on an unfinished form.
        $adapter = $this->configuredAdapter([]);
        $slug = $adapter->name();
        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();

        $adapter->saveConfig(\Illuminate\Http\Request::create('/', 'POST', [
            $slug.'_url' => 'https://vendor.example/api',
            $slug.'_source_id_path' => 'id',
            $slug.'_field_paths' => [],
            $slug.'_field_paths_pending' => ['key' => 'hostname', 'value' => ''],
            $slug.'_direction_pending' => ['value' => 'push'],
        ]));

        $stored = json_decode(SyncAdapterConfig::get($instance->id, 'field_paths'), true);
        $this->assertSame([], $stored);
        $this->assertNull(SyncAdapterConfig::get($instance->id, 'direction.hostname'));
    }

    public function test_saveconfig_preserves_existing_custom_field_mappings_on_round_trip()
    {
        // Regression for #19983: saving a CustomHttp adapter with its
        // existing custom:X mappings re-posted (no changes made) must
        // keep them. The previous deletion-detection logic keyed on
        // the `<slug>_mapping` POST, which CustomHttp does not emit
        // (rendersExtrasPicker = false), so every pre-existing extra
        // looked "deleted" on every save and the mapping / direction /
        // field_paths rows were wiped.
        $adapter = $this->configuredAdapter([
            'field_paths' => json_encode([
                'custom:5' => 'tags.asset_tag',
                'custom:6' => 'location.room',
            ]),
            'mapping.custom:5' => 'custom:5',
            'mapping.custom:6' => 'custom:6',
            'direction.custom:5' => 'pull',
            'direction.custom:6' => 'pull',
        ]);
        $slug = $adapter->name();
        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();

        $adapter->saveConfig(\Illuminate\Http\Request::create('/', 'POST', [
            $slug.'_url' => 'https://vendor.example/api',
            $slug.'_source_id_path' => 'id',
            $slug.'_field_paths' => [
                'custom:5' => 'tags.asset_tag',
                'custom:6' => 'location.room',
            ],
            $slug.'_direction' => [
                'custom:5' => 'pull',
                'custom:6' => 'pull',
            ],
        ]));

        $stored = json_decode(SyncAdapterConfig::get($instance->id, 'field_paths'), true);
        $this->assertSame(
            ['custom:5' => 'tags.asset_tag', 'custom:6' => 'location.room'],
            $stored,
            'Custom field paths should round-trip untouched when the admin re-saves without changes.',
        );
        $this->assertSame('custom:5', SyncAdapterConfig::get($instance->id, 'mapping.custom:5'));
        $this->assertSame('custom:6', SyncAdapterConfig::get($instance->id, 'mapping.custom:6'));
        $this->assertSame('pull', SyncAdapterConfig::get($instance->id, 'direction.custom:5'));
        $this->assertSame('pull', SyncAdapterConfig::get($instance->id, 'direction.custom:6'));
    }

    public function test_saveconfig_removes_mapping_direction_when_field_paths_row_is_deleted()
    {
        // Deletion signal: a destination present in the stored
        // field_paths map but absent from the POST means the admin
        // removed the row via the field_paths widget's × button.
        // Clean up the orphan mapping + direction entries so a stale
        // self-routing config doesn't survive the deletion.
        $adapter = $this->configuredAdapter([
            'field_paths' => json_encode([
                'custom:5' => 'tags.asset_tag',
                'custom:6' => 'location.room',
            ]),
            'mapping.custom:5' => 'custom:5',
            'mapping.custom:6' => 'custom:6',
            'direction.custom:5' => 'pull',
            'direction.custom:6' => 'pull',
        ]);
        $slug = $adapter->name();
        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();

        // Admin removed custom:6 via the widget. POST only re-sends custom:5.
        $adapter->saveConfig(\Illuminate\Http\Request::create('/', 'POST', [
            $slug.'_url' => 'https://vendor.example/api',
            $slug.'_source_id_path' => 'id',
            $slug.'_field_paths' => [
                'custom:5' => 'tags.asset_tag',
            ],
            $slug.'_direction' => [
                'custom:5' => 'pull',
            ],
        ]));

        $stored = json_decode(SyncAdapterConfig::get($instance->id, 'field_paths'), true);
        $this->assertSame(['custom:5' => 'tags.asset_tag'], $stored);
        $this->assertSame('custom:5', SyncAdapterConfig::get($instance->id, 'mapping.custom:5'));
        $this->assertNull(SyncAdapterConfig::get($instance->id, 'mapping.custom:6'));
        $this->assertNull(SyncAdapterConfig::get($instance->id, 'direction.custom:6'));
    }

    public function test_pull_warns_when_configured_path_returns_null_on_every_record()
    {
        // A dot-path that returns null on every record is almost
        // always a typo or a stale/renamed vendor
        // field.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_hostname' => 'name',
            'field_serial' => 'blargh.not_a_real_field',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                ['id' => 'dev-1', 'name' => 'host-a'],
                ['id' => 'dev-2', 'name' => 'host-b'],
            ]),
        ]);

        $captured = [];
        $channel = Mockery::mock();
        $channel->shouldReceive('warning')->andReturnUsing(function ($msg) use (&$captured) {
            $captured[] = $msg;
        });
        $channel->shouldReceive('info');
        Log::shouldReceive('channel')->with('sync-adapters')->andReturn($channel);

        iterator_to_array($adapter->pull());

        $matches = array_filter($captured, fn ($m) => str_contains($m, 'blargh.not_a_real_field') && str_contains($m, 'serial'));
        $this->assertNotEmpty($matches, 'Expected an always-null summary warning naming the offending path. Captured: '.json_encode($captured));
    }

    public function test_pull_does_not_warn_when_path_resolves_on_at_least_one_record()
    {
        // A path that misses on some records but hits on others is
        // a normal partial-field scenario (optional vendor data), not
        // a config error. No warning should fire.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_hostname' => 'name',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                ['id' => 'dev-1', 'name' => 'host-a'],
                ['id' => 'dev-2'],
            ]),
        ]);

        $captured = [];
        $channel = Mockery::mock();
        $channel->shouldReceive('warning')->andReturnUsing(function ($msg) use (&$captured) {
            $captured[] = $msg;
        });
        $channel->shouldReceive('info');
        Log::shouldReceive('channel')->with('sync-adapters')->andReturn($channel);

        iterator_to_array($adapter->pull());

        $this->assertEmpty($captured, 'No warning expected when the path resolves on at least one record. Captured: '.json_encode($captured));
    }

    public function test_pull_does_not_warn_on_empty_vendor_response()
    {
        // With zero records processed we can't tell whether a path
        // is a typo or the vendor just returned nothing, so the
        // warning shouldn't fire.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_hostname' => 'blargh.not_a_valid_field',
        ]);

        Http::fake(['vendor.example/*' => Http::response([])]);

        $captured = [];
        $channel = Mockery::mock();
        $channel->shouldReceive('warning')->andReturnUsing(function ($msg) use (&$captured) {
            $captured[] = $msg;
        });
        $channel->shouldReceive('info');
        Log::shouldReceive('channel')->with('sync-adapters')->andReturn($channel);

        iterator_to_array($adapter->pull());

        $this->assertEmpty($captured);
    }

    public function test_missing_dot_path_leaves_field_null_without_throwing()
    {
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_hostname' => 'not.present.here',
        ]);

        Http::fake(['vendor.example/*' => Http::response([['id' => 'dev-1']])]);

        $records = iterator_to_array($adapter->pull());
        $this->assertNull($records[0]->hostname);
    }

    public function test_non_array_at_records_path_logs_warning_and_yields_nothing()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'data.not_an_array',
            'field_source_id' => 'id',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['data' => ['not_an_array' => 'oops']])]);

        $records = iterator_to_array($adapter->pull());
        $this->assertSame([], $records);
    }

    public function test_blank_source_id_path_aborts_pull_softly_instead_of_throwing()
    {
        // Regression: blank source_id path made dotPathGet return
        // the whole record array, which (string) cast blew up with
        // "Array to string conversion" and aborted the sync. Now we
        // detect the missing config up front and log a clear
        // "Source ID is required" message instead.
        $adapter = $this->configuredAdapter([
            'field_hostname' => 'name',
        ]);

        Http::fake(['vendor.example/*' => Http::response([['id' => 'x', 'name' => 'host']])]);

        $records = iterator_to_array($adapter->pull());
        $this->assertSame([], $records);
    }

    public function test_non_scalar_at_source_id_path_skips_record_without_throwing()
    {
        // If the vendor puts an object or array at the source_id
        // path in one specific record (bad row, schema drift),
        // stringOrNull returns null and pull() skips just that
        // record with an info log, letting other records through.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_hostname' => 'name',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                ['id' => ['nested' => 'not a scalar'], 'name' => 'broken'],
                ['id' => 'good-1', 'name' => 'valid'],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);
        $this->assertSame('good-1', $records[0]->sourceId);
    }

    public function test_offset_limit_pagination_walks_until_short_page()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'offset_limit',
            'pagination_page_size' => '2',
        ]);

        // Two full pages of 2, then a short page of 1, then would-be
        // page 4 never fires because the short page signals "last".
        Http::fake([
            'vendor.example/*offset=0*' => Http::response(['rows' => [['id' => 'a'], ['id' => 'b']]]),
            'vendor.example/*offset=2*' => Http::response(['rows' => [['id' => 'c'], ['id' => 'd']]]),
            'vendor.example/*offset=4*' => Http::response(['rows' => [['id' => 'e']]]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame(['a', 'b', 'c', 'd', 'e'], array_map(fn ($r) => $r->sourceId, $records));

        // Never fetches offset=6.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'offset=6'));
    }

    public function test_offset_limit_pagination_honors_custom_param_names()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'data',
            'field_source_id' => 'id',
            'pagination_style' => 'offset_limit',
            'pagination_page_size' => '3',
            'pagination_limit_param' => 'per_page',
            'pagination_offset_param' => 'start',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response(['data' => [['id' => 'only']]]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_contains($request->url(), 'per_page=3')
            && str_contains($request->url(), 'start=0'));
    }

    public function test_page_number_pagination_walks_until_short_page()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'page_number',
            'pagination_page_size' => '2',
        ]);

        Http::fake([
            'vendor.example/*page=1*' => Http::response(['rows' => [['id' => 'a'], ['id' => 'b']]]),
            'vendor.example/*page=2*' => Http::response(['rows' => [['id' => 'c'], ['id' => 'd']]]),
            'vendor.example/*page=3*' => Http::response(['rows' => [['id' => 'e']]]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame(['a', 'b', 'c', 'd', 'e'], array_map(fn ($r) => $r->sourceId, $records));

        // Never fetches page 4 because page 3 was short.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'page=4'));
    }

    public function test_page_number_pagination_honors_custom_page_param_and_start()
    {
        // Zero-based API: pages start at 0 and the query param is
        // called "p" instead of "page".
        $adapter = $this->configuredAdapter([
            'records_path' => 'data',
            'field_source_id' => 'id',
            'pagination_style' => 'page_number',
            'pagination_page_size' => '10',
            'pagination_page_param' => 'p',
            'pagination_page_start' => '0',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response(['data' => [['id' => 'only']]]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_contains($request->url(), 'p=0'));
    }

    public function test_next_url_pagination_follows_response_link()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'next_url',
            'pagination_next_path' => 'links.next',
        ]);

        // Distinct path segments per page keep Http::fake pattern
        // matching unambiguous. The first-page URL is
        // https://vendor.example/api by way of configuredAdapter().
        Http::fake([
            'vendor.example/page-two' => Http::response([
                'rows' => [['id' => 'c']],
                'links' => ['next' => null],
            ]),
            'vendor.example/api' => Http::response([
                'rows' => [['id' => 'a'], ['id' => 'b']],
                'links' => ['next' => 'https://vendor.example/page-two'],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame(['a', 'b', 'c'], array_map(fn ($r) => $r->sourceId, $records));
    }

    public function test_next_url_pagination_rejects_cross_host_cursor()
    {
        // A hostile upstream can emit any absolute URL in the next-URL
        // field. The adapter must keep the second-hop request on the
        // configured host so the auth header can't be leaked to an
        // attacker-chosen target.
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'next_url',
            'pagination_next_path' => 'links.next',
            'auth_method' => 'bearer',
            'bearer_token' => 'leak-canary',
        ]);

        Http::fake([
            'vendor.example/api' => Http::response([
                'rows' => [['id' => 'first']],
                'links' => ['next' => 'https://attacker.example/exfil?token=steal'],
            ]),
            'vendor.example/exfil*' => Http::response([
                'rows' => [['id' => 'second']],
                'links' => ['next' => null],
            ]),
            'attacker.example/*' => Http::response(['rows' => [], 'links' => ['next' => null]]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'attacker.example'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'vendor.example/exfil')
            && str_contains($request->url(), 'token=steal')
            && $request->hasHeader('Authorization', 'Bearer leak-canary'));
    }

    /**
     * Reporter-identified robustness note (Zer0Gate post-fix for
     * GHSA-cf4m-f928-928g): if upstream emits a path-relative cursor
     * with no leading slash (e.g. `"next": "page2"`), parse_url returns
     * `path => "page2"` and the earlier concatenation produced
     * `https://hostpage2` which was unparseable. Pagination aborted on
     * the next iteration (fail-safe) but we'd rather be explicit. The
     * adapter now rejects the cursor and logs the abort when the path
     * lacks a leading slash.
     */
    public function test_next_url_pagination_rejects_relative_cursor_without_leading_slash()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'next_url',
            'pagination_next_path' => 'links.next',
        ]);

        Http::fake([
            'vendor.example/api' => Http::response([
                'rows' => [['id' => 'first']],
                'links' => ['next' => 'page2'],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records, 'First page processes, pagination stops at malformed cursor.');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'vendor.examplepage2')
            || str_contains($request->url(), 'vendor.example/page2'));
    }

    public function test_pull_path_query_string_survives_pagination_style_none()
    {
        // Regression test for #19755. The admin configures a filtered
        // pull_path (ancestor_group_id=...) and expects every pull to
        // honor it. With pagination_style=none the adapter passed an
        // empty $queryParams to ->get(), which Laravel/Guzzle turned
        // into a `query` option that overwrote the URL's existing
        // query, silently dropping the filter.
        $adapter = $this->configuredAdapter([
            'records_path' => 'data',
            'field_source_id' => 'id',
            'pagination_style' => 'none',
            'pull_path' => '/v2/devices?ancestor_group_id=42&limit=100',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['data' => [['id' => 'x']]])]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_contains($request->url(), 'ancestor_group_id=42')
            && str_contains($request->url(), 'limit=100'));
    }

    public function test_pull_path_query_string_survives_offset_limit_pagination()
    {
        // Same bug class as the None case but with pagination params
        // present. The admin's query + the adapter's offset/limit must
        // coexist on the outgoing URL.
        $adapter = $this->configuredAdapter([
            'records_path' => 'data',
            'field_source_id' => 'id',
            'pagination_style' => 'offset_limit',
            'pagination_page_size' => '50',
            'pull_path' => '/v2/devices?ancestor_group_id=42',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['data' => [['id' => 'only']]])]);

        iterator_to_array($adapter->pull());

        Http::assertSent(fn ($request) => str_contains($request->url(), 'ancestor_group_id=42')
            && str_contains($request->url(), 'limit=50')
            && str_contains($request->url(), 'offset=0'));
    }

    public function test_next_url_cursor_query_string_survives_second_hop()
    {
        // Graph-style cursors carry their pagination state in query
        // (`$skiptoken=XYZ`). The second-hop request must preserve
        // that query or pagination terminates on page one with partial
        // results. Companion to the host-pinning check above.
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'next_url',
            'pagination_next_path' => 'links.next',
        ]);

        Http::fake([
            'vendor.example/exfil*' => Http::response(['rows' => [['id' => 'b']], 'links' => ['next' => null]]),
            'vendor.example/api' => Http::response([
                'rows' => [['id' => 'a']],
                'links' => ['next' => 'https://vendor.example/exfil?skiptoken=next-page-cursor'],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame(['a', 'b'], array_map(fn ($r) => $r->sourceId, $records));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'skiptoken=next-page-cursor'));
    }

    public function test_pagination_style_none_yields_one_page_and_stops()
    {
        $adapter = $this->configuredAdapter([
            'records_path' => 'rows',
            'field_source_id' => 'id',
            'pagination_style' => 'none',
        ]);

        $callCount = 0;
        Http::fake([
            'vendor.example/*' => function () use (&$callCount) {
                $callCount++;

                return Http::response(['rows' => [['id' => 'only-'.$callCount]]]);
            },
        ]);

        iterator_to_array($adapter->pull());

        $this->assertSame(1, $callCount, 'None-style must issue exactly one HTTP call.');
    }

    public function test_http_failure_propagates_so_controller_shows_error()
    {
        // Vendor 5xx / auth failure must propagate rather than get
        // swallowed. Prior behavior returned an empty iterable, which
        // the controller flashed as a warm-fuzzy "Synced 0 host(s), 0
        // error(s)" success. Now the exception bubbles to the
        // controller's outer catch, which flashes a red error with
        // the sanitized vendor summary.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['error' => 'boom'], 500)]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        iterator_to_array($adapter->pull());
    }

    public function test_html_escaped_string_values_are_decoded_on_ingest()
    {
        // Some APIs (Snipe-IT's own /api/v1/hardware is one) run
        // string values through htmlspecialchars(), so a model name
        // like `MacBook Pro 13"` shows up on the wire as
        // `MacBook Pro 13&quot;`. Decoding here keeps us from
        // round-tripping the entity into the asset table.
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_paths' => json_encode([
                'hostname' => 'name',
                'model' => 'model',
                'custom:5' => 'notes',
            ]),
            'mapping.custom:5' => 'custom:5',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                [
                    'id' => 'dev-1',
                    'name' => 'wksn &amp; friends',
                    'model' => 'MacBook Pro 13&quot; &#039;16',
                    'notes' => 'Ampersand: A &amp; B',
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('wksn & friends', $records[0]->hostname);
        $this->assertSame("MacBook Pro 13\" '16", $records[0]->hardwareModel);
        $this->assertSame('Ampersand: A & B', $records[0]->extra['custom:5']);
    }

    public function test_last_seen_string_parses_to_carbon()
    {
        $adapter = $this->configuredAdapter([
            'field_source_id' => 'id',
            'field_last_seen' => 'last_check_in',
        ]);

        Http::fake([
            'vendor.example/*' => Http::response([
                ['id' => 'dev-1', 'last_check_in' => '2026-01-15T12:34:56Z'],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertNotNull($records[0]->lastSeen);
        $this->assertSame('2026-01-15 12:34:56', $records[0]->lastSeen->utc()->format('Y-m-d H:i:s'));
    }

    public function test_implements_pushable_when_push_method_is_not_disabled()
    {
        $adapter = $this->configuredAdapter([
            'push_method' => 'PATCH',
        ]);

        $this->assertInstanceOf(PushableSyncAdapter::class, $adapter);
        $this->assertTrue($adapter->canPush());
    }

    public function test_cannot_push_when_push_method_is_disabled()
    {
        $adapter = $this->configuredAdapter(['push_method' => 'disabled']);

        $this->assertFalse($adapter->canPush());
    }

    public function test_cannot_push_when_push_method_is_unset()
    {
        // Fresh install: nothing under push_method. canPush() must
        // return false so the shell hides the Push Now button until
        // the admin picks a verb.
        $adapter = $this->configuredAdapter([]);

        $this->assertFalse($adapter->canPush());
    }

    public function test_push_with_blank_push_path_targets_base_url()
    {
        // Some APIs accept record-scoped writes at the base URL when
        // the id is in the body rather than the path. Blank
        // push_path means "hit the base URL directly."
        $adapter = $this->configuredAdapter([
            'push_method' => 'PATCH',
            'field_asset_tag' => 'asset_tag',
        ]);
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail()->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'BASE-URL-1']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ext-base',
        ]);

        Http::fake(['vendor.example/api' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && rtrim($request->url(), '/') === 'https://vendor.example/api';
        });
    }

    public function test_push_builds_payload_from_directed_fields_with_configured_dot_paths()
    {
        $adapter = $this->configuredAdapter([
            'auth_method' => 'bearer',
            'bearer_token' => 'stub-bearer',
            'push_path' => '/api/v1/devices/{external_id}',
            'field_asset_tag' => 'asset.tag',
            'field_hostname' => 'name',
        ]);
        $instance = SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');
        SyncAdapterConfig::put($instance->id, 'direction.hostname', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'SNIPE-42', 'name' => 'wksn-42']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ext-42',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/api/v1/devices/ext-42')
                && ($body['asset']['tag'] ?? null) === 'SNIPE-42'
                && ($body['name'] ?? null) === 'wksn-42';
        });
    }

    public function test_push_method_override_is_honored()
    {
        $adapter = $this->configuredAdapter([
            'push_path' => '/devices/{external_id}',
            'push_method' => 'PUT',
            'field_asset_tag' => 'asset_tag',
        ]);
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail()->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'PUT-ME']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ext-99',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertSent(fn ($request) => $request->method() === 'PUT');
    }

    public function test_push_substitutes_url_encoded_external_id_in_path_template()
    {
        // Regression guard: external ids often contain characters
        // that need to be percent-encoded (spaces, forward slashes,
        // etc.). The substitution has to rawurlencode the id or the
        // resulting URL is malformed.
        $adapter = $this->configuredAdapter([
            'push_path' => '/devices/{external_id}/fields',
            'field_asset_tag' => 'asset_tag',
        ]);
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail()->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'ENC-1']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'weird id/with slash',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/devices/weird%20id%2Fwith%20slash/fields'));
    }

    public function test_push_skips_when_no_external_source_row()
    {
        // Asset was never pulled from this instance, so there is no
        // vendor-side id to write against. Framework's pushPrologue
        // returns null and we no-op silently rather than 404 the
        // vendor with a made-up id.
        $adapter = $this->configuredAdapter([
            'push_path' => '/devices/{external_id}',
            'field_asset_tag' => 'asset_tag',
        ]);
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail()->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create();

        Http::fake(['vendor.example/*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    public function test_push_dry_run_logs_instead_of_sending()
    {
        $adapter = $this->configuredAdapter([
            'push_path' => '/devices/{external_id}',
            'field_asset_tag' => 'asset_tag',
        ]);
        $instance = SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');
        SyncAdapterConfig::put($instance->id, 'push_dry_run', '1');

        $asset = Asset::factory()->create(['asset_tag' => 'DRY-RUN']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ext-dry',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    public function test_push_notes_target_receives_composed_notes()
    {
        $adapter = $this->configuredAdapter([
            'push_path' => '/devices/{external_id}',
            'push_notes_target' => 'metadata.notes',
            'push_notes_template' => 'Snipe tag: {asset_tag}',
        ]);
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', $adapter->name())->firstOrFail()->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'NOTES-1']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ext-notes',
        ]);

        Http::fake(['vendor.example/*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ($body['metadata']['notes'] ?? null) === 'Snipe tag: NOTES-1';
        });
    }

    /**
     * @param  array<string, string>  $config
     */
    private function configuredAdapter(array $config): CustomHttpAdapter
    {
        $instance = SyncAdapterInstance::create([
            'adapter_type' => 'custom_http',
            'label' => 'Test Custom '.uniqid('', true),
            'active' => true,
        ]);

        SyncAdapterConfig::put($instance->id, 'url', 'https://vendor.example/api');

        // Match credential()'s decryption path: secret-flagged keys
        // must round-trip through Crypt so credential() can read them.
        // Anything not in this set is stored plaintext.
        $secretKeys = ['bearer_token', 'basic_password', 'api_key_value'];

        if (array_key_exists('field_source_id', $config)) {
            SyncAdapterConfig::put($instance->id, 'source_id_path', (string) $config['field_source_id']);
            unset($config['field_source_id']);
        }
        $fieldPathAliases = [
            'field_hostname' => 'hostname',
            'field_serial' => 'serial',
            'field_asset_tag' => 'asset_tag',
            'field_model' => 'model',
            'field_manufacturer' => 'manufacturer',
            'field_mac' => 'mac',
            'field_ip' => 'ip',
            'field_os' => 'os',
            'field_os_version' => 'os_version',
            'field_last_seen' => 'last_seen',
            'field_assigned_user_email' => 'assigned_user_email',
            'field_assigned_user_name' => 'assigned_user_name',
        ];
        $fieldPaths = [];
        foreach ($fieldPathAliases as $legacyKey => $canonicalKey) {
            if (array_key_exists($legacyKey, $config)) {
                $fieldPaths[$canonicalKey] = (string) $config[$legacyKey];
                unset($config[$legacyKey]);
            }
        }
        // Shim: canPush() now gates on push_method rather than
        // push_path.
        if (array_key_exists('push_path', $config) && ! array_key_exists('push_method', $config)) {
            $config['push_method'] = 'PATCH';
        }

        if ($fieldPaths !== []) {
            SyncAdapterConfig::put($instance->id, 'field_paths', json_encode($fieldPaths));
        }

        foreach ($config as $key => $value) {
            $stored = in_array($key, $secretKeys, true) ? Crypt::encrypt((string) $value) : (string) $value;
            SyncAdapterConfig::put($instance->id, $key, $stored);
        }

        return new CustomHttpAdapter($instance->fresh());
    }
}
