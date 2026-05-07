<?php

namespace Tests\Unit;

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use Tests\TestCase;

class AccessoryTest extends TestCase
{
    public function test_an_accessory_belongs_to_a_company()
    {
        $accessory = Accessory::factory()
            ->create(
                [
                    'company_id' => Company::factory()->create()->id]);
        $this->assertInstanceOf(Company::class, $accessory->company);
    }

    public function test_an_accessory_has_a_location()
    {
        $accessory = Accessory::factory()
            ->create(
                [
                    'location_id' => Location::factory()->create()->id,
                ]);
        $this->assertInstanceOf(Location::class, $accessory->location);
    }

    public function test_an_accessory_belongs_to_a_category()
    {
        $accessory = Accessory::factory()->appleBtKeyboard()
            ->create(
                [
                    'category_id' => Category::factory()->create(
                        [
                            'category_type' => 'accessory',
                        ]
                    )->id]);
        $this->assertInstanceOf(Category::class, $accessory->category);
        $this->assertEquals('accessory', $accessory->category->category_type);
    }

    public function test_an_accessory_has_a_manufacturer()
    {
        $accessory = Accessory::factory()->appleBtKeyboard()->create(
            [
                'category_id' => Category::factory()->create(),
                'manufacturer_id' => Manufacturer::factory()->apple()->create(),
            ]);
        $this->assertInstanceOf(Manufacturer::class, $accessory->manufacturer);
    }

    public function test_percent_remaining_returns_one_hundred_when_nothing_is_checked_out()
    {
        $accessory = new Accessory([
            'qty' => 10,
        ]);
        $accessory->checkouts_count = 0;

        $this->assertEquals(100, $accessory->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_value_when_partially_checked_out()
    {
        $accessory = new Accessory([
            'qty' => 10,
        ]);
        $accessory->checkouts_count = 3;

        $this->assertEquals(70.0, $accessory->percentRemaining());
    }

    public function test_percent_remaining_can_go_negative_when_checked_out_exceeds_quantity()
    {
        $accessory = new Accessory([
            'qty' => 2,
        ]);
        $accessory->checkouts_count = 3;

        $this->assertEquals(-50.0, $accessory->percentRemaining());
    }
}
