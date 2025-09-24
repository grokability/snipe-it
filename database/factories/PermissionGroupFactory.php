<?php

namespace Database\Factories;

use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionGroupFactory extends Factory
{
    protected $model = PermissionGroup::class;

    public function definition()
    {
        return [
            'name'        => $this->faker->unique()->words(2, true),
            'permissions' => json_encode([]), 
            'created_by'  => null, 
            'notes'       => $this->faker->optional()->sentence(),
        ];
    }
}
