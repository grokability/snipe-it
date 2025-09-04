<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManufacturerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Manufacturer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company(),
            'created_by' => User::factory()->superuser(),
            'support_phone' => $this->faker->phoneNumber(),
            'url' => $this->faker->url(),
            'support_email' => $this->faker->safeEmail(),
            'notes'   => 'Created by DB seeder',
        ];
    }

    public function apple()
    {
        return $this->state(function () {
            return [
                'name' => 'Apple',
                'url' => 'https://apple.com',
                'support_url' => 'https://support.apple.com',
                'warranty_lookup_url' => 'https://checkcoverage.apple.com',
                'image' => 'apple.jpg',
            ];
        });
    }

    public function microsoft()
    {
        return $this->state(function () {
            return [
                'name' => 'Microsoft',
                'url' => 'https://microsoft.com',
                'support_url' => 'https://support.microsoft.com',
                'warranty_lookup_url' => 'https://account.microsoft.com/devices',
                'image' => 'microsoft.png',
            ];
        });
    }

    public function dell()
    {
        return $this->state(function () {
            return [
                'name' => 'Dell',
                'url' => 'https://dell.com',
                'support_url' => 'https://support.dell.com',
                'warranty_lookup_url' => 'https://www.dell.com/support/home/en-us/Products/?app=warranty',
                'image' => 'dell.png',
            ];
        });
    }

    public function asus()
    {
        return $this->state(function () {
            return [
                'name' => 'Asus',
                'url' => 'https://asus.com',
                'support_url' => 'https://support.asus.com',
                'image' => 'asus.png',
            ];
        });
    }

    public function hp()
    {
        return $this->state(function () {
            return [
                'name' => 'HP',
                'url' => 'https://hp.com',
                'support_url' => 'https://support.hp.com',
                'image' => 'hp.png',
            ];
        });
    }

    public function lenovo()
    {
        return $this->state(function () {
            return [
                'name' => 'Lenovo',
                'url' => 'https://lenovo.com',
                'support_url' => 'https://support.lenovo.com',
                'image' => 'lenovo.jpg',
            ];
        });
    }

    public function lg()
    {
        return $this->state(function () {
            return [
                'name' => 'LG',
                'url' => 'https://lg.com',
                'support_url' => 'https://support.lg.com',
                'image' => 'lg.jpg',
            ];
        });
    }

    public function polycom()
    {
        return $this->state(function () {
            return [
                'name' => 'Polycom',
                'url' => 'https://polycom.com',
                'support_url' => 'https://support.polycom.com',
                'image' => 'polycom.png',
            ];
        });
    }

    public function adobe()
    {
        return $this->state(function () {
            return [
                'name' => 'Adobe',
                'url' => 'https://adobe.com',
                'support_url' => 'https://support.adobe.com',
                'image' => 'adobe.jpg',
            ];
        });
    }

    public function avery()
    {
        return $this->state(function () {
            return [
                'name' => 'Avery',
                'url' => 'https://avery.com',
                'support_url' => 'https://support.avery.com',
                'image' => 'avery.png',
            ];
        });
    }

    public function crucial()
    {
        return $this->state(function () {
            return [
                'name' => 'Crucial',
                'url' => 'https://crucial.com',
                'support_url' => 'https://support.crucial.com',
                'image' => 'crucial.jpg',
            ];
        });
    }

    public function samsung()
    {
        return $this->state(function () {
            return [
                'name' => 'Samsung',
                'url' => 'https://www.samsung.com',
                'support_url' => 'https://www.samsung.com/support/',
                'image' => 'samsung.png',
            ];
        });
    }

    public function google()
    {
        return $this->state(function () {
            return [
                'name' => 'Google',
                'url' => 'https://www.google.com',
                'image' => 'google.webp',
            ];
        });
    }

    public function huawei()
    {
        return $this->state(function () {
            return [
                'name' => 'Huawei',
                'url' => 'https://consumer.huawei.com/',
                'image' => 'huawei.webp',
            ];
        });
    }

    public function sony()
    {
        return $this->state(function () {
            return [
                'name' => 'Sony',
                'url' => 'https://electronics.sony.com',
                'image' => 'sony.png',
            ];
        });
    }
}
