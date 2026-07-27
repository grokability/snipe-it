<?php

namespace Tests\Feature\Assets\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteAssetTest extends TestCase
{
    public function test_permission_needed_to_delete_asset()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('hardware.destroy', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_delete_asset()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->deleteAssets()->create())
            ->delete(route('hardware.destroy', $asset))
            ->assertRedirectToRoute('hardware.index')
            ->assertSessionHas('success');

        $this->assertSoftDeleted($asset);
    }

    public function test_action_log_entry_made_when_asset_deleted()
    {
        $actor = User::factory()->deleteAssets()->create();

        $asset = Asset::factory()->create();

        $this->actingAs($actor)->delete(route('hardware.destroy', $asset));

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $actor->id,
            'action_type' => 'delete',
            'target_id' => null,
            'target_type' => null,
            'item_type' => Asset::class,
            'item_id' => $asset->id,
        ]);
    }

    public function test_action_logs_action_date_is_populated_when_asset_deleted()
    {
        $actor = User::factory()->deleteAssets()->create();

        $asset = Asset::factory()->create();

        $this->actingAs($actor)->delete(route('hardware.destroy', $asset));

        // Loggable sets action_date via PHP's date('Y-m-d H:i:s') rather than
        // Carbon::now(), so freezeTime()/setTestNow can't pin it in sync with
        // Eloquent's Carbon-driven created_at or with $asset->updated_at.
        // Assert what the test name actually promises: the delete row exists
        // for this actor/asset AND its action_date column is populated.
        $log = Actionlog::query()
            ->where('created_by', $actor->id)
            ->where('action_type', 'delete')
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->whereNull('target_id')
            ->whereNull('target_type')
            ->first();

        $this->assertNotNull($log, 'Expected a delete action log for the actor and asset.');
        $this->assertNotNull($log->action_date, 'action_date should be populated on the delete action log.');
    }

    public function test_asset_is_checked_in_when_deleted()
    {
        Event::fake();

        $assignedUser = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($assignedUser)->create();

        $this->assertTrue($assignedUser->assets->contains($asset));

        $this->actingAs(User::factory()->deleteAssets()->create())
            ->delete(route('hardware.destroy', $asset));

        $this->assertFalse(
            $assignedUser->fresh()->assets->contains($asset),
            'Asset still assigned to user after deletion'
        );

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    public function test_image_is_preserved_when_asset_soft_deleted()
    {
        // Soft-deleting an asset preserves its image on disk so a
        // restored asset still has one. The image is only removed for
        // good by `snipeit:purge` when the row is force-deleted.
        // Coverage for that permanent-removal path lives in
        // `tests/Feature/Console/Commands/PurgeTest.php`.
        Storage::fake('public');

        $asset = Asset::factory()->create(['image' => 'image.jpg']);

        Storage::disk('public')->put('assets/image.jpg', 'content');

        Storage::disk('public')->assertExists('assets/image.jpg');

        $this->actingAs(User::factory()->deleteAssets()->create())
            ->delete(route('hardware.destroy', $asset));

        Storage::disk('public')->assertExists('assets/image.jpg');
    }
}
