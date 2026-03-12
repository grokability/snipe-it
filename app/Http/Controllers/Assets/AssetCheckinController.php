<?php

namespace App\Http\Controllers\Assets;

use App\Actions\Assets\AssetCheckinAction;
use App\Events\CheckoutableCheckedIn;
use App\Exceptions\AssetModelUnknown;
use App\Exceptions\AssetsCheckedInAlready;
use App\Exceptions\AssetsDoNotExist;
use App\Exceptions\NoAssetsSelected;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckinRequest;
use App\Http\Traits\MigratesLegacyAssetLocations;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
        $assetIds = $request->input('asset_ids') ?? $assetId;
        $assetIds = Arr::wrap($assetIds);
        $assetIds = array_values(array_unique(array_filter($assetIds)));

        if (empty($assetIds)) {
            return redirect()->route('hardware.index')->with('error', 'No assets selected.');
        }
        $assets = Asset::query()
            ->whereIn('id', $assetIds)
            ->with(['assignedTo', 'model'])
            ->get()
            ->keyBy('id');

        $missingIds = array_values(array_diff($assetIds, $assets->keys()->all()));
        if (!empty($missingIds)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }
        //have to validate assignedTo here for the redirect
        $isCheckedIn = $assets->filter(fn($asset) => $asset->assignedTo == null)->keys()->all();
        if (empty($isCheckedIn)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
        }

        foreach ($assets as $asset) {
            $this->authorize('checkin', $asset);
        }
        //stores the session variables before disassociating
        if (count($assetIds) == 1) {
            $asset = $assets->get($assetIds[0]);
            $checkedInFromId = $asset->assigned_to;
            $checkedInFromType = $asset->assigned_type;

            session()->put('checkedInFrom', $checkedInFromId);
            session()->put('checkout_to_type', match ($checkedInFromType) {
                'App\Models\User' => 'user',
                'App\Models\Location' => 'location',
                'App\Models\Asset' => 'asset',
            });
        }

        try {
            foreach ($assets as $asset) {
                AssetCheckinAction::run($request, $asset);
            }
        } catch (\Exception $e) {
            \Log::error('Asset checkin failed', [
                'asset_id' => $asset->id ?? null,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.error') . $e->getMessage());
        }
        if (count($assetIds) == 1) {
            return Helper::getRedirectOption($request, $assetIds[0], 'Assets')
                ->with('success', trans('admin/hardware/message.checkin.success'));
        }

        return redirect()->route('hardware.index')->with('success', trans('admin/hardware/message.checkin.success'));
    }
}
