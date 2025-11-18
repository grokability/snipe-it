<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->streetAddress(),
            'address2' => $this->faker->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip' => $this->faker->postCode(),
            'country' => $this->faker->countryCode(),
            'contact' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'fax'   => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'url'   => $this->faker->url(),
            'notes' => $this->faker->text(191), // Supplier notes can be a max of 255 characters.
            'tag_color' => $this->faker->hexColor(),
        ];
    }
}
