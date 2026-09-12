<?php

namespace Tests\Feature\Checkins\Api;

use App\Mail\CheckinAccessoryMail;
use App\Models\Accessory;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class AccessoryCheckinTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();
        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertForbidden();
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = User::factory()->forCompany($companyA)->checkinAccessories()->create();
        $accessoryForCompanyB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();
        $anotherAccessoryForCompanyB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();

        $this->assertEquals(1, $accessoryForCompanyB->checkouts->count());
        $this->assertEquals(1, $anotherAccessoryForCompanyB->checkouts->count());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->postJson(route('api.accessories.checkin', $accessoryForCompanyB->checkouts->first()))
            ->assertForbidden();

        $this->actingAsForApi($superUser)
            ->postJson(route('api.accessories.checkin', $anotherAccessoryForCompanyB->checkouts->first()))
            ->assertStatusMessageIs('success');

        $this->assertEquals(1, $accessoryForCompanyB->fresh()->checkouts->count(), 'Accessory should not be checked in');
        $this->assertEquals(0, $anotherAccessoryForCompanyB->fresh()->checkouts->count(), 'Accessory should be checked in');
        $this->assertHasTheseActionLogs($anotherAccessoryForCompanyB, ['create', 'checkin from']);
    }

    public function test_can_checkin_accessory()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->assertEquals(1, $accessory->checkouts->count());

        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi(User::factory()->checkinAccessories()->create())
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertStatusMessageIs('success');

        $this->assertEquals(0, $accessory->fresh()->checkouts->count(), 'Accessory should be checked in');
        $this->assertHasTheseActionLogs($accessory, ['create'/* , 'checkout' */, 'checkin from']); // TODO - should be the 3 events!
    }

    public function test_checkin_is_logged()
    {
        $user = User::factory()->create();
        $actor = User::factory()->checkinAccessories()->create();

        $accessory = Accessory::factory()->checkedOutToUser($user)->create();
        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $actor->id,
            'action_type' => 'checkin from',
            'target_id' => $user->id,
            'target_type' => User::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
        ]);

        $this->assertHasTheseActionLogs($accessory, ['create', 'checkin from']);
    }

    public function test_checkin_of_accessory_checked_out_to_location_is_logged_against_the_location()
    {
        $location = Location::factory()->create();
        $actor = User::factory()->checkinAccessories()->create();

        $accessory = Accessory::factory()->checkedOutToLocation($location)->create();
        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $actor->id,
            'action_type' => 'checkin from',
            'target_id' => $location->id,
            'target_type' => Location::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
        ]);
    }

    public function test_checkin_sends_checkin_email_to_user_when_category_enables_it()
    {
        Mail::fake();

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();
        $accessory->category->update(['checkin_email' => true]);

        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi(User::factory()->checkinAccessories()->create())
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertStatusMessageIs('success');

        Mail::assertSent(
            CheckinAccessoryMail::class,
            fn ($mail) => $mail->hasTo($user->email),
        );
    }

    public function test_checkin_clears_pending_acceptance_when_notifications_are_fully_disabled()
    {
        $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $accessory->category->update([
            'require_acceptance' => true,
            'checkin_email' => false,
        ]);

        $checkout = $accessory->checkouts()
            ->where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->firstOrFail();

        $acceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertTrue($acceptance->isPending());

        $this->actingAsForApi(User::factory()->checkinAccessories()->create())
            ->postJson(route('api.accessories.checkin', $checkout))
            ->assertStatusMessageIs('success');

        $this->assertNotNull(
            CheckoutAcceptance::withTrashed()->findOrFail($acceptance->id)->deleted_at,
            'Pending acceptance survived accessory check-in.'
        );
    }
}
