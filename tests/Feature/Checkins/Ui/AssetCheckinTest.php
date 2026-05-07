<?php

namespace Tests\Feature\Checkins\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssetCheckinTest extends TestCase
{
    public function test_checking_in_asset_requires_correct_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hardware.checkin.store', [Asset::factory()->assignedToUser()->create()]))
            ->assertForbidden();
    }

    public function test_cannot_check_in_asset_that_is_not_checked_out()
    {
        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [Asset::factory()->create()]))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.index'));
    }

    public function test_cannot_store_asset_checkin_that_is_not_checked_out()
    {
        $this->actingAs(User::factory()->checkinAssets()->create())
            ->get(route('hardware.checkin.store', [Asset::factory()->create()]))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.index'));
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.checkin.create', Asset::factory()->assignedToUser()->create()))
            ->assertOk();
    }

    public function test_requestable_toggle_is_hidden_on_checkin_page_without_old_status_selection()
    {
        $deployableStatus = Statuslabel::factory()->readyToDeploy()->create();
        $asset = Asset::factory()->assignedToUser()->create([
            'status_id' => $deployableStatus->id,
        ]);

        $response = $this->actingAs(User::factory()->checkinAssets()->create())
            ->get(route('hardware.checkin.create', $asset))
            ->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString('id="set-requestable-wrapper"', $content);
        $this->assertMatchesRegularExpression(
            '/id="set-requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s',
            $content
        );
    }

    public function test_requestable_toggle_is_hidden_on_checkin_page_for_non_deployable_status()
    {
        $nonDeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);
        $asset = Asset::factory()->assignedToUser()->create([
            'status_id' => $nonDeployableStatus->id,
        ]);

        $response = $this->actingAs(User::factory()->checkinAssets()->create())
            ->get(route('hardware.checkin.create', $asset))
            ->assertOk();

        $content = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/id="set-requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s',
            $content
        );
    }

    public function test_requestable_toggle_visibility_prefers_old_input_status_id_when_present()
    {
        $deployableStatus = Statuslabel::factory()->readyToDeploy()->create();
        $nonDeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);

        $asset = Asset::factory()->assignedToUser()->create([
            'status_id' => $nonDeployableStatus->id,
        ]);

        $responseWithDeployableOldInput = $this->actingAs(User::factory()->checkinAssets()->create())
            ->withSession(['_old_input' => ['status_id' => (string) $deployableStatus->id]])
            ->get(route('hardware.checkin.create', $asset))
            ->assertOk();

        $this->assertDoesNotMatchRegularExpression(
            '/id="set-requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s',
            $responseWithDeployableOldInput->getContent()
        );

        $assetWithDeployableStatus = Asset::factory()->assignedToUser()->create([
            'status_id' => $deployableStatus->id,
        ]);

        $responseWithNonDeployableOldInput = $this->actingAs(User::factory()->checkinAssets()->create())
            ->withSession(['_old_input' => ['status_id' => (string) $nonDeployableStatus->id]])
            ->get(route('hardware.checkin.create', $assetWithDeployableStatus))
            ->assertOk();

        $this->assertMatchesRegularExpression(
            '/id="set-requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s',
            $responseWithNonDeployableOldInput->getContent()
        );
    }

    public function test_asset_can_be_checked_in()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $location = Location::factory()->create();
        $status = Statuslabel::first() ?? Statuslabel::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'expected_checkin' => now()->addDay(),
            'last_checkin' => null,
            'accepted' => 'accepted',
        ]);

        $this->assertTrue($asset->assignedTo->is($user));

        $currentTimestamp = now();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(
                route('hardware.checkin.store', [$asset]),
                [
                    'name' => 'Changed Name',
                    'status_id' => $status->id,
                    'location_id' => $location->id,
                ],
            );

        $this->assertNull($asset->refresh()->assignedTo);
        $this->assertNull($asset->expected_checkin);
        $this->assertNotNull($asset->last_checkin);
        $this->assertNull($asset->assignedTo);
        $this->assertNull($asset->assigned_type);
        $this->assertNull($asset->accepted);
        $this->assertEquals('Changed Name', $asset->name);
        $this->assertEquals($status->id, $asset->status_id);
        $this->assertTrue($asset->location()->is($location));

        Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($currentTimestamp) {
            // this could be better mocked but is ok for now.
            return (int) Carbon::parse($event->action_date)->diffInSeconds($currentTimestamp, true) < 2;
        }, 1);
    }

    public function test_location_is_set_to_rtd_location_by_default_upon_checkin()
    {
        $rtdLocation = Location::factory()->create();
        $asset = Asset::factory()->assignedToUser()->create([
            'location_id' => Location::factory()->create()->id,
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]));

        $this->assertTrue($asset->refresh()->location()->is($rtdLocation));
        $this->assertHasTheseActionLogs($asset, ['create', 'checkin from']);
    }

    public function test_default_location_can_be_updated_upon_checkin()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]), [
                'location_id' => $location->id,
                'update_default_location' => 0,
            ]);

        $this->assertTrue($asset->refresh()->defaultLoc()->is($location));
    }

    public function test_assets_license_seats_are_cleared_upon_checkin()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        LicenseSeat::factory()->assignedToUser()->for($asset)->create();

        $this->assertNotNull($asset->licenseseats->first()->assigned_to);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]));

        $this->assertNull($asset->refresh()->licenseseats->first()->assigned_to);
    }

    public function test_checking_in_asset_updates_location_of_assets_assigned_to_it()
    {
        $originalLocation = Location::factory()->create();
        $checkedOutLocation = Location::factory()->create();

        $parentAsset = Asset::factory()->assignedToLocation($checkedOutLocation)->create([
            'location_id' => $checkedOutLocation->id,
            'rtd_location_id' => $originalLocation->id,
        ]);

        $childAsset = Asset::factory()->create([
            'assigned_to' => $parentAsset->id,
            'assigned_type' => Asset::class,
            'location_id' => $checkedOutLocation->id,
            'rtd_location_id' => $originalLocation->id,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$parentAsset]), [
                'location_id' => $originalLocation->id,
            ]);

        $this->assertEquals($originalLocation->id, $parentAsset->fresh()->location_id);
        $this->assertEquals($originalLocation->id, $childAsset->fresh()->location_id);
        $this->assertEquals($parentAsset->id, $childAsset->fresh()->assigned_to);
        $this->assertEquals(Asset::class, $childAsset->fresh()->assigned_type);
    }

    public function test_legacy_location_values_set_to_zero_are_updated()
    {
        $asset = Asset::factory()->canBeInvalidUponCreation()->assignedToUser()->create([
            'rtd_location_id' => 0,
            'location_id' => 0,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]));

        $this->assertNull($asset->refresh()->rtd_location_id);
        $this->assertEquals($asset->location_id, $asset->rtd_location_id);
    }

    public function test_pending_checkout_acceptances_are_cleared_upon_checkin()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $acceptance = CheckoutAcceptance::factory()->for($asset, 'checkoutable')->pending()->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]));

        $this->assertFalse($acceptance->exists(), 'Acceptance was not deleted');
    }

    public function test_checkin_time_and_action_log_note_can_be_set()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route(
                'hardware.checkin.store', [Asset::factory()->assignedToUser()->create()]
            ), [
                'checkin_at' => '2023-01-02',
                'note' => 'hello',
            ]);

        Event::assertDispatched(function (CheckoutableCheckedIn $event) {
            return $event->action_date === '2023-01-02' && $event->note === 'hello';
        }, 1);
    }

    public function test_checkin_can_set_asset_to_requestable_when_status_is_deployable()
    {
        $deployableStatus = Statuslabel::factory()->readyToDeploy()->create();
        $asset = Asset::factory()->assignedToUser()->create([
            'requestable' => 0,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]), [
                'status_id' => $deployableStatus->id,
                'set_requestable' => 1,
            ]);

        $this->assertTrue((bool) $asset->fresh()->requestable);

        $log = Actionlog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'checkin from')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->log_meta);

        $logMeta = json_decode($log->log_meta, true);
        $this->assertArrayHasKey('requestable', $logMeta);
        $this->assertEquals(0, (int) $logMeta['requestable']['old']);
        $this->assertEquals(1, (int) $logMeta['requestable']['new']);
    }

    public function test_checkin_does_not_set_asset_to_requestable_when_status_is_not_deployable()
    {
        $undeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);
        $asset = Asset::factory()->assignedToUser()->create([
            'requestable' => 0,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.checkin.store', [$asset]), [
                'status_id' => $undeployableStatus->id,
                'set_requestable' => 1,
            ]);

        $this->assertFalse((bool) $asset->fresh()->requestable);
    }

    public function test_asset_checkin_page_is_redirected_if_model_is_invalid()
    {

        $asset = Asset::factory()->assignedToUser()->create();
        $asset->model_id = 0;
        $asset->forceSave();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('hardware.checkin.create', [$asset]))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.show', $asset));
    }

    public function test_asset_checkin_page_post_is_redirected_if_model_is_invalid()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        $asset->model_id = 0;
        $asset->forceSave();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('hardware.checkin.store', $asset))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.show', $asset));
    }

    public function test_asset_checkin_page_post_is_redirected_if_redirect_selection_is_index()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.index'))
            ->post(route('hardware.checkin.store', $asset), [
                'redirect_option' => 'index',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.index'));
    }

    public function test_asset_checkin_page_post_is_redirected_if_redirect_selection_is_item()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.index'))
            ->post(route('hardware.checkin.store', $asset), [
                'redirect_option' => 'item',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('hardware.show', $asset));
    }
}
