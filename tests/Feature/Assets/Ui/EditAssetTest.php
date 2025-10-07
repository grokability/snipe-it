<?php

namespace Tests\Feature\Assets\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EditAssetTest extends TestCase
{

    public function testPermissionRequiredToViewAsset()
    {
        $asset = Asset::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('hardware.edit', $asset))
            ->assertForbidden();
    }

    public function testPageCanBeAccessed(): void
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $response = $this->actingAs($user)->get(route('hardware.edit', $asset));
        $response->assertStatus(200);
    }

    public function testAssetEditPostIsRedirectedIfRedirectSelectionIsIndex()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->viewAssets()->editAssets()->create())
            ->from(route('hardware.edit', $asset))
            ->put(route('hardware.update', $asset),
                [
                    'redirect_option' => 'index',
                    'name' => 'New name',
                    'asset_tags' => 'New Asset Tag',
                    'status_id' => StatusLabel::factory()->create()->id,
                    'model_id' => AssetModel::factory()->create()->id,
                ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.index'));
        $this->assertDatabaseHas('assets', ['asset_tag' => 'New Asset Tag']);
    }
    public function testAssetEditPostIsRedirectedIfRedirectSelectionIsItem()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->viewAssets()->editAssets()->create())
            ->from(route('hardware.edit', $asset))
            ->put(route('hardware.update', $asset), [
                'redirect_option' => 'item',
                'name' => 'New name',
                'asset_tags' => 'New Asset Tag',
                'status_id' => StatusLabel::factory()->create()->id,
                'model_id' => AssetModel::factory()->create()->id,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.show', $asset));

        $this->assertDatabaseHas('assets', ['asset_tag' => 'New Asset Tag']);
    }

    public function testNewCheckinIsLoggedIfStatusChangedToUndeployable()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $deployable_status = Statuslabel::factory()->rtd()->create();
        $achived_status = Statuslabel::factory()->archived()->create();
        $asset = Asset::factory()->assignedToUser($user)->create(['status_id' => $deployable_status->id]);
        $this->assertTrue($asset->assignedTo->is($user));

        $currentTimestamp = now();

        $this->actingAs(User::factory()->viewAssets()->editAssets()->create())
            ->from(route('hardware.edit', $asset))
            ->put(route('hardware.update', $asset), [
                    'status_id' => $achived_status->id,
                    'model_id' => $asset->model_id,
                    'asset_tags' => $asset->asset_tag,
                ],
            )
            ->assertStatus(302);
            //->assertRedirect(route('hardware.show', ['hardware' => $asset->id]));;

        // $asset->refresh();
        $asset = Asset::find($asset->id);
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
        $this->assertEquals($achived_status->id, $asset->status_id);

        Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($currentTimestamp) {
            return (int) Carbon::parse($event->action_date)->diffInSeconds($currentTimestamp, true) < 2;
        }, 1);
    }

    public function testCurrentLocationIsNotUpdatedOnEdit()
    {
        $defaultLocation = Location::factory()->create();
        $currentLocation = Location::factory()->create();
        $asset = Asset::factory()->create([
            'location_id' => $currentLocation->id,
            'rtd_location_id' => $defaultLocation->id
        ]);

        $this->actingAs(User::factory()->viewAssets()->editAssets()->create())
            ->put(route('hardware.update', $asset), [
                'redirect_option' => 'item',
                'name' => 'New name',
                'asset_tags' => 'New Asset Tag',
                'status_id' => $asset->status_id,
                'model_id' => $asset->model_id,
            ]);

        $asset->refresh();
        $this->assertEquals('New name', $asset->name);
        $this->assertEquals($currentLocation->id, $asset->location_id);
    }


    public function test_handles_model_being_deleted()
    {
        $this->withoutExceptionHandling();

        $newStatus = StatusLabel::factory()->create();

        $asset = Asset::factory()->create();

        $asset->model()->forceDelete();

        $this->actingAs(User::factory()->viewAssets()->editAssets()->create())
            ->from(route('hardware.edit', $asset))
            ->put(route('hardware.update', $asset), [
                'redirect_option' => 'index',
                'purchase_date' => '2025-08-30',
                'name' => 'New name',
                'asset_tags' => 'New Asset Tag',
                'status_id' => $newStatus->id,
                // triggers potential issue in AssetObserver's saving method
                'model_id' => AssetModel::factory()->create()->id,
            ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status_id' => $newStatus->id,
        ]);
    }
}
