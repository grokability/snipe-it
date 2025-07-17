<?php

namespace App\Http\Controllers\Assets;

use App\Events\CheckoutableCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckinRequest;
use App\Http\Traits\MigratesLegacyAssetLocations;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class AssetCheckinController extends Controller
{
    use MigratesLegacyAssetLocations;

    /**
     * Returns a view that presents a form to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @param string $backto
     * @since [v1.0]
     */
    public function create(Asset $asset, $backto = null) : View | RedirectResponse
    {

        $this->authorize('checkin', $asset);

        // This asset is already checked in, redirect
        if (is_null($asset->assignedTo)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
        }

        if (!$asset->model) {
            return redirect()->route('hardware.show', $asset->id)->with('error', trans('admin/hardware/general.model_invalid_fix'));
        }

        // Invoke the validation to see if the audit will complete successfully
        $asset->setRules($asset->getRules() + $asset->customFieldValidationRules());

        if ($asset->isInvalid()) {
            return redirect()->route('hardware.edit', $asset)->withErrors($asset->getErrors());
        }

        $target_option = match ($asset->assigned_type) {
            'App\Models\Asset' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset_previous')]),
            'App\Models\Location' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.location')]),
            default => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
        };
        return view('hardware/checkin', compact('asset', 'target_option'))
            ->with('item', $asset)
            ->with('statusLabel_list', Helper::statusLabelList())
            ->with('backto', $backto)
            ->with('table_name', 'Assets');
    }

    /**
     * Validate and process the form data to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param AssetCheckinRequest $request
     * @param int $assetId
     * @param null $backto
     * @since [v1.0]
     */
    public function store(AssetCheckinRequest $request, $assetId = null, $backto = null) : RedirectResponse
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (is_null($target = $asset->assignedTo)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
        }

        if (!$asset->model) {
            return redirect()->route('hardware.show', $asset->id)->with('error', trans('admin/hardware/general.model_invalid_fix'));
        }

        $this->authorize('checkin', $asset);

        session()->put('checkedInFrom', $asset->assignedTo->id);
        session()->put('checkout_to_type', match ($asset->assigned_type) {
            'App\Models\User' => 'user',
            'App\Models\Location' => 'location',
            'App\Models\Asset' => 'asset',
        });

        $asset->expected_checkin = null;
        $asset->assignedTo()->disassociate($asset);
        $asset->accepted = null;
        $asset->name = $request->get('name');

        if ($request->filled('status_id')) {
            $asset->status_id = e($request->get('status_id'));
        }

        // Add any custom fields that should be included in the checkout
        $asset->customFieldsForCheckinCheckout('display_checkin');

        $this->migrateLegacyLocations($asset);

        $asset->location_id = $asset->rtd_location_id;

        if ($request->filled('location_id')) {
            Log::debug('NEW Location ID: '.$request->get('location_id'));
            $asset->location_id = $request->get('location_id');

            if ($request->get('update_default_location') == 0){
                $asset->rtd_location_id = $request->get('location_id');
            }
        }

        $originalValues = $asset->getRawOriginal();

        // Handle last checkin date
        $checkin_at = date('Y-m-d H:i:s');
        if (($request->filled('checkin_at')) && ($request->get('checkin_at') != date('Y-m-d'))) {
            $originalValues['action_date'] = $checkin_at;
            $checkin_at = $request->get('checkin_at');

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
        $acceptances->map(function($acceptance) {
            $acceptance->delete();
        });

        session()->put('redirect_option', $request->get('redirect_option'));

        // Add any custom fields that should be included in the checkout
        $asset->customFieldsForCheckinCheckout('display_checkin');

        if ($asset->save()) {

            event(new CheckoutableCheckedIn($asset, $target, auth()->user(), $request->input('note'), $checkin_at, $originalValues));
            return Helper::getRedirectOption($request, $asset->id, 'Assets')
                ->with('success', trans('admin/hardware/message.checkin.success'));
        }
        // Redirect to the asset management page with error
        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.error').$asset->getErrors());
    }
}
