<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Verifies that a location cannot be given a parent that belongs to a different
 * company when FMCS is enabled, and that the check is bypassed when FMCS is off.
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

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('locations.create'))
            ->post(route('locations.store'), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertRedirect(route('locations.create'))
            ->assertSessionHas('error');

        $this->assertFalse(Location::where('name', 'Location B')->exists());
    }

    public function test_can_create_location_with_same_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('locations.store'), [
                'name'       => 'Location B',
                'company_id' => $acme->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertRedirect(route('locations.index'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Location::where('name', 'Location B')->exists());
    }

    public function test_can_create_location_with_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('locations.store'), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertRedirect(route('locations.index'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Location::where('name', 'Location B')->exists());
    }

    // -----------------------------------------------------------------------
    // update (edit)
    // -----------------------------------------------------------------------

    public function test_cannot_update_location_to_cross_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('locations.edit', $location))
            ->put(route('locations.update', $location), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertRedirect(route('locations.edit', $location))
            ->assertSessionHas('error');

        $this->assertNull($location->fresh()->parent_id);
    }

    public function test_can_update_location_to_same_company_parent_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $acme = Company::factory()->create(['name' => 'Acme']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $acme->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('locations.update', $location), [
                'name'       => 'Location B',
                'company_id' => $acme->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertRedirect(route('locations.index'))
            ->assertSessionHasNoErrors();

        $this->assertEquals($parentLocation->id, $location->fresh()->parent_id);
    }

    public function test_can_update_location_to_cross_company_parent_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $acme   = Company::factory()->create(['name' => 'Acme']);
        $globex = Company::factory()->create(['name' => 'Globex']);

        $parentLocation = Location::factory()->create(['company_id' => $acme->id]);
        $location       = Location::factory()->create(['name' => 'Location B', 'company_id' => $globex->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('locations.update', $location), [
                'name'       => 'Location B',
                'company_id' => $globex->id,
                'parent_id'  => $parentLocation->id,
            ])
            ->assertRedirect(route('locations.index'))
            ->assertSessionHasNoErrors();

        $this->assertEquals($parentLocation->id, $location->fresh()->parent_id);
    }
}
