<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Verifies that a location cannot be given a parent that belongs to a different
 * company when FMCS is enabled, and that the check is bypassed when FMCS is off.
 *
 * The API update case also covers the scenario where only parent_id changes
 * (company_id not included in the request), to ensure the check runs regardless.
 */
class LocationParentCompanyTest extends TestCase
{
    // -----------------------------------------------------------------------
    // store (create)
    // -----------------------------------------------------------------------

    public function test_cannot_create_location_with_cross_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.locations.store'), [
                'name' => 'Location B',
                'company_id' => $globex->id,
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertFalse(Location::where('name', 'Location B')->exists());
    }

    public function test_can_create_location_with_same_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.locations.store'), [
                'name' => 'Location B',
                'company_id' => $acme->id,
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertTrue(Location::where('name', 'Location B')->exists());
    }

    public function test_can_create_location_with_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.locations.store'), [
                'name' => 'Location B',
                'company_id' => $globex->id,
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertTrue(Location::where('name', 'Location B')->exists());
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_cannot_update_location_to_cross_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name' => 'Location B',
                'company_id' => $globex->id,
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNull($location->fresh()->parent_id);
    }

    public function test_cannot_update_location_parent_id_only_to_cross_company_parent_when_fmcs_enabled()
    {
        // Ensures the check fires even when company_id is not included in the request.
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNull($location->fresh()->parent_id);
    }

    public function test_can_update_location_to_same_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location = Location::factory()->create(['name' => 'Location B', 'company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name' => 'Location B',
                'company_id' => $acme->id,
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEquals($parentLocation->id, $location->fresh()->parent_id);
    }

    public function test_can_update_location_to_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name' => 'Location B',
                'company_id' => $globex->id,
                'parent_id' => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEquals($parentLocation->id, $location->fresh()->parent_id);
    }

    public function test_scoped_actor_cannot_create_location_with_cross_company_parent_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $foreignParent = Location::factory()->create(['company_id' => $globex->id]);
        $scopedActor = User::factory()->createLocations()->forCompany($acme)->create();

        $this->actingAsForApi($scopedActor)
            ->postJson(route('api.locations.store'), [
                'name' => 'Forged Child',
                'company_id' => $acme->id,
                'parent_id' => $foreignParent->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertFalse(Location::where('name', 'Forged Child')->exists());
    }

    public function test_scoped_actor_cannot_update_location_to_cross_company_parent_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $foreignParent = Location::factory()->create(['company_id' => $globex->id]);
        $ownLocation = Location::factory()->create(['company_id' => $acme->id]);
        $scopedActor = User::factory()->editLocations()->forCompany($acme)->create();

        $this->actingAsForApi($scopedActor)
            ->patchJson(route('api.locations.update', $ownLocation), [
                'parent_id' => $foreignParent->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNull($ownLocation->fresh()->parent_id);
    }

    public function test_scoped_actor_cannot_create_location_with_cross_company_parent_under_scoped_locations_fmcs()
    {
        $acme = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $foreignParent = Location::factory()->create(['company_id' => $globex->id]);
        $scopedActor = User::factory()->createLocations()->forCompany($acme)->create();

        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $this->actingAsForApi($scopedActor)
            ->postJson(route('api.locations.store'), [
                'name' => 'Forged Child Scoped',
                'company_id' => $acme->id,
                'parent_id' => $foreignParent->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertFalse(Location::where('name', 'Forged Child Scoped')->exists());
    }

    // -----------------------------------------------------------------------
    // Model-rule defense in depth
    //
    // The controller checks above are the primary reject path for
    // API/UI/bulk callers. The same invariant is also enforced by the
    // parent_matches_location_company validation rule on the Location
    // model, which catches any save() that skips the controller entirely
    // (importer, seeder, job, future controller). These tests write
    // directly to the model to prove the rule fires without any HTTP
    // request in the mix.
    // -----------------------------------------------------------------------

    public function test_model_save_rejects_cross_company_parent_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create();
        $globex = Company::factory()->create();
        $foreignParent = Location::factory()->create(['company_id' => $globex->id]);

        $location = Location::factory()->make([
            'name' => 'Direct-Save Attempt',
            'company_id' => $acme->id,
            'parent_id' => $foreignParent->id,
        ]);

        $this->assertFalse($location->save());
        $this->assertFalse(Location::where('name', 'Direct-Save Attempt')->exists());
    }

    public function test_model_save_allows_same_company_parent_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create();
        $sameCompanyParent = Location::factory()->create(['company_id' => $acme->id]);

        $location = Location::factory()->make([
            'name' => 'Direct-Save Same Company',
            'company_id' => $acme->id,
            'parent_id' => $sameCompanyParent->id,
        ]);

        $this->assertTrue($location->save());
    }

    public function test_model_save_allows_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme = Company::factory()->create();
        $globex = Company::factory()->create();
        $foreignParent = Location::factory()->create(['company_id' => $globex->id]);

        $location = Location::factory()->make([
            'name' => 'Direct-Save FMCS Off',
            'company_id' => $acme->id,
            'parent_id' => $foreignParent->id,
        ]);

        $this->assertTrue($location->save());
    }

    // GHSA-vx68-xhq6-9gmr: cross-company parent rejection used to interpolate
    // the parent location's name and company name into the error message,
    // leaking cross-tenant names to any caller with locations.create /
    // locations.edit permission. The rejection now returns a generic message
    // when the caller cannot see the parent location. Superusers and callers
    // whose pivot includes the parent's company still get the detailed
    // diagnostic.

    public function test_api_store_cross_company_parent_does_not_leak_name_to_non_superuser(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->createLocations()->make());

        $secret = Location::factory()->make(['name' => 'Vault Secret HQ']);
        $secret->company_id = $companyB->id;
        $secret->setValidating(false);
        $secret->save();

        $response = $this->actingAsForApi($actor)->postJson(
            route('api.locations.store'),
            [
                'name' => 'probe',
                'company_id' => $companyA->id,
                'parent_id' => $secret->id,
            ]
        );

        $response->assertOk()->assertStatusMessageIs('error');
        $this->assertStringNotContainsString('Vault Secret HQ', (string) $response->json('messages'));
    }

    public function test_api_store_cross_company_parent_still_discloses_to_superuser(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $secret = Location::factory()->create([
            'name' => 'Vault Secret HQ',
            'company_id' => $companyB->id,
        ]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())->postJson(
            route('api.locations.store'),
            [
                'name' => 'probe',
                'company_id' => $companyA->id,
                'parent_id' => $secret->id,
            ]
        );

        $response->assertOk()->assertStatusMessageIs('error');
        $this->assertStringContainsString('Vault Secret HQ', (string) $response->json('messages'));
    }

    public function test_api_update_cross_company_parent_does_not_leak_name_to_non_superuser(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editLocations()->make());

        $own = Location::factory()->create(['name' => 'My Loc', 'company_id' => $companyA->id]);

        $secret = Location::factory()->make(['name' => 'Vault Secret HQ']);
        $secret->company_id = $companyB->id;
        $secret->setValidating(false);
        $secret->save();

        $response = $this->actingAsForApi($actor)->patchJson(
            route('api.locations.update', $own->id),
            ['parent_id' => $secret->id]
        );

        $response->assertOk()->assertStatusMessageIs('error');
        $this->assertStringNotContainsString('Vault Secret HQ', (string) $response->json('messages'));
    }
}
