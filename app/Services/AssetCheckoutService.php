<?php

namespace App\Services;

use App\Events\CheckoutableCheckedIn;
use App\Exceptions\CheckoutNotAllowed;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
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
            // User target: use their current location (may be null).
            $asset->location_id = $target->location_id;
        }

        if ($target instanceof User && $asset->licenseseats->isNotEmpty()) {
            $this->propagateLicenseSeats($asset, $target, $actor);
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

    /**
     * GHSA-9wq4-gp32-rcgj: when an asset's attached license seats follow it
     * to a new user, every seat must pass the same guards the license
     * checkout surfaces enforce (unreassignable_seat reject, occupied-seat
     * on non-reassignable license reject, CheckoutableCheckedIn event and
     * pending-acceptance cleanup for displaced holders).
     */
    private function propagateLicenseSeats(Asset $asset, User $target, User $actor): void
    {
        foreach ($asset->licenseseats as $seat) {
            if ($seat->unreassignable_seat) {
                throw new CheckoutNotAllowed(trans('admin/licenses/message.checkout.unavailable'));
            }

            $isOccupiedByOther = $seat->assigned_to !== null && (int) $seat->assigned_to !== (int) $target->id;
            if ($isOccupiedByOther && !$seat->license->reassignable) {
                throw new CheckoutNotAllowed(trans('admin/licenses/message.checkout.unavailable'));
            }
        }

        foreach ($asset->licenseseats as $seat) {
            $previousHolderId = $seat->assigned_to;

            if ($previousHolderId !== null && (int) $previousHolderId !== (int) $target->id) {
                $displaced = User::withoutGlobalScopes()->find($previousHolderId);
                if ($displaced) {
                    CheckoutAcceptance::pending()
                        ->where('checkoutable_type', LicenseSeat::class)
                        ->where('checkoutable_id', $seat->id)
                        ->where('assigned_to_id', $displaced->id)
                        ->get()
                        ->each(fn($a) => $a->delete());

                    event(new CheckoutableCheckedIn($seat, $displaced, $actor, null));
                }
            }

            $seat->assigned_to = $target->id;
            $seat->save();
        }
    }
}
