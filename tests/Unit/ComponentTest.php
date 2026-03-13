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
    public function testAComponentBelongsToACompany()
    {
        $component = Component::factory()
            ->create(
                    [
                        'company_id' => Company::factory()->create()->id
                    ]
                );
        $this->assertInstanceOf(Company::class, $component->company);
    }

    public function testAComponentHasALocation()
    {
        $component = Component::factory()
            ->create(['location_id' => Location::factory()->create()->id]);
        $this->assertInstanceOf(Location::class, $component->location);
    }

    public function testAComponentBelongsToACategory()
    {
        $component = Component::factory()->ramCrucial4()
            ->create(
                [
                    'category_id' => 
                        Category::factory()->create(
                            [
                                'category_type' => 'component'
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
}
