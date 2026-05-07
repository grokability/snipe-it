<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class ForceCheckinAssetTest extends TestCase
{
    public function test_permission_required_to_force_checkin_asset()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('asset.checkin.force', $asset))
            ->assertForbidden();
    }

    public function test_can_force_checkin_asset_with_orphaned_assigned_to_and_missing_type()
    {
        $asset = Asset::factory()->create();
        $originalCheckinCounter = (int) $asset->checkin_counter;
        $asset->assigned_to = 999; // Non-existent ID
        $asset->assigned_type = null; // Missing type
        $asset->forceSave();

        $response = $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('asset.checkin.force', $asset));

        $response->assertRedirect(route('hardware.show', $asset))
            ->assertSessionHas('success');

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
        $this->assertSame($originalCheckinCounter, (int) $asset->checkin_counter);

        $this->assertDatabaseHas((new Actionlog)->getTable(), [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'force checkin',
        ]);

        $forceCheckinLog = Actionlog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'force checkin')
            ->latest('id')
            ->first();

        $this->assertNotNull($forceCheckinLog);
        $this->assertNull($forceCheckinLog->log_meta);
    }

    public function test_can_force_checkin_asset_with_hard_deleted_assigned_to()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->save();

        // Hard delete the user
        $user->forceDelete();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('asset.checkin.force', $asset))
            ->assertRedirect(route('hardware.show', $asset))
            ->assertSessionHas('success');

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
    }

    public function test_cannot_force_checkin_asset_that_is_not_orphaned()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->save();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('asset.checkin.force', $asset))
            ->assertRedirect(route('hardware.show', $asset))
            ->assertSessionHas('error');

        $asset->refresh();
        $this->assertNotNull($asset->assigned_to);
    }

    public function test_cannot_force_checkin_asset_with_no_assignment()
    {
        $asset = Asset::factory()->create();
        $asset->assigned_to = null;
        $asset->assigned_type = null;
        $asset->save();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('asset.checkin.force', $asset))
            ->assertRedirect(route('hardware.show', $asset))
            ->assertSessionHas('error');
    }

    public function test_force_checkin_button_shown_on_asset_view_when_orphaned()
    {
        $asset = Asset::factory()->create();
        $asset->assigned_to = 999; // Non-existent ID
        $asset->assigned_type = User::class;
        $asset->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertSeeText(trans('general.force_checkin'));
    }

    public function test_force_checkin_button_not_shown_when_asset_is_not_orphaned()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertDontSeeText(trans('general.force_checkin'));
    }
}
