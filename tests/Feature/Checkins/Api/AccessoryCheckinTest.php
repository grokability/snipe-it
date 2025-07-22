<?php

namespace Tests\Feature\Checkins\Api;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class AccessoryCheckinTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();
        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertForbidden();
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = User::factory()->for($companyA)->checkinAccessories()->create();
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

    public function testCanCheckinAccessory()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->assertEquals(1, $accessory->checkouts->count());

        $accessoryCheckout = $accessory->checkouts->first();

        $this->actingAsForApi(User::factory()->checkinAccessories()->create())
            ->postJson(route('api.accessories.checkin', $accessoryCheckout))
            ->assertStatusMessageIs('success');

        $this->assertEquals(0, $accessory->fresh()->checkouts->count(), 'Accessory should be checked in');
        $this->assertHasTheseActionLogs($accessory, ['create'/*, 'checkout'*/, 'checkin from']); // TODO - should be the 3 events!
    }

    public function testCheckinIsLogged()
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
    }
}
