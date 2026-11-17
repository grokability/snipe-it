<?php

namespace Tests\Feature\Licenses\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class BulkCheckinSelectedLicenseSeatsTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $seat = LicenseSeat::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertForbidden();
    }

    public function test_requires_permission_on_show()
    {
        $seat = LicenseSeat::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('licenses.bulkcheckin.show'), ['ids' => [$seat->id]])
            ->assertForbidden();
    }

    public function test_show_renders_interstitial_with_selected_seats()
    {
        $license = License::factory()->create(['seats' => 3]);
        $seat1 = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $license->id]);
        $seat2 = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.show'), ['ids' => [$seat1->id, $seat2->id]])
            ->assertOk()
            ->assertViewIs('licenses.bulk-checkin')
            ->assertViewHas('ids', [$seat1->id, $seat2->id]);

        // Nothing is actually checked in until the interstitial posts
        // to .store.
        $this->assertNotNull($seat1->fresh()->assigned_to);
        $this->assertNotNull($seat2->fresh()->assigned_to);
    }

    public function test_show_warns_when_no_rows_selected()
    {
        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.show'), ['ids' => []])
            ->assertRedirect()
            ->assertSessionHas('warning', trans('admin/licenses/general.bulk.checkin_selected.no_seats_selected'));
    }

    public function test_can_bulk_checkin_seats_assigned_to_users()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 3]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $seat1 = LicenseSeat::factory()->assignedToUser($user1)->create(['license_id' => $license->id]);
        $seat2 = LicenseSeat::factory()->assignedToUser($user2)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat1->id, $seat2->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($seat1->fresh()->assigned_to);
        $this->assertNull($seat2->fresh()->assigned_to);

        Event::assertDispatched(CheckoutableCheckedIn::class, 2);
    }

    public function test_can_bulk_checkin_seats_assigned_to_assets()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2]);
        $asset = Asset::factory()->create();
        $seat = LicenseSeat::factory()->assignedToAsset($asset)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($seat->fresh()->asset_id);

        Event::assertDispatched(CheckoutableCheckedIn::class, 1);
    }

    public function test_empty_ids_returns_warning()
    {
        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => []])
            ->assertRedirect()
            ->assertSessionHas('warning', trans('admin/licenses/general.bulk.checkin_selected.no_seats_selected'));
    }

    public function test_missing_ids_returns_warning()
    {
        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), [])
            ->assertRedirect()
            ->assertSessionHas('warning', trans('admin/licenses/general.bulk.checkin_selected.no_seats_selected'));
    }

    public function test_unassigned_seats_in_submitted_ids_are_skipped()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2]);
        $unassignedSeat = LicenseSeat::factory()->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$unassignedSeat->id]])
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertNull($unassignedSeat->fresh()->assigned_to);
        $this->assertNull($unassignedSeat->fresh()->asset_id);

        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    public function test_non_reassignable_license_marks_unreassignable_seat()
    {
        $license = License::factory()->create(['seats' => 2, 'reassignable' => false]);
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->assignedToUser($user)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) $seat->fresh()->unreassignable_seat);
    }

    public function test_reassignable_license_does_not_mark_unreassignable_seat()
    {
        $license = License::factory()->create(['seats' => 2, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->assignedToUser($user)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse((bool) $seat->fresh()->unreassignable_seat);
    }

    public function test_only_submitted_seat_ids_are_processed()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 3]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $seat1 = LicenseSeat::factory()->assignedToUser($user1)->create(['license_id' => $license->id]);
        $seat2 = LicenseSeat::factory()->assignedToUser($user2)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat1->id]])
            ->assertRedirect();

        $this->assertNull($seat1->fresh()->assigned_to);
        $this->assertNotNull($seat2->fresh()->assigned_to);

        Event::assertDispatched(CheckoutableCheckedIn::class, 1);
    }

    public function test_success_message_is_pluralized_correctly()
    {
        $license = License::factory()->create(['seats' => 3]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $seat1 = LicenseSeat::factory()->assignedToUser($user1)->create(['license_id' => $license->id]);
        $seat2 = LicenseSeat::factory()->assignedToUser($user2)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat1->id, $seat2->id]])
            ->assertSessionHas('success', trans_choice('admin/licenses/general.bulk.checkin_selected.success', 2, ['count' => 2]));
    }

    public function test_checkin_event_contains_correct_target_for_user_seat()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2]);
        $targetUser = User::factory()->create();
        $seat = LicenseSeat::factory()->assignedToUser($targetUser)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]]);

        Event::assertDispatched(CheckoutableCheckedIn::class, function ($event) use ($targetUser) {
            return $event->checkedOutTo->id === $targetUser->id;
        });
    }

    public function test_checkin_event_contains_correct_target_for_asset_seat()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2]);
        $targetAsset = Asset::factory()->create();
        $seat = LicenseSeat::factory()->assignedToAsset($targetAsset)->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]]);

        Event::assertDispatched(CheckoutableCheckedIn::class, function ($event) use ($targetAsset) {
            return $event->checkedOutTo->id === $targetAsset->id;
        });
    }

    public function test_shared_note_lands_on_every_action_log()
    {
        // Regression test: the whole reason for the interstitial is to
        // capture a shared note. The posted `note` must thread into
        // every per-row CheckoutableCheckedIn event so each resulting
        // action_log row carries it.
        $license = License::factory()->create(['seats' => 3]);
        $seat1 = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $license->id]);
        $seat2 = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.bulkcheckin.store'), [
                'ids' => [$seat1->id, $seat2->id],
                'note' => 'End-of-lease returns, batch 2026-Q4',
            ])
            ->assertRedirect();

        $logs = Actionlog::where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'checkin from')
            ->get();

        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertSame('End-of-lease returns, batch 2026-Q4', $log->note);
        }
    }

    public function test_fmcs_prevents_checkin_of_seat_from_other_company()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        [$myCompany, $otherCompany] = Company::factory()->count(2)->create();

        $actor = User::factory()->checkinLicenses()->forCompany($myCompany->id)->create();
        $otherLicense = License::factory()->create(['company_id' => $otherCompany->id, 'seats' => 2]);
        $targetUser = User::factory()->forCompany($otherCompany->id)->create();
        $seat = LicenseSeat::factory()->assignedToUser($targetUser)->create(['license_id' => $otherLicense->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($actor)
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertRedirect();

        $this->assertNotNull($seat->fresh()->assigned_to);

        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    public function test_bulk_checkin_show_does_not_expose_cross_company_seat_under_fmcs()
    {
        // The interstitial view must filter the same way as .store.
        // LicenseSeat has no CompanyableTrait, so the controller leans
        // on the eager-loaded `license` relation (which auto-applies
        // CompanyableScope) and short-circuits when it comes back null.
        $this->settings->enableMultipleFullCompanySupport();

        [$myCompany, $otherCompany] = Company::factory()->count(2)->create();
        $actor = User::factory()->checkinLicenses()->viewLicenses()->forCompany($myCompany->id)->create();
        $otherLicense = License::factory()->create(['company_id' => $otherCompany->id, 'seats' => 2]);
        $seat = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $otherLicense->id]);

        $response = $this->actingAs($actor)
            ->post(route('licenses.bulkcheckin.show'), ['ids' => [$seat->id]]);

        if ($response->status() !== 200) {
            $response->assertRedirect()->assertSessionHas('warning');

            return;
        }

        // If the interstitial renders at all, the foreign seat must
        // be absent from the collection or the license column null.
        $seats = $response->viewData('seats');
        $visible = $seats->filter(fn ($row) => $row->license !== null);
        $this->assertFalse(
            $visible->contains('id', $seat->id),
            'bulkCheckinShow must not expose cross-tenant seats with their license visible',
        );
    }

    public function test_floater_actor_can_bulk_checkin_null_company_license_seat()
    {
        // Floater ON: company A actor is allowed to touch null-company
        // (floater) licenses. Pin so a future refactor that over-tightens
        // the lookup doesn't block a legitimate flow.
        Event::fake([CheckoutableCheckedIn::class]);

        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $actor = User::factory()->checkinLicenses()->forCompany($companyA->id)->create();

        $floaterLicense = License::factory()->create(['company_id' => null, 'seats' => 2]);
        $seat = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $floaterLicense->id]);

        $this->actingAs($actor)
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($seat->fresh()->assigned_to);
        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    public function test_scope_locations_fmcs_still_blocks_cross_company_seat_checkin()
    {
        // scope_locations_fmcs is orthogonal to seat checkin (licenses
        // aren't location-scoped on their own), but FMCS strictness must
        // still hold when the setting is on. Pin so a refactor that
        // conflates the two modes can't silently loosen either.
        Event::fake([CheckoutableCheckedIn::class]);

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->checkinLicenses()->forCompany($companyA->id)->create();
        $foreignLicense = License::factory()->create(['company_id' => $companyB->id, 'seats' => 2]);
        $seat = LicenseSeat::factory()->assignedToUser()->create(['license_id' => $foreignLicense->id]);

        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $this->actingAs($actor)
            ->post(route('licenses.bulkcheckin.store'), ['ids' => [$seat->id]])
            ->assertRedirect();

        $this->assertNotNull($seat->fresh()->assigned_to);
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }
}
