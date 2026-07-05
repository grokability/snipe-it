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
use App\Models\Location;
use App\Models\Statuslabel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class AssetCheckinController extends Controller
{
    use MigratesLegacyAssetLocations;

    /**
     * Returns a view that presents a form to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $assetId
     * @param  string  $backto
     *
     * @since [v1.0]
     */
    public function create(Asset $asset, $backto = null): View|RedirectResponse
    {

        $this->authorize('checkin', $asset);

        // This asset is already checked in, redirect
        if (is_null($asset->assignedTo)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
        }

        if (! $asset->model) {
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

        $deployableStatusIds = array_map('intval', array_keys(Helper::deployableStatusLabelList()));
        $selectedStatusId = old('status_id');
        $showRequestableToggle = is_numeric($selectedStatusId)
            && in_array((int) $selectedStatusId, $deployableStatusIds, true);

        return view('hardware/checkin', compact('asset', 'target_option'))
            ->with('item', $asset)
            ->with('statusLabel_list', Helper::statusLabelList())
            ->with('deployable_status_ids', $deployableStatusIds)
            ->with('show_requestable_toggle', $showRequestableToggle)
            ->with('backto', $backto)
            ->with('table_name', 'Assets');
    }

    /**
     * Validate and process the form data to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $assetId
     * @param  null  $backto
     *
     * @since [v1.0]
     */
    public function store(AssetCheckinRequest $request, $assetId = null, $backto = null): RedirectResponse
    {
        // Check if the asset exists
        if (is_null($asset = Asset::withTrashed()->find($assetId))) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (is_null($target = $asset->assignedTo)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
        }

        if (! $asset->model) {
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
        $asset->name = $request->input('name');

        if ($request->filled('status_id')) {
            $asset->status_id = e($request->input('status_id'));
        }

        $selectedStatusId = $request->filled('status_id')
            ? (int) $request->input('status_id')
            : (int) $asset->status_id;

        $isDeployableStatus = Statuslabel::query()
            ->whereKey($selectedStatusId)
            ->where('deployable', 1)
            ->exists();

        if ($request->boolean('set_requestable') && $isDeployableStatus) {
            $asset->requestable = true;
        }

        // Add any custom fields that should be included in the checkout
        $asset->customFieldsForCheckinCheckout('display_checkin');

        $this->migrateLegacyLocations($asset);

        $asset->location_id = $asset->rtd_location_id;

        if ($request->has('location_id')) {
            if ($request->filled('location_id')) {
                // Resolve via the scoped Location query so non-existent IDs and IDs the actor
                // cannot see under FMCS are rejected before we write them to the asset.
                $submittedLocation = Location::find($request->input('location_id'));

                if (! $submittedLocation) {
                    return redirect()->back()->withInput()
                        ->with('error', trans('admin/hardware/message.create.target_not_found.location'));
                }

                Log::debug('NEW Location ID: '.$submittedLocation->id);
                $asset->location_id = $submittedLocation->id;
                if ($request->input('update_default_location') == 0) {
                    $asset->rtd_location_id = $submittedLocation->id;
                }
            } else {
                // Explicitly submitted as empty — clear the location
                $asset->location_id = null;
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

        if ($asset->save()) {
            // Update the location of any child assets
            Asset::where('assigned_type', Asset::class)
                ->where('assigned_to', $asset->id)
                ->update(['location_id' => $asset->location_id]);

            event(new CheckoutableCheckedIn($asset, $target, auth()->user(), $request->input('note'), $checkin_at, $originalValues));

            return Helper::getRedirectOption($request, $asset->id, 'Assets')
                ->with('success', trans('admin/hardware/message.checkin.success'));
        }

        // Redirect to the asset management page with error
        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.error').$asset->getErrors());
    }

    /**
     * This would only be used if the target is actually hard-deleted
     * and literally does not exist in the database anymore. This will null out the assigned_to
     * and assigned_type fields, but will not trigger any events or do any of the other things that a
     * normal checkin would do, since the target itself is now invalid.
     */
    public function forceCheckin(Asset $asset)
    {

        $this->authorize('checkin', $asset);

        if (! $asset->hasOrphanedAssignment()) {
            return redirect()->route('hardware.show', $asset->id)
                ->with('error', trans('admin/hardware/message.checkin.force_checkin_not_orphaned'));
        }

        $asset->assigned_to = null;
        $asset->assigned_type = null;

        if ($asset->save()) {
            $asset->logForceCheckin();

            return redirect()->route('hardware.show', $asset->id)
                ->with('success', trans('admin/hardware/message.checkin.force_checkin_orphaned_success'));
        }

        return redirect()->route('hardware.show', $asset->id)
            ->with('error', trans('admin/hardware/message.checkin.force_checkin_error'));
    }
}
