<?php
namespace Database\Factories;

use App\Models\Category;
use App\Models\License;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = License::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_by' => User::factory()->superuser(),
            'name' => $this->faker->name(),
            'license_email' => $this->faker->safeEmail(),
            'serial' => $this->faker->uuid(),
            'notes'   => 'Created by DB seeder',
            'seats' => $this->faker->numberBetween(1, 10),
            'purchase_date' => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get())->format('Y-m-d'),
            'order_number' => $this->faker->numberBetween(1000000, 50000000),
            'expiration_date' => null,
            'reassignable' => $this->faker->boolean(),
            'termination_date' => null,
            'supplier_id' => Supplier::factory(),
            'category_id' => Category::factory(),
        ];
    }

    public function photoshop()
    {
        return $this->state(function () {
            $data = [
                'name' => 'Photoshop',
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Adobe')->first() ?? Manufacturer::factory()->adobe();
                },
                'purchase_cost' => '299.99',
                'seats' => 10,
                'purchase_order' => '13503Q',
                'maintained' => true,
                'category_id' => function () {
                    return Category::where('name', 'Graphics Software')->first() ?? Category::factory()->licenseGraphicsCategory();
                },
            ];

            return $data;
        });
    }

    public function acrobat()
    {
        return $this->state(function () {
            $data = [
                'name' => 'Acrobat',
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Adobe')->first() ?? Manufacturer::factory()->adobe();
                },
                'purchase_cost' => '29.99',
                'seats' => 10,
                'category_id' => function () {
                    return Category::where('name', 'Graphics Software')->first() ?? Category::factory()->licenseGraphicsCategory();
                },
            ];

            return $data;
        });
    }

    public function indesign()
    {
        return $this->state(function () {
            $data = [
                'name' => 'InDesign',
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Adobe')->first() ?? Manufacturer::factory()->adobe();
                },
                'purchase_cost' => '199.99',
                'seats' => 10,
                'category_id' => function () {
                    return Category::where('name', 'Graphics Software')->first() ?? Category::factory()->licenseGraphicsCategory();
                },
            ];
    

            return $data;
        });
    }

    public function office()
    {
        return $this->state(function () {
            $data = [
                'name' => 'Office',
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Microsoft')->first() ?? Manufacturer::factory()->microsoft();
                },
                'purchase_cost' => '49.99',
                'seats' => 20,
                'category_id' => function () {
                    return Category::where('name', 'Office Software')->first() ?? Category::factory()->licenseOfficeCategory();
                },
            ];

            return $data;
        });
    }
}
