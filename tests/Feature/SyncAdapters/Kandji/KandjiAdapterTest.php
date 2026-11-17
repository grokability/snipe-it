<?php

namespace Tests\Feature\SyncAdapters\Kandji;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Kandji\KandjiAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Kandji adapter through SyncAdapter.
 * Mocks Kandji's REST API, asserts assets + asset_external_sources land
 * correctly, and that pagination termination on a short page works.
 */
class KandjiAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_kandji_and_creates_assets()
    {
        $adapter = $this->configuredKandjiAdapter();

        Http::fake([
            '*/api/v1/devices*' => Http::sequence()
                ->push([
                    $this->kandjiDevice(id: 'aaa-111', device_name: 'workstation-01', model: 'MacBook Pro'),
                    $this->kandjiDevice(id: 'bbb-222', device_name: 'workstation-02', model: 'MacBook Air'),
                ])
                // Empty second page terminates the client's pagination loop.
                ->push([]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'kandji', 'external_id' => 'aaa-111']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'kandji', 'external_id' => 'bbb-222']);
        $this->assertDatabaseHas('assets', ['name' => 'workstation-01']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredKandjiAdapter();

        Http::fake([
            '*/api/v1/devices*' => Http::sequence()
                ->push([
                    $this->kandjiDevice(
                        id: 'ccc-333',
                        device_name: 'design-mbp',
                        model: 'MacBook Pro (16-inch, M3)',
                        serial_number: 'ABC123',
                        platform: 'Mac',
                        os_version: '14.2.1',
                        last_check_in: '2026-01-15T10:00:00.000Z',
                    ),
                ])
                ->push([]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('kandji', $record->sourceKey);
        $this->assertSame('ccc-333', $record->sourceId);
        $this->assertSame('design-mbp', $record->hostname);
        $this->assertSame('MacBook Pro (16-inch, M3)', $record->hardwareModel);
        $this->assertSame('ABC123', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer); // inferred from platform=Mac
        $this->assertSame('Mac', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_pull_raises_when_base_url_returns_html_instead_of_json()
    {
        // Regression: when the admin pastes the Iru (Kandji) web
        // console URL as the Base URL instead of the API host, Kandji
        // returns 200 OK with an SPA shell (text/html). ->throw() sees
        // 200 and doesn't fire, ->json() decodes to null, and the
        // sync used to complete silently with "0 hosts synced". The
        // throwIfNotJson macro registered in AppServiceProvider now
        // surfaces this as a real error so the admin gets an
        // actionable flash instead of a zero-hosts warning.
        $adapter = $this->configuredKandjiAdapter();

        Http::fake([
            '*/api/v1/devices*' => Http::response(
                '<!DOCTYPE html><html><body><div id="app"></div></body></html>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        $this->expectException(\App\Exceptions\SyncAdapterVendorException::class);
        $this->expectExceptionMessage('Verify the adapter Base URL points at the vendor API');
        iterator_to_array($adapter->pull());
    }

    public function test_purchase_date_from_vendor_datetime_rotates_to_app_timezone_before_truncation(): void
    {
        // Companion to the last_seen fix: vendor-supplied datetimes mapped
        // to the native `purchase_date` column (a Y-m-d field) also need
        // to rotate to app.timezone before the date part is extracted. A
        // vendor datetime at the UTC day boundary would otherwise record
        // the UTC-wall-clock date, which differs from the user's expected
        // local date by one calendar day.
        config(['app.timezone' => 'America/Los_Angeles']);

        // Reuse the Kandji adapter's extraFields plumbing to put a
        // purchase_date value through the mapped-extra path. Direct the
        // model's `purchased_at` extra at native:purchase_date so the
        // SyncsHostFromRecord case-branch for 'purchase_date' fires.
        $instance = SyncAdapterInstance::where('slug', 'kandji')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.api.kandji.io');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-kandji-token'));

        // Vendor sends 03:00:00 UTC on 2026-07-23. That is 20:00:00 Pacific
        // on 2026-07-22. Pre-fix, Carbon::parse keeps UTC, and format('Y-m-d')
        // emits "2026-07-23". Post-fix, the rotate-then-format produces
        // "2026-07-22" (Pacific calendar date), matching user expectation.
        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'kandji',
            sourceId: 'tz-pd-1',
            hostname: 'pacific-pd',
            hardwareSerial: 'TZ-PD-1',
        );

        $asset = new \App\Models\Asset;
        // Invoke the case 'purchase_date' handler directly via the public
        // facade: writeExtraValueToTarget routes through writeNative,
        // which hits the case statement. Simplest driver is to call the
        // native-field writer with a datetime payload.
        $reflection = new \ReflectionClass(\App\SyncAdapters\SyncsHostFromRecord::class);
        $writeNative = $reflection->getMethod('writeNative');
        $writeNative->invoke(null, $asset, 'purchase_date', '2026-07-23T03:00:00Z', $instance, $record);

        // Asset casts purchase_date, so read the raw stored attribute to
        // see the string the sync pipeline actually wrote.
        $this->assertSame(
            '2026-07-22',
            $asset->getAttributes()['purchase_date'] ?? null,
            'purchase_date must store the user\'s local calendar date for a UTC datetime at the day boundary.',
        );
    }

    public function test_last_seen_is_stored_in_app_timezone_not_vendor_utc(): void
    {
        // Issue #19765: Kandji returns `last_check_in` as ISO-8601 UTC with a
        // Z suffix. Pre-fix, Carbon::parse honored the Z and the shared
        // writer called ->toDateTimeString() on the UTC Carbon, so the UTC
        // wall clock went into asset_external_sources.last_seen. On an
        // instance with APP_TIMEZONE set to a non-UTC value, the view then
        // interpreted that stored string as app-timezone wall clock,
        // which read 7 hours in the future on PDT.
        config(['app.timezone' => 'America/Los_Angeles']);

        $adapter = $this->configuredKandjiAdapter();

        Http::fake([
            '*/api/v1/devices*' => Http::sequence()
                ->push([
                    $this->kandjiDevice(
                        id: 'tz-1',
                        device_name: 'pacific-mbp',
                        serial_number: 'TZ-SN-1',
                        last_check_in: '2026-01-15T22:11:37.000Z',
                    ),
                ])
                ->push([]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        // 22:11:37 UTC on 2026-01-15 is 14:11:37 the same day in Pacific
        // standard time. The stored naive string must match the Pacific
        // wall clock, same as every other naive datetime Snipe-IT writes
        // under the same config.
        $this->assertDatabaseHas('asset_external_sources', [
            'source' => 'kandji',
            'external_id' => 'tz-1',
            'last_seen' => '2026-01-15 14:11:37',
        ]);
    }

    private function configuredKandjiAdapter(): KandjiAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'kandji')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.api.kandji.io');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-kandji-token'));

        return new KandjiAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function kandjiDevice(
        string $id,
        string $device_name = 'device',
        string $model = 'MacBook',
        ?string $serial_number = null,
        ?string $platform = 'Mac',
        ?string $os_version = null,
        ?string $last_check_in = null,
    ): array {
        return [
            'device_id' => $id,
            'device_name' => $device_name,
            'model' => $model,
            'serial_number' => $serial_number,
            'platform' => $platform,
            'os_version' => $os_version,
            'last_check_in' => $last_check_in,
            'asset_tag' => null,
            'assigned_blueprint_id' => null,
            'mdm_enabled' => true,
        ];
    }
}
