<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessoryCheckoutFactory extends Factory
{
    protected $model = AccessoryCheckout::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'accessory_id' => Accessory::factory(),
            'assigned_to' => User::factory(),
            'assigned_type' => User::class,
        ];
    }
}
