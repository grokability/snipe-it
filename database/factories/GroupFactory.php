<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Group::class;

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
            'notes'   => 'Created by DB seeder',
            'permissions' => json_encode([]),
        ];
    }
}
