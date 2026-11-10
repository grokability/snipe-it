<?php

namespace App\Services;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class AssetCheckinService
{
    public function checkin(
        Asset $asset,
        User $actor,
        ?string $note = null,
        ?string $checkinAt = null,
        ?int $locationId = null,
        bool $updateDefaultLocation = false,
    ): bool {
        $target = $asset->assignedTo;

        $checkinAt = $checkinAt ?? date('Y-m-d H:i:s');
        $originalValues = $asset->getRawOriginal();

        // Signal to the activity log that the action date differs from today
        if (substr($checkinAt, 0, 10) !== date('Y-m-d')) {
            $originalValues['action_date'] = date('Y-m-d H:i:s');
        }

        $asset->expected_checkin = null;
        $asset->last_checkin = $checkinAt;
        $asset->assignedTo()->disassociate($asset);
        $asset->accepted = null;

        // Fix legacy zero location IDs
        if ($asset->rtd_location_id == '0') {
            Log::debug('Manually override the RTD location ID on checkin');
            $asset->rtd_location_id = '';
        }
        if ($asset->location_id == '0') {
            Log::debug('Manually override the location ID on checkin');
            $asset->location_id = '';
        }

        $asset->location_id = $asset->rtd_location_id;

        if ($locationId !== null) {
            $asset->location_id = $locationId;
            if ($updateDefaultLocation) {
                $asset->rtd_location_id = $locationId;
            }
        }

        $asset->licenseseats->each(fn ($seat) => $seat->update(['assigned_to' => null]));

        CheckoutAcceptance::pending()
            ->whereHasMorph('checkoutable', [Asset::class], fn (Builder $q) => $q->where('id', $asset->id))
            ->get()
            ->each(fn ($acceptance) => $acceptance->delete());

        if ($asset->save()) {
            Asset::where('assigned_type', Asset::class)
                ->where('assigned_to', $asset->id)
                ->update(['location_id' => $asset->location_id]);

            event(new CheckoutableCheckedIn($asset, $target, $actor, $note, $checkinAt, $originalValues));

            return true;
        }

        return false;
    }
}
