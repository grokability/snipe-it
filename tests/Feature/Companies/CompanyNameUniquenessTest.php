<?php

namespace Tests\Feature\Companies;

use App\Models\Company;
use Tests\TestCase;

/**
 * Company::name switched from global `unique_undeleted` to
 * `unique_undeleted_in_scope:parent_id` so a "West" sub-company under
 * two different parent companies can coexist.
 */
class CompanyNameUniquenessTest extends TestCase
{
    public function test_two_child_companies_with_the_same_name_under_different_parents_can_coexist(): void
    {
        $alpha = Company::factory()->create(['name' => 'AlphaCorp']);
        $bravo = Company::factory()->create(['name' => 'BravoCorp']);

        Company::factory()->create(['name' => 'West', 'parent_id' => $alpha->id]);
        $westUnderBravo = Company::factory()->create(['name' => 'West', 'parent_id' => $bravo->id]);

        $this->assertTrue($westUnderBravo->exists);
        $this->assertDatabaseCount('companies', 4);
    }

    public function test_two_child_companies_with_the_same_name_under_the_same_parent_still_collide(): void
    {
        $alpha = Company::factory()->create(['name' => 'AlphaCorp']);
        Company::factory()->create(['name' => 'West', 'parent_id' => $alpha->id]);

        $duplicate = new Company(['name' => 'West', 'parent_id' => $alpha->id]);

        $this->assertFalse($duplicate->save(), 'sibling company name collision should still be rejected');
        $this->assertNotEmpty($duplicate->getErrors()->get('name'));
    }

    public function test_two_top_level_companies_with_the_same_name_still_collide(): void
    {
        Company::factory()->create(['name' => 'AcmeGlobal', 'parent_id' => null]);

        $duplicate = new Company(['name' => 'AcmeGlobal', 'parent_id' => null]);

        $this->assertFalse($duplicate->save(), 'two top-level companies sharing a name should still be rejected');
    }

    public function test_top_level_and_child_can_share_a_name(): void
    {
        $parent = Company::factory()->create(['name' => 'ParentCorp']);
        $topLevel = Company::factory()->create(['name' => 'Sub', 'parent_id' => null]);
        $child = Company::factory()->create(['name' => 'Sub', 'parent_id' => $parent->id]);

        $this->assertTrue($topLevel->exists);
        $this->assertTrue($child->exists);
    }
}
