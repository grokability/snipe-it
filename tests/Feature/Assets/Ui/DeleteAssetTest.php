<?php

namespace Tests\Feature\Assets\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteAssetTest extends TestCase
{
    public function testPermissionNeededToDeleteAsset()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('hardware.destroy', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function testCanDeleteAsset()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->deleteAssets()->create())
            ->delete(route('hardware.destroy', $asset))
            ->assertRedirectToRoute('hardware.index')
            ->assertSessionHas('success');

        $this->assertSoftDeleted($asset);
    }

    public function testActionLogEntryMadeWhenAssetDeleted()
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

    public function testActionLogsActionDateIsPopulatedWhenAssetDeleted()
    {
        $actor = User::factory()->deleteAssets()->create();

        $asset = Asset::factory()->create();

        $this->actingAs($actor)->delete(route('hardware.destroy', $asset));

        $asset->refresh();

        $this->assertDatabaseHas('action_logs', [
            'action_date' => $asset->updated_at,
            'created_at' => $asset->updated_at,
            'created_by' => $actor->id,
            'action_type' => 'delete',
            'target_id' => null,
            'target_type' => null,
            'item_type' => Asset::class,
            'item_id' => $asset->id,
        ]);

    }

    public function testAssetIsCheckedInWhenDeleted()
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

    public function testImageIsDeletedWhenAssetDeleted()
    {
        Storage::fake('public');

        $asset = Asset::factory()->create(['image' => 'image.jpg']);

        Storage::disk('public')->put('assets/image.jpg', 'content');

        Storage::disk('public')->assertExists('assets/image.jpg');

        $this->actingAs(User::factory()->deleteAssets()->create())
            ->delete(route('hardware.destroy', $asset));

        Storage::disk('public')->assertMissing('assets/image.jpg');
    }
}
