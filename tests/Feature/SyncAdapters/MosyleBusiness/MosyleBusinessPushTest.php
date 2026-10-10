<?php

namespace Tests\Feature\SyncAdapters\MosyleBusiness;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\MosyleBusiness\MosyleBusinessAdapter;
use App\SyncAdapters\PushableAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Push-path coverage for the Mosyle Business adapter. Business uses
 * a dispatcher pattern on POST /devices with operation=update_device,
 * carrying serialnumber + updatable fields at the top level of the
 * body. Business DOES support a notes push field, unlike Manager v2.
 */
class MosyleBusinessPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_adapter_implements_pushable_interface(): void
    {
        $this->assertInstanceOf(PushableAdapter::class, $this->configured());
    }

    public function test_push_asset_tag_sends_update_device_operation_at_top_level(): void
    {
        $adapter = $this->configured();
        $instance = SyncAdapterInstance::where('slug', 'mosyle_business')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create([
            'asset_tag' => 'SNIPE-BIZ-77',
            'serial' => 'BIZ-SN-777',
        ]);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'biz-udid-abc',
        ]);

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response(['status' => 'OK']),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/devices')) {
                return true;
            }
            if ($request->method() !== 'POST') {
                return false;
            }
            $body = $request->data();

            return ($body['operation'] ?? null) === 'update_device'
                && ($body['serialnumber'] ?? null) === 'BIZ-SN-777'
                && ($body['asset_tag'] ?? null) === 'SNIPE-BIZ-77'
                && ($request->header('accessToken')[0] ?? null) === 'fake-access-token'
                && ($request->header('Authorization')[0] ?? null) === 'Bearer fake-jwt'
                && ! array_key_exists('accessToken', $body)
                && ! array_key_exists('elements', $body);
        });
    }

    public function test_notes_field_target_is_notes_because_business_supports_it(): void
    {
        // Business exposes a `notes` string on operation=update_device.
        // Manager v2 does not, which is the Business adapter's main
        // feature advantage over the Manager adapter.
        $this->assertSame('notes', $this->configured()->notesFieldTarget());
    }

    public function test_push_falls_back_to_external_id_when_serial_missing(): void
    {
        $adapter = $this->configured();
        $instance = SyncAdapterInstance::where('slug', 'mosyle_business')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create([
            'asset_tag' => 'SNIPE-BIZ-88',
            'serial' => null,
        ]);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'BIZ-SN-888',
        ]);

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response(['status' => 'OK']),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/devices')) {
                return true;
            }

            return ($request->data()['serialnumber'] ?? null) === 'BIZ-SN-888';
        });
    }

    private function configured(): MosyleBusinessAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle_business')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/mosyle-business');
        SyncAdapterConfig::put($instance->id, 'access_token', Crypt::encrypt('fake-access-token'));
        SyncAdapterConfig::put($instance->id, 'email', 'sync-service@example.com');
        SyncAdapterConfig::put($instance->id, 'password', Crypt::encrypt('fake-password'));

        return new MosyleBusinessAdapter($instance->fresh());
    }
}
