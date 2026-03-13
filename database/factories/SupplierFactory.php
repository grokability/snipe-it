<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
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
            'address' => $this->faker->streetAddress(),
            'address2' => $this->faker->secondaryAddress(),
            'city' => $this->faker->city(),
            'contact' => $this->faker->name(),
            'country' => $this->faker->countryCode(),
            'created_by' => User::factory()->superuser(),
            'email' => $this->faker->safeEmail(),
            'fax'   => $this->faker->phoneNumber(),
            'name' => $this->faker->company(),
            'notes' => $this->faker->text(191), // Supplier notes can be a max of 255 characters.
            'phone' => $this->faker->phoneNumber(),
            'state' => $this->faker->stateAbbr(),
            'tag_color' => $this->faker->hexColor(),
            'url'   => $this->faker->url(),
            'zip' => $this->faker->postCode(),
        ];
    }
}
