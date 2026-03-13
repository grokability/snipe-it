<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'per_page' => 20,
            'site_name' => $this->faker->sentence(),
            'auto_increment_assets' => false,
            'alert_email' => $this->faker->safeEmail(),
            'alerts_enabled' => true,
            'brand' => 1,
            'default_currency' => $this->faker->currencyCode(),
            'locale' => 'en-US',
            'pwd_secure_min' => 10, // Match web setup
            'email_domain' => 'example.org',
        ];
    }
}
