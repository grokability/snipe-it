<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_by' => User::factory()->superuser(),
            'location_id' => Location::factory(),
            'name' => $this->faker->unique()->word() . ' Department',
            'notes'   => 'Created by DB seeder',
            'tag_color' => $this->faker->hexColor(),
        ];
    }

    public function hr()
    {
        return $this->state(function () {
            return [
                'name' => 'Human Resources',
            ];
        });
    }

    public function engineering()
    {
        return $this->state(function () {
            return [
                'name' => 'Engineering',
            ];
        });
    }

    public function marketing()
    {
        return $this->state(function () {
            return [
                'name' => 'Marketing',
            ];
        });
    }

    public function client()
    {
        return $this->state(function () {
            return [
                'name' => 'Client Services',
            ];
        });
    }

    public function design()
    {
        return $this->state(function () {
            return [
                'name' => 'Graphic Design',
            ];
        });
    }

    public function product()
    {
        return $this->state(function () {
            return [
                'name' => 'Product Management',
            ];
        });
    }

    public function silly()
    {
        return $this->state(function () {
            return [
                'name' => 'Dept of Silly Walks',
            ];
        });
    }
}
