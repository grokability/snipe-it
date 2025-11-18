<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'address2' => $this->faker->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'country' => $this->faker->countryCode(),
            'currency' => $this->faker->currencyCode(),
            'zip' => $this->faker->postcode(),
            'image' => rand(1, 9).'.jpg',
            'notes'   => 'Created by DB seeder',
            'tag_color' => $this->faker->hexColor(),
        ];
    }
  
    // one of these can eventuall go away - left temporarily for conflict resolution
    public function deleted(): self
    {
        return $this->state(['deleted_at' => $this->faker->dateTime()]);
    }
  
    public function deletedLocation()
    {
        return $this->state(function () {
            return [
                'deleted_at' => $this->faker->dateTime(),
            ];
        });
    }
}
