<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckoutRequestFactory extends Factory
{
    protected $model = CheckoutRequest::class;

    public function definition(): array
    {
        return [
            'requestable_id' => Asset::factory(),
            'requestable_type' => Asset::class,
            'quantity' => 1,
            'user_id' => User::factory(),
        ];
    }

    public function forAsset()
    {
        return $this->state(function (array $attributes) {
            return [
                'requestable_id' => Asset::factory(),
                'requestable_type' => Asset::class,
            ];
        });
    }

    public function forAssetModel()
    {
        return $this->state(function (array $attributes) {
            return [
                'requestable_id' => AssetModel::factory(),
                'requestable_type' => AssetModel::class,
            ];
        });
    }
}
