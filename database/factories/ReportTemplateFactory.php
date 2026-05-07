<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'options' => [
                'id' => '1',
            ],
            'created_by' => User::factory(),
            'is_shared' => 0,
        ];
    }

    public function shared()
    {
        return $this->state(function () {
            return ['is_shared' => 1];
        });
    }

    public function notShared()
    {
        return $this->state(function () {
            return ['is_shared' => 0];
        });
    }
}
