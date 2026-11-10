<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationTest extends TestCase
{
    public function test_passes_if_not_self_parent()
    {
        $a = Location::factory()->make([
            'name' => 'Test Location',
            'id' => 1,
            'parent_id' => Location::factory()->create(['id' => 10])->id,
        ]);

        $this->assertTrue($a->isValid());
    }

    public function test_fails_if_self_parent()
    {
        $a = Location::factory()->make([
            'name' => 'Test Location',
            'id' => 1,
            'parent_id' => 1,
        ]);

        $this->assertFalse($a->isValid());
        $this->assertStringContainsString(trans('validation.non_circular', ['attribute' => 'parent id']), $a->getErrors());
    }

    public function test_effective_fmcs_company_id_inherits_from_nearest_ancestor_with_a_company()
    {
        $company = Company::factory()->create();
        $grandparent = Location::factory()->create(['company_id' => $company->id]);
        $parent = Location::factory()->create(['parent_id' => $grandparent->id]);
        $child = Location::factory()->create(['parent_id' => $parent->id]);

        $this->assertSame($company->id, $child->effectiveFmcsCompanyId());
    }
}
