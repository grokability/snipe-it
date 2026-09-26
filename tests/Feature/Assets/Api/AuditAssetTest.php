<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auditing')]
class AuditAssetTest extends TestCase
{
    public function test_permission_required_to_bulk_audit_assets()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.audit', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_that_a_non_existent_asset_id_returns_error()
    {
        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit', 123_456_789))
            ->assertStatusMessageIs('error');
    }

    public function test_requires_permission_to_audit_asset()
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.audit', $asset))
            ->assertForbidden();
    }

    public function test_legacy_asset_audit_is_saved()
    {
        $asset = Asset::factory()->create();
        $future = now()->addMonths(5)->toDateString();

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit.legacy'), [
                'asset_tag' => $asset->asset_tag,
                'next_audit_date' => $future,
                'note' => 'test',
            ])
            ->assertStatusMessageIs('success')
            ->assertJson(
                [
                    'messages' => trans('admin/hardware/message.audit.success'),
                    'payload' => [
                        'id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'note' => 'test',
                    ],
                ])
            ->assertStatus(200);

        $asset->refresh();
        $this->assertEquals($future, $asset->next_audit_date);
    }

    public function test_legacy_asset_audit_defaults_to_asset_tag_when_audit_by_field_is_not_sent()
    {
        $asset = Asset::factory()->create();
        $future = now()->addMonths(2)->toDateString();

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit.legacy'), [
                // Simulates the new quickscan form where audit_key is posted
                // and the dropdown may remain on default.
                'audit_key' => $asset->asset_tag,
                'next_audit_date' => $future,
                'note' => 'default asset tag',
            ])
            ->assertStatusMessageIs('success')
            ->assertJsonPath('payload.id', $asset->id)
            ->assertJsonPath('payload.audit_by_field', 'Asset Tag')
            ->assertJsonPath('payload.audit_key', (string) $asset->asset_tag)
            ->assertStatus(200);

        $asset->refresh();
        $this->assertEquals($future, $asset->next_audit_date);
    }

    public function test_legacy_asset_audit_can_find_asset_by_serial_when_selected()
    {
        $this->settings->enableUniqueSerialNumbers();

        $asset = Asset::factory()->create(['serial' => 'SERIAL-ABC-123']);
        $future = now()->addMonths(2)->toDateString();

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit.legacy'), [
                'audit_by_field' => 'serial',
                'audit_key' => $asset->serial,
                'next_audit_date' => $future,
                'note' => 'serial lookup',
            ])
            ->assertStatusMessageIs('success')
            ->assertJsonPath('payload.id', $asset->id)
            ->assertJsonPath('payload.audit_by_field', 'Serial')
            ->assertJsonPath('payload.audit_key', $asset->serial)
            ->assertStatus(200);

        $asset->refresh();
        $this->assertEquals($future, $asset->next_audit_date);
    }

    public function test_asset_audit_is_saved()
    {
        $asset = Asset::factory()->create(['next_audit_date' => now()->subMonth()->toDateString()]);
        $now = now();
        $future = now()->addMonths(3)->toDateString();

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit', $asset), [
                'next_audit_date' => $future,
                'note' => 'test',
            ])
            ->assertStatusMessageIs('success')
            ->assertJson(
                [
                    'messages' => trans('admin/hardware/message.audit.success'),
                    'payload' => [
                        'id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'note' => 'test',
                    ],
                ])
            ->assertStatus(200);

        $this->assertHasTheseActionLogs($asset, ['create', 'audit']);

        $asset->refresh();
        $this->assertTrue(
            Carbon::parse($asset->last_audit_date)->betweenIncluded($now->copy()->subSecond(), now()->addSecond()),
            'Expected last_audit_date to be close to the audit request time.'
        );
        $this->assertEquals($future, $asset->next_audit_date);
    }

    /**
     * @link https://github.com/grokability/snipe-it/issues/18495
     */
    public function test_audit_does_not_set_next_audit_date_if_given_null()
    {
        $this->settings->setAuditInterval(null);

        $asset = Asset::factory()->create(['next_audit_date' => null]);

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit', $asset), [
                'asset_tag' => $asset->asset_tag,
                // this is the important part
                'next_audit_date' => null,
                'note' => null,
            ])
            ->assertStatusMessageIs('success')
            ->assertStatus(200);

        $asset->refresh();
        $this->assertNull($asset->next_audit_date);
    }

    public function test_asset_name_is_cleared_on_audit_when_clear_name_is_set()
    {
        $asset = Asset::factory()->create(['name' => 'My Asset Name']);

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit.legacy'), [
                'asset_tag' => $asset->asset_tag,
                'clear_name' => '1',
            ])
            ->assertStatusMessageIs('success')
            ->assertStatus(200);

        $this->assertNull($asset->refresh()->name);
    }

    public function test_asset_name_is_not_cleared_on_audit_when_clear_name_is_not_set()
    {
        $asset = Asset::factory()->create(['name' => 'My Asset Name']);

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit.legacy'), [
                'asset_tag' => $asset->asset_tag,
            ])
            ->assertStatusMessageIs('success')
            ->assertStatus(200);

        $this->assertEquals('My Asset Name', $asset->refresh()->name);
    }

    public function test_audit_persists_uploaded_image_and_stamps_filename_on_log()
    {
        // Parity with the web audit form: an uploaded `image` on the API
        // audit request should be saved into private_uploads/audits/ and its
        // storage-relative filename recorded on the resulting audit log entry.
        Storage::fake();

        $asset = Asset::factory()->create();

        $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->post(route('api.asset.audit', $asset->id), [
                'note' => 'audit w/ photo',
                'image' => UploadedFile::fake()->image('audit.jpg'),
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        // Payload echoes back the stored filename.
        $filename = $response->json('payload.image');
        $this->assertNotNull($filename);
        $this->assertStringStartsWith('audit-'.$asset->id.'-', $filename);
        Storage::assertExists('private_uploads/audits/'.$filename);

        // Filename shows up on the audit log so the audit history view can render it.
        $log = Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first();
        $this->assertNotNull($log);
        $this->assertSame($filename, $log->filename);
    }

    public function test_audit_without_image_does_not_stamp_filename()
    {
        Storage::fake();

        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit', $asset->id), [
                'note' => 'no photo',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $log = Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first();
        $this->assertNotNull($log);
        $this->assertNull($log->filename);
    }
}
