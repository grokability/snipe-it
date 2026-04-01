<?php

namespace Database\Factories;

use App\Models\Printable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Printable>
 */
class PrintableFactory extends Factory
{
    protected $model = Printable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'       => $this->faker->sentence(3),
            'content'    => '<p><strong>Asset Tag:</strong> {asset_tag}</p>'
                . '<p><strong>Model:</strong> {model_name}</p>'
                . '<p><strong>Serial:</strong> {serial}</p>',
            'created_by' => User::factory()->superuser(),
        ];
    }
}
