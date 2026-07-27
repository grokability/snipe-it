<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Company;
use App\Models\Location;
use Tests\TestCase;

/**
 * Location::name used to enforce global uniqueness via `unique_undeleted`.
 * That blocked reasonable structures like `DC1 / Rack 1` and `DC2 / Rack 1`
 * as siblings under different parents. `unique_undeleted_in_scope` scopes
 * uniqueness to `(name, parent_id, company_id)` so children only need to
 * be unique among their own siblings within their own company.
 */
class LocationNameUniquenessTest extends TestCase
{
    public function test_two_children_with_the_same_name_under_different_parents_can_coexist(): void
    {
        $dc1 = Location::factory()->create(['name' => 'DC1', 'company_id' => null]);
        $dc2 = Location::factory()->create(['name' => 'DC2', 'company_id' => null]);

        Location::factory()->create(['name' => 'Rack 1', 'parent_id' => $dc1->id, 'company_id' => null]);
        $rackUnderDc2 = Location::factory()->create(['name' => 'Rack 1', 'parent_id' => $dc2->id, 'company_id' => null]);

        $this->assertTrue($rackUnderDc2->exists);
        $this->assertDatabaseCount('locations', 4);
    }

    public function test_two_children_with_the_same_name_under_the_same_parent_still_collide(): void
    {
        $dc1 = Location::factory()->create(['name' => 'DC1', 'company_id' => null]);
        Location::factory()->create(['name' => 'Rack 1', 'parent_id' => $dc1->id, 'company_id' => null]);

        $duplicate = new Location([
            'name' => 'Rack 1',
            'parent_id' => $dc1->id,
            'company_id' => null,
        ]);

        $this->assertFalse($duplicate->save(), 'sibling name collision should still be rejected');
        $this->assertNotEmpty($duplicate->getErrors()->get('name'));
    }

    public function test_top_level_locations_with_the_same_name_still_collide(): void
    {
        Location::factory()->create(['name' => 'HQ', 'parent_id' => null, 'company_id' => null]);

        $duplicate = new Location([
            'name' => 'HQ',
            'parent_id' => null,
            'company_id' => null,
        ]);

        $this->assertFalse($duplicate->save(), 'two top-level locations sharing a name should still be rejected');
    }

    public function test_top_level_location_and_a_child_can_share_a_name(): void
    {
        // `HQ` at top level and `HQ` under some parent are in different
        // buckets (NULL parent_id vs. non-null parent_id), so they can
        // coexist per SQL uniqueness semantics.
        $parent = Location::factory()->create(['name' => 'DC1', 'parent_id' => null, 'company_id' => null]);

        $topLevelHq = Location::factory()->create(['name' => 'HQ', 'parent_id' => null, 'company_id' => null]);
        $childHq = Location::factory()->create(['name' => 'HQ', 'parent_id' => $parent->id, 'company_id' => null]);

        $this->assertTrue($topLevelHq->exists);
        $this->assertTrue($childHq->exists);
    }

    public function test_same_name_under_different_companies_can_coexist(): void
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $inA = Location::factory()->create(['name' => 'HQ', 'parent_id' => null, 'company_id' => $companyA->id]);
        $inB = Location::factory()->create(['name' => 'HQ', 'parent_id' => null, 'company_id' => $companyB->id]);

        $this->assertTrue($inA->exists);
        $this->assertTrue($inB->exists);
    }

    public function test_updating_a_location_does_not_conflict_with_itself(): void
    {
        // The prepareUniqueUndeletedInScopeRule trait method excludes the
        // row being edited from the collision check when the model exists.
        // Without that carve-out, save-without-name-change would falsely
        // fail on any subsequent update.
        $location = Location::factory()->create(['name' => 'Existing', 'company_id' => null]);
        $location->address = '123 Somewhere';

        $this->assertTrue($location->save(), 'saving an existing location without a name collision should succeed');
    }
}
