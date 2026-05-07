<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class ComponentTest extends TestCase
{
    public function test_a_component_belongs_to_a_company()
    {
        $component = Component::factory()
            ->create(
                [
                    'company_id' => Company::factory()->create()->id,
                ]
            );
        $this->assertInstanceOf(Company::class, $component->company);
    }

    public function test_a_component_has_a_location()
    {
        $component = Component::factory()
            ->create(['location_id' => Location::factory()->create()->id]);
        $this->assertInstanceOf(Location::class, $component->location);
    }

    public function test_a_component_belongs_to_a_category()
    {
        $component = Component::factory()->ramCrucial4()
            ->create(
                [
                    'category_id' => Category::factory()->create(
                        [
                            'category_type' => 'component',
                        ]
                    )->id]);
        $this->assertInstanceOf(Category::class, $component->category);
        $this->assertEquals('component', $component->category->category_type);
    }

    public function test_num_checked_out_takes_does_not_scope_by_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $componentForCompanyA = Component::factory()->for($companyA)->create(['qty' => 5]);
        $assetForCompanyB = Asset::factory()->for($companyB)->create();

        // Ideally, we shouldn't have a component attached to an
        // asset from a different company but alas...
        $componentForCompanyA->assets()->attach($componentForCompanyA->id, [
            'component_id' => $componentForCompanyA->id,
            'assigned_qty' => 4,
            'asset_id' => $assetForCompanyB->id,
        ]);

        $this->actingAs(User::factory()->superuser()->create());
        $this->assertEquals(4, $componentForCompanyA->fresh()->numCheckedOut());

        $this->actingAs(User::factory()->admin()->create());
        $this->assertEquals(4, $componentForCompanyA->fresh()->numCheckedOut());

        $this->actingAs(User::factory()->for($companyA)->create());
        $this->assertEquals(4, $componentForCompanyA->fresh()->numCheckedOut());
    }

    public function test_num_remaining_takes_company_scoping_into_account()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $componentForCompanyA = Component::factory()->for($companyA)->create(['qty' => 5]);
        $assetForCompanyB = Asset::factory()->for($companyB)->create();

        // Ideally, we shouldn't have a component attached to an
        // asset from a different company but alas...
        $componentForCompanyA->assets()->attach($componentForCompanyA->id, [
            'component_id' => $componentForCompanyA->id,
            'assigned_qty' => 4,
            'asset_id' => $assetForCompanyB->id,
        ]);

        $this->actingAs(User::factory()->superuser()->create());
        $this->assertEquals(1, $componentForCompanyA->fresh()->numRemaining());

        $this->actingAs(User::factory()->admin()->create());
        $this->assertEquals(1, $componentForCompanyA->fresh()->numRemaining());

        $this->actingAs(User::factory()->for($companyA)->create());
        $this->assertEquals(1, $componentForCompanyA->fresh()->numRemaining());
    }

    public function test_percent_remaining_returns_zero_when_quantity_is_zero()
    {
        $component = new class extends Component
        {
            public int $remaining = 99;

            public function numRemaining()
            {
                return $this->remaining;
            }
        };
        $component->qty = 0;

        $this->assertEquals(0, $component->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_available_ratio()
    {
        $component = new class extends Component
        {
            public int $remaining = 3;

            public function numRemaining()
            {
                return $this->remaining;
            }
        };
        $component->qty = 8;

        $this->assertEquals(37.5, $component->percentRemaining());
    }

    public function test_percent_remaining_clamps_to_bounds()
    {
        $component = new class extends Component
        {
            public int $remaining = -5;

            public function numRemaining()
            {
                return $this->remaining;
            }
        };
        $component->qty = 10;
        $this->assertEquals(0.0, $component->percentRemaining());

        $component->remaining = 15;
        $this->assertEquals(100.0, $component->percentRemaining());
    }
}
