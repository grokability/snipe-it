<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Validates that FMCS location scoping is enforced (and not over-enforced) when
 * creating or updating a user via the UI with scope_locations_fmcs enabled.
 *
 * Rules under test:
 *  1. User + location in the same company → allowed.
 *  2. User in company A, location in company B → rejected.
 *  3. Location with no company + floater ON → allowed.
 *  4. Location with no company + floater OFF → rejected.
 *  5. No company selected for user → location company is irrelevant → allowed.
 */
class FmcsLocationValidationTest extends TestCase
{
    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'fmcs-test-'.uniqid(),
            'password' => 'testpassword1235!!',
            'password_confirmation' => 'testpassword1235!!',
        ], $overrides);
    }

    public function test_user_and_location_in_same_company_is_allowed_on_create()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.store'), $this->basePayload([
                'company_ids' => [$company->id],
                'location_id' => $location->id,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }

    public function test_user_and_location_in_different_companies_is_rejected_on_create()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $location = Location::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.store'), $this->basePayload([
                'company_ids' => [$companyA->id],
                'location_id' => $location->id,
            ]))
            ->assertSessionHasErrors('location_id');
    }

    public function test_null_company_location_with_floater_on_is_allowed_on_create()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => null]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.store'), $this->basePayload([
                'company_ids' => [$company->id],
                'location_id' => $location->id,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }

    public function test_null_company_location_with_floater_off_is_rejected_on_create()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => null]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.store'), $this->basePayload([
                'company_ids' => [$company->id],
                'location_id' => $location->id,
            ]))
            ->assertSessionHasErrors('location_id');
    }

    public function test_no_company_selected_allows_any_location_on_create()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.store'), $this->basePayload([
                'location_id' => $location->id,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }

    public function test_user_and_location_in_same_company_is_allowed_on_update()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'company_ids' => [$company->id],
                'location_id' => $location->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }

    public function test_user_and_location_in_different_companies_is_rejected_on_update()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $location = Location::factory()->create(['company_id' => $companyB->id]);
        $user = User::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'company_ids' => [$companyA->id],
                'location_id' => $location->id,
            ])
            ->assertSessionHasErrors('location_id');
    }
}
