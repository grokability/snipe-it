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

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.locations.store'), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
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
                'name'       => 'Location B',
                'company_id' => $acme->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertTrue(Location::where('name', 'Location B')->exists());
    }

    public function test_can_create_location_with_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.locations.store'), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
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

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNull($location->fresh()->parent_id);
    }

    public function test_cannot_update_location_parent_id_only_to_cross_company_parent_when_fmcs_enabled()
    {
        // Ensures the check fires even when company_id is not included in the request.
        $this->settings->enableMultipleFullCompanySupport();

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

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
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $acme->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name'       => 'Location B',
                'company_id' => $acme->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEquals($parentLocation->id, $location->fresh()->parent_id);
    }

    public function test_can_update_location_to_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEquals($parentLocation->id, $location->fresh()->parent_id);
    }
}
