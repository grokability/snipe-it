<?php

namespace Tests\Feature\Fmcs;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class FmcsSelfServiceLocationTest extends TestCase
{
    public function test_self_service_cannot_set_cross_company_location(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->canEditOwnLocation()->forCompany($companyA)->create();

        $foreignLocation = Location::factory()->make();
        $foreignLocation->company_id = $companyB->id;
        $foreignLocation->setValidating(false);
        $foreignLocation->save();

        $this->actingAs($actor)
            ->from(route('profile'))
            ->post(route('profile'), [
                'first_name' => $actor->first_name,
                'location_id' => $foreignLocation->id,
            ])
            ->assertSessionHasErrors('location_id');

        $this->assertNotSame(
            $foreignLocation->id,
            (int) $actor->fresh()->location_id,
            'Cross-company location_id must not persist on self-service profile update.',
        );
    }

    public function test_self_service_can_set_same_company_location(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $actor = User::factory()->canEditOwnLocation()->forCompany($company)->create();

        $location = Location::factory()->make();
        $location->company_id = $company->id;
        $location->setValidating(false);
        $location->save();

        $this->actingAs($actor)
            ->from(route('profile'))
            ->post(route('profile'), [
                'first_name' => $actor->first_name,
                'location_id' => $location->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($location->id, (int) $actor->fresh()->location_id);
    }

    public function test_self_service_null_location_passes(): void
    {
        /*
         * Clearing the location (user has one, wants none) must stay
         * possible. The nullable guard on the fmcs rule chain already
         * does this, pin the behavior.
         */
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->make();
        $location->company_id = $company->id;
        $location->setValidating(false);
        $location->save();

        $actor = User::factory()->canEditOwnLocation()->forCompany($company)->create([
            'location_id' => $location->id,
        ]);

        $this->actingAs($actor)
            ->from(route('profile'))
            ->post(route('profile'), [
                'first_name' => $actor->first_name,
                'location_id' => null,
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($actor->fresh()->location_id);
    }

    public function test_scope_locations_fmcs_off_does_not_block_cross_company_location(): void
    {
        /*
         * fmcs_location is gated on scope_locations_fmcs = 1. When that
         * admin setting is off, locations are treated as unscoped and the
         * cross-company check does not fire on EITHER the admin or
         * self-service path. Pin that behavior so future tightening is a
         * deliberate decision rather than a silent regression.
         */
        $this->settings->enableMultipleFullCompanySupport();
        // scope_locations_fmcs remains off (default)

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->canEditOwnLocation()->forCompany($companyA)->create();

        $foreignLocation = Location::factory()->make();
        $foreignLocation->company_id = $companyB->id;
        $foreignLocation->setValidating(false);
        $foreignLocation->save();

        $this->actingAs($actor)
            ->from(route('profile'))
            ->post(route('profile'), [
                'first_name' => $actor->first_name,
                'location_id' => $foreignLocation->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($foreignLocation->id, (int) $actor->fresh()->location_id);
    }
}
