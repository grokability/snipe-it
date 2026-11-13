<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;

class AssetCheckoutService
{
    public function checkout(
        Asset $asset,
        User|Location|Asset $target,
        User $actor,
        ?string $checkoutAt = null,
        ?string $expectedCheckin = null,
        ?string $note = null,
        ?string $name = null,
        bool $signInPlace = false,
    ): bool {
        if ($target instanceof Location) {
            $asset->location_id = $target->id;
        } elseif ($target instanceof Asset) {
            // Prefer current location, fall back to RTD (home) location
            $asset->location_id = $target->location_id ?: $target->rtd_location_id;
        } else {
            // User — use their current location (may be null)
            $asset->location_id = $target->location_id;
        }

        if ($target instanceof User && $asset->licenseseats->isNotEmpty()) {
            $asset->licenseseats->each(fn ($seat) => $seat->update(['assigned_to' => $target->id]));
        }

        return $asset->checkOut(
            $target,
            $actor,
            $checkoutAt ?? date('Y-m-d H:i:s'),
            $expectedCheckin,
            $note,
            $name ?? $asset->name,
            $asset->location_id,
            $signInPlace,
        );
    }
}
