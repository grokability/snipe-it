<?php
namespace Tests\Unit;

use App\Models\Accessory;
use App\Models\Manufacturer;
use App\Models\Location;
use App\Models\Category;
use App\Models\Company;
use Tests\TestCase;

class AccessoryTest extends TestCase
{
    public function testAnAccessoryBelongsToACompany()
    {
        $accessory = Accessory::factory()
        ->create(
            [
                'company_id' => 
                    Company::factory()->create()->id]);
        $this->assertInstanceOf(Company::class, $accessory->company);
    }

    public function testAnAccessoryHasALocation()
    {
        $accessory = Accessory::factory()
            ->create(
                [
                    'location_id' => Location::factory()->create()->id
                ]);
        $this->assertInstanceOf(Location::class, $accessory->location);
    }

    public function testAnAccessoryBelongsToACategory()
    {
        $accessory = Accessory::factory()->appleBtKeyboard()
            ->create(
                [
                    'category_id' => 
                        Category::factory()->create(
                            [
                                'category_type' => 'accessory'
                            ]
                )->id]);
        $this->assertInstanceOf(Category::class, $accessory->category);
        $this->assertEquals('accessory', $accessory->category->category_type);
    }

    public function testAnAccessoryHasAManufacturer()
    {
        $accessory = Accessory::factory()->appleBtKeyboard()->create(
            [
                'category_id' => Category::factory()->create(),
                'manufacturer_id' => Manufacturer::factory()->apple()->create()
            ]);
        $this->assertInstanceOf(Manufacturer::class, $accessory->manufacturer);
    }
}
