<?php

namespace Tests\Feature\Checkins\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BulkAssetCheckinTest extends TestCase
{
    public function test_requires_checkin_permission()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
            ])
            ->assertForbidden();
    }

    public function test_show_page_requires_checkin_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hardware.bulkcheckin.show'))
            ->assertForbidden();
    }

    public function test_show_page_renders()
    {
        $this->actingAs(User::factory()->checkinAssets()->create())
            ->get(route('hardware.bulkcheckin.show'))
            ->assertOk();
    }

    public function test_bulk_edit_routing_redirects_to_bulk_checkin_show()
    {
        $assets = Asset::factory()->assignedToUser()->count(2)->create();

        $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
            ->post(route('hardware.bulkedit.show'), [
                'ids' => $assets->pluck('id')->toArray(),
                'bulk_actions' => 'checkin',
                'sort' => 'id',
                'order' => 'asc',
            ])
            ->assertRedirectToRoute('hardware.bulkcheckin.show');
    }

    public function test_can_bulk_checkin_assets()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $assets = Asset::factory()->assignedToUser($user)->count(2)->create([
            'expected_checkin' => now()->addDay(),
            'accepted' => 'accepted',
        ]);

        $response = $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
            ])
            ->assertStatus(302);

        $assets->each(function (Asset $asset) {
            $fresh = $asset->fresh();
            $this->assertNull($fresh->assigned_to);
            $this->assertNull($fresh->assigned_type);
            $this->assertNull($fresh->expected_checkin);
            $this->assertNull($fresh->accepted);
            $this->assertNotNull($fresh->last_checkin);
        });

        Event::assertDispatched(CheckoutableCheckedIn::class, 2);

        $this->followRedirects($response)->assertSee('alert-success');
    }

    public function test_skips_assets_not_checked_out()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $assignedAsset = Asset::factory()->assignedToUser()->create();
        $unassignedAsset = Asset::factory()->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$assignedAsset->id, $unassignedAsset->id],
            ])
            ->assertStatus(302);

        $this->assertNull($assignedAsset->fresh()->assigned_to);
        $this->assertNotNull($unassignedAsset->fresh()->id);

        Event::assertDispatched(CheckoutableCheckedIn::class, 1);
    }

    public function test_status_can_be_changed_upon_bulk_checkin()
    {
        $rtdStatus = Statuslabel::factory()->readyToDeploy()->create();
        $assets = Asset::factory()->assignedToUser()->count(2)->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'status_id' => $rtdStatus->id,
            ]);

        $assets->each(function (Asset $asset) use ($rtdStatus) {
            $this->assertEquals($rtdStatus->id, $asset->fresh()->status_id);
        });
    }

    public function test_location_is_set_to_rtd_location_upon_bulk_checkin()
    {
        $rtdLocation = Location::factory()->create();
        $checkedOutLocation = Location::factory()->create();

        $assets = Asset::factory()->assignedToUser()->count(2)->create([
            'location_id' => $checkedOutLocation->id,
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
            ]);

        $assets->each(function (Asset $asset) use ($rtdLocation) {
            $this->assertEquals($rtdLocation->id, $asset->fresh()->location_id);
        });
    }

    public function test_bulk_checkin_rejects_nonexistent_location_id()
    {
        $rtdLocation = Location::factory()->create();
        $assets = Asset::factory()->assignedToUser()->count(2)->create(['rtd_location_id' => $rtdLocation->id]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'location_id' => PHP_INT_MAX,
            ])
            ->assertRedirectToRoute('hardware.bulkcheckin.show')
            ->assertSessionHas('error');

        $assets->each(function (Asset $asset) {
            $this->assertNotNull($asset->fresh()->assigned_to);
        });
    }

    public function test_bulk_checkin_rejects_location_from_another_company_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $foreignLocation = Location::factory()->create(['company_id' => $companyB->id]);
        $rtdLocation = Location::factory()->create(['company_id' => $companyA->id]);
        $assets = Asset::factory()->assignedToUser()->count(2)->create([
            'company_id' => $companyA->id,
            'rtd_location_id' => $rtdLocation->id,
        ]);
        $actor = User::factory()->checkinAssets()->viewAssets()->create(['company_id' => $companyA->id]);

        $this->actingAs($actor)
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'location_id' => $foreignLocation->id,
            ])
            ->assertRedirectToRoute('hardware.bulkcheckin.show')
            ->assertSessionHas('error');

        $assets->each(function (Asset $asset) {
            $this->assertNotNull($asset->fresh()->assigned_to);
        });
    }

    public function test_submitted_location_overrides_rtd_location_without_updating_default()
    {
        $rtdLocation = Location::factory()->create();
        $submittedLocation = Location::factory()->create();

        $assets = Asset::factory()->assignedToUser()->count(2)->create([
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'location_id' => $submittedLocation->id,
                'update_default_location' => '1',
            ]);

        $assets->each(function (Asset $asset) use ($submittedLocation, $rtdLocation) {
            $fresh = $asset->fresh();
            $this->assertEquals($submittedLocation->id, $fresh->location_id);
            $this->assertEquals($rtdLocation->id, $fresh->rtd_location_id);
        });
    }

    public function test_submitted_location_updates_both_current_and_default_when_radio_set_to_zero()
    {
        $rtdLocation = Location::factory()->create();
        $submittedLocation = Location::factory()->create();

        $assets = Asset::factory()->assignedToUser()->count(2)->create([
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'location_id' => $submittedLocation->id,
                'update_default_location' => '0',
            ]);

        $assets->each(function (Asset $asset) use ($submittedLocation) {
            $fresh = $asset->fresh();
            $this->assertEquals($submittedLocation->id, $fresh->location_id);
            $this->assertEquals($submittedLocation->id, $fresh->rtd_location_id);
        });
    }

    public function test_license_seats_are_cleared_when_checkbox_is_checked()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        LicenseSeat::factory()->assignedToUser()->for($asset)->create();

        $this->assertNotNull($asset->licenseseats->first()->assigned_to);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
                'checkin_licenses' => '1',
            ]);

        $this->assertNull($asset->fresh()->licenseseats->first()->assigned_to);
    }

    public function test_license_seats_are_retained_when_checkbox_is_unchecked()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        LicenseSeat::factory()->assignedToUser()->for($asset)->create();

        $originalAssignedTo = $asset->licenseseats->first()->assigned_to;
        $this->assertNotNull($originalAssignedTo);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
                'checkin_licenses' => '0',
            ]);

        $this->assertEquals($originalAssignedTo, $asset->fresh()->licenseseats->first()->assigned_to);
    }

    public function test_pending_checkout_acceptances_are_cleared_upon_bulk_checkin()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        $acceptance = CheckoutAcceptance::factory()->for($asset, 'checkoutable')->pending()->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
            ]);

        $this->assertFalse($acceptance->exists(), 'Pending acceptance was not deleted upon bulk checkin');
    }

    public function test_checkin_date_and_note_are_passed_to_event()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
                'checkin_at' => '2024-06-15',
                'note' => 'bulk checkin note',
            ]);

        Event::assertDispatched(function (CheckoutableCheckedIn $event) {
            return $event->action_date === '2024-06-15'
                && $event->note === 'bulk checkin note';
        });
    }

    public function test_child_asset_locations_are_updated_when_checkbox_is_checked()
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
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$parentAsset->id],
                'checkin_child_assets' => '1',
            ]);

        $this->assertEquals($originalLocation->id, $parentAsset->fresh()->location_id);
        $this->assertEquals($originalLocation->id, $childAsset->fresh()->location_id);
    }

    public function test_child_asset_locations_are_retained_when_checkbox_is_unchecked()
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
        ]);

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$parentAsset->id],
                'checkin_child_assets' => '0',
            ]);

        $this->assertEquals($originalLocation->id, $parentAsset->fresh()->location_id);
        $this->assertEquals($checkedOutLocation->id, $childAsset->fresh()->location_id);
    }

    public function test_origin_url_is_stored_when_routing_to_bulk_checkin()
    {
        $assets = Asset::factory()->assignedToUser()->count(2)->create();
        $originUrl = route('hardware.index').'?status_type=Deployed';

        $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
            ->from($originUrl)
            ->post(route('hardware.bulkedit.show'), [
                'ids' => $assets->pluck('id')->toArray(),
                'bulk_actions' => 'checkin',
                'sort' => 'id',
                'order' => 'asc',
            ])
            ->assertSessionHas('url.intended', $originUrl);
    }

    public function test_successful_checkin_redirects_to_origin_url()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        $originUrl = route('hardware.index').'?status_type=Deployed';

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->withSession(['url.intended' => $originUrl])
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
            ])
            ->assertRedirect($originUrl);
    }

    public function test_external_referer_is_not_stored_as_intended_url()
    {
        $assets = Asset::factory()->assignedToUser()->count(2)->create();

        $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
            ->from('https://evil.example.com/phish')
            ->post(route('hardware.bulkedit.show'), [
                'ids' => $assets->pluck('id')->toArray(),
                'bulk_actions' => 'checkin',
                'sort' => 'id',
                'order' => 'asc',
            ])
            ->assertSessionMissing('url.intended');
    }

    public function test_successful_checkin_falls_back_to_hardware_index_without_origin_url()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => [$asset->id],
            ])
            ->assertRedirect(route('hardware.index'));
    }

    public function test_returns_error_when_no_assets_selected()
    {
        $this->actingAs(User::factory()->checkinAssets()->create())
            ->post(route('hardware.bulkcheckin.store'), [
                'selected_assets' => null,
            ])
            ->assertRedirectToRoute('hardware.bulkcheckin.show')
            ->assertSessionHas('error');
    }
}
