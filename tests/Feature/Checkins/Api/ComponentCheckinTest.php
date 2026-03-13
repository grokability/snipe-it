<?php

namespace Tests\Feature\Checkins\Api;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class ComponentCheckinTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.components.checkin', $component->assets->first()->pivot->id))
            ->assertForbidden();
    }

    public function testHandlesNonExistentPivotId()
    {
        $this->actingAsForApi(User::factory()->checkinComponents()->create())
            ->postJson(route('api.components.checkin', 1000), [
                'checkin_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testHandlesNonExistentComponent()
    {
        $component = Component::factory()->checkedOutToAsset()->create();
        $pivotId = $component->assets->first()->pivot->id;
        $component->delete();

        $this->actingAsForApi(User::factory()->checkinComponents()->create())
            ->postJson(route('api.components.checkin', $pivotId), [
                'checkin_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testCannotCheckinMoreThanCheckedOut()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $pivot = $component->assets->first()->pivot;
        $pivot->update(['assigned_qty' => 1]);

        $this->actingAsForApi(User::factory()->checkinComponents()->create())
            ->postJson(route('api.components.checkin', $component->assets->first()->pivot->id), [
                'checkin_qty' => 3,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testCanCheckinComponent()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->checkinComponents()->create();

        $component = Component::factory()->checkedOutToAsset()->create();
        $pivot = $component->assets->first()->pivot;
        $pivot->update(['assigned_qty' => 3]);


        $this->actingAsForApi($user)
            ->postJson(route('api.components.checkin', $component->assets->first()->pivot->id), [
                'checkin_qty' => 2,
                'note' => 'my note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEquals(1, $component->fresh()->assets->first()->pivot->assigned_qty);
        $this->assertHasTheseActionLogs($component, ['create']); //FIXME?


        Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($user, $component) {
            return $event->checkoutable->is($component)
                && $event->checkedOutTo->is($component->assets->first())
                && $event->checkedInBy->is($user)
                && $event->note === 'my note';
        });
    }

    public function testCheckingInEntireAssignedQuantityClearsThePivotRecordFromTheDatabase()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->checkinComponents()->create();

        $component = Component::factory()->checkedOutToAsset()->create();
        $pivot = $component->assets->first()->pivot;
        $pivot->update(['assigned_qty' => 3]);

        $this->actingAsForApi($user)
            ->postJson(route('api.components.checkin', $component->assets->first()->pivot->id), [
                'checkin_qty' => 3,
                'note' => 'my note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEmpty($component->fresh()->assets);

        Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($user, $component) {
            return $event->checkoutable->is($component)
                && $event->checkedOutTo->is($component->assets->first())
                && $event->checkedInBy->is($user)
                && $event->note === 'my note';
        });
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $componentInCompanyA = Component::factory()->for($companyA)->checkedOutToAsset()->create();
        $userInCompanyB = User::factory()->for($companyB)->create();
        $pivotId = $componentInCompanyA->assets->first()->pivot->id;

        $this->actingAsForApi($userInCompanyB)
            ->postJson(route('api.components.checkin', $pivotId), [
                'checkin_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testCheckinIsLogged()
    {
        $user = User::factory()->checkinComponents()->create();

        $component = Component::factory()->checkedOutToAsset()->create();
        $pivot = $component->assets->first()->pivot;
        $pivot->update(['assigned_qty' => 3]);

        $this->actingAsForApi($user)
            ->postJson(route('api.components.checkin', $component->assets->first()->pivot->id), [
                'checkin_qty' => 3,
                'note' => 'my note',
            ]);

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $user->id,
            'action_type' => 'checkin from',
            'target_id' => $component->assets->first()->id,
            'target_type' => Asset::class,
            'note' => 'my note',
            'item_id' => $component->id,
            'item_type' => Component::class,
        ]);
        $this->assertHasTheseActionLogs($component, ['create', /*'checkout',*/ 'checkin from']); //FIXME?
    }
}
