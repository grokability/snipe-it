<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseSeatFactory extends Factory
{
    public function definition()
    {
        return [
            'license_id' => License::factory(),
            'unreassignable_seat' => false,
        ];
    }

    public function assignedToAsset(Asset $asset = null)
    {
        return $this->state(function () use ($asset) {
            return [
                'asset_id' => $asset->id ?? Asset::factory(),
            ];
        });
    }

    public function assignedToUser(User $user = null)
    {
        return $this->state(function () use ($user) {
            return [
                'assigned_to' => $user->id ?? User::factory(),
            ];
        });
    }

    public function reassignable()
    {
        return $this->afterMaking(function (LicenseSeat $seat) {
            $seat->license->update(['reassignable' => true]);
        });
    }

    public function notReassignable()
    {
        return $this->afterMaking(function (LicenseSeat $seat) {
            $seat->license->update(['reassignable' => false]);
        });
    }
}
