<?php

namespace Database\Factories;

use App\Models\Depreciation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepreciationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Depreciation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_by' => User::factory()->superuser(),
            'months' => 36,
            'name' => $this->faker->unique()->catchPhrase(),
        ];
    }

    public function computer()
    {
        return $this->state(function () {
            return [
                'name' => 'Computer Depreciation',
                'months' => 36,
            ];
        });
    }

    public function display()
    {
        return $this->state(function () {
            return [
                'name' => 'Display Depreciation',
                'months' => 12,
            ];
        });
    }

    public function mobilePhones()
    {
        return $this->state(function () {
            return [
                'name' => 'Mobile Phone Depreciation',
                'months' => 24,
            ];
        });
    }
}
