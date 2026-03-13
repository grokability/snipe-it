<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Manufacturer;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;

class ComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Component::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'category_id' => Category::factory(),
            'company_id' => Company::factory(),
            'created_by' => User::factory()->superuser(),
            'location_id' => Location::factory(),
            'min_amt' => $this->faker->numberBetween($min = 1, $max = 2),
            'model_number' => $this->faker->numberBetween(1000000, 50000000),
            'name' => $this->faker->text(20),
            'order_number' => $this->faker->numberBetween(1000000, 50000000),
            'purchase_cost' => $this->faker->randomFloat(2),
            'purchase_date' => $this->faker->dateTime()->format('Y-m-d'),
            'qty' => $this->faker->numberBetween(3, 10),
            'serial'   => $this->faker->uuid(),
            'supplier_id' => Supplier::factory(),
        ];
    }

    public function ramCrucial4()
    {
        $manufacturer = Manufacturer::where('name', 'Crucial')->first() ?? Manufacturer::factory()->create(['name' => 'Crucial']);
        return $this->state(function () use ($manufacturer) {
            return [
                'name' => 'Crucial 4GB DDR3L-1600 SODIMM',
                'category_id' => function () {
                    return Category::where('name', 'RAM')->first() ?? Category::factory()->componentRamCategory();
                },
                'qty' => 10,
                'min_amt' => 2,
                'manufacturer_id' => $manufacturer->id,
                'location_id' => Location::factory(),
            ];
        });
    }

    public function ramCrucial8()
    {
        $manufacturer = Manufacturer::where('name', 'Crucial')->first() ?? Manufacturer::factory()->create(['name' => 'Crucial']);
        return $this->state(function () use ($manufacturer) {
            return [
                'name' => 'Crucial 8GB DDR3L-1600 SODIMM Memory for Mac',
                'category_id' => function () {
                    return Category::where('name', 'RAM')->first() ?? Category::factory()->componentRamCategory();
                },
                'qty' => 10,
                'min_amt' => 2,
                'manufacturer_id' => $manufacturer->id,
            ];
        });
    }

    public function ssdCrucial120()
    {
        $manufacturer = Manufacturer::where('name', 'Crucial')->first() ?? Manufacturer::factory()->create(['name' => 'Crucial']);
        return $this->state(function () use ($manufacturer) {
            return [
                'name' => 'Crucial BX300 120GB SATA Internal SSD',
                'category_id' => function () {
                    return Category::where('name', 'HDD/SSD')->first() ?? Category::factory()->componentHddCategory();
                },
                'qty' => 10,
                'min_amt' => 2,
                'manufacturer_id' => $manufacturer->id,
            ];
        });
    }

    public function ssdCrucial240()
    {
        $manufacturer = Manufacturer::where('name', 'Crucial')->first() ?? Manufacturer::factory()->create(['name' => 'Crucial']);
        return $this->state(function () use ($manufacturer) {
            return [
                'name' => 'Crucial BX300 240GB SATA Internal SSD',
                'category_id' => function () {
                    return Category::where('name', 'HDD/SSD')->first() ?? Category::factory()->componentHddCategory();
                },
                'qty' => 10,
                'min_amt' => 2,
                'manufacturer_id' => $manufacturer->id,
            ];
        });
    }

    public function checkedOutToAsset(Asset $asset = null)
    {
        return $this->afterCreating(function (Component $component) use ($asset) {
            $component->assets()->attach($component->id, [
                'component_id' => $component->id,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'asset_id' => $asset->id ?? Asset::factory()->create()->id,
            ]);
        });
    }

}
