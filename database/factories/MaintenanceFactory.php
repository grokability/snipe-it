<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Maintenance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'asset_id' => Asset::factory()->laptopZenbook(),
            'supplier_id' => Supplier::factory(),
            'asset_maintenance_type' => $this->faker->randomElement(['maintenance', 'repair', 'upgrade']),
            'name' => $this->faker->name(),
            'start_date' => $this->faker->date(),
            'is_warranty' => $this->faker->boolean(),
            'notes' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'cost' => $this->faker->randomFloat(),
            'created_by' => User::factory()->superuser(),
            'image' => $this->faker->numberBetween(1,11).'.png',
        ];
    }
}
