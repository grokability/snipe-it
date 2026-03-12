<?php

namespace App\Actions\Assets;

use App\Actions\BaseAction;
use App\Events\CheckoutableCheckedIn;
use App\Exceptions\AssetModelUnknown;
use App\Exceptions\AssetsCheckedInAlready;
use App\Http\Requests\AssetCheckinRequest;
use App\Http\Traits\MigratesLegacyAssetLocations;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class AssetCheckinAction extends BaseAction
{
    use MigratesLegacyAssetLocations;

    protected function handle(AssetCheckinRequest $request, $asset): bool
    {
        $missingModel = $asset->model;
        if (!empty($missingModel)) {
            throw new AssetModelUnknown($missingModel);
        }
        $target = $asset->assignedTo;
        $asset->expected_checkin = null;
        $asset->assignedTo()->disassociate($asset);
        $asset->accepted = null;
        $asset->name = $request->input('name');

        if ($request->filled('status_id')) {
            $asset->status_id = e($request->input('status_id'));
        }

        // Add any custom fields that should be included in the checkout
        $asset->customFieldsForCheckinCheckout('display_checkin');
        $this->migrateLegacyLocations($asset);

        $asset->location_id = $asset->rtd_location_id;

        if ($request->filled('location_id')) {
            Log::debug('NEW Location ID: ' . $request->input('location_id'));
            $asset->location_id = $request->input('location_id');

            if ($request->input('update_default_location') == 0) {
                $asset->rtd_location_id = $request->input('location_id');
            }
        }

        $originalValues = $asset->getRawOriginal();

        // Handle last checkin date
        $checkin_at = date('Y-m-d H:i:s');
        if (($request->filled('checkin_at')) && ($request->input('checkin_at') != date('Y-m-d'))) {
            $originalValues['action_date'] = $checkin_at;
            $checkin_at = $request->input('checkin_at');

        }
        $asset->last_checkin = $checkin_at;

        $asset->licenseseats->each(function (LicenseSeat $seat) {
            $seat->update(['assigned_to' => null]);
        });

        // Get all pending Acceptances for this asset and delete them
        $acceptances = CheckoutAcceptance::pending()->whereHasMorph('checkoutable',
            [Asset::class],
            function (Builder $query) use ($asset) {
                $query->where('id', $asset->id);
            })->get();
        $acceptances->map(function ($acceptance) {
            $acceptance->delete();
        });

        session()->put('redirect_option', $request->input('redirect_option'));

        // Add any custom fields that should be included in the checkout
        $asset->customFieldsForCheckinCheckout('display_checkin');

        $asset->save();

        event(new CheckoutableCheckedIn($asset, $target, auth()->user(), $request->input('note'), $checkin_at, $originalValues));
        return true;
    }
}
