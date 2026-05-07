<?php

namespace Database\Factories;

use App\Models\PredefinedKit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PredefinedKit>
 */
class PredefinedKitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
        ];
    }
}
