<?php

namespace App\Http\Controllers\Components;

use App\Events\CheckoutableCheckedIn;
use App\Events\ComponentCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComponentCheckinController extends Controller
{
    /**
     * Returns a view that allows the checkin of a component from an asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentCheckinController::store() method that stores the data.
     * @since [v4.1.4]
     * @param $component_asset_id
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($component_asset_id)
    {

        // This could probably be done more cleanly but I am very tired. - @snipe
        if ($component_assets = DB::table('components_assets')->find($component_asset_id)) {
            if (is_null($component = Component::find($component_assets->component_id))) {
                return redirect()->route('components.index')->with('error', trans('admin/components/messages.not_found'));
            }
            if (is_null($asset = Asset::find($component_assets->asset_id))) {
                return redirect()->route('components.index')->with('error',
                    trans('admin/components/message.not_found'));
            }
            $this->authorize('checkin', $component);

            return view('components/checkin', compact('component_assets', 'component', 'asset'));
        }

        return redirect()->route('components.index')->with('error', trans('admin/components/messages.not_found'));
    }

    /**
     * Validate and store checkin data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentCheckinController::create() method that returns the form.
     * @since [v4.1.4]
     * @param Request $request
     * @param $component_asset_id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, $component_asset_id, $backto = null)
    {
        if ($component_assets = DB::table('components_assets')->find($component_asset_id)) {
            if (is_null($component = Component::find($component_assets->component_id))) {

                return redirect()->route('components.index')->with('error',
                    trans('admin/components/message.not_found'));
            }

            $this->authorize('checkin', $component);

            $max_to_checkin = $component_assets->assigned_qty;
            $validator = Validator::make($request->all(), [
                'checkin_qty' => "required|numeric|between:1,$max_to_checkin",
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Validation passed, so let's figure out what we have to do here.
            $qty_remaining_in_checkout = ($component_assets->assigned_qty - (int) $request->input('checkin_qty'));

            // We have to modify the record to reflect the new qty that's
            // actually checked out.
            $component_assets->assigned_qty = $qty_remaining_in_checkout;
            DB::table('components_assets')->where('id',
                $component_asset_id)->update(['assigned_qty' => $qty_remaining_in_checkout]);

            // If the checked-in qty is exactly the same as the assigned_qty,
            // we can simply delete the associated components_assets record
            if ($qty_remaining_in_checkout == 0) {
                DB::table('components_assets')->where('id', '=', $component_asset_id)->delete();
            }

            $asset = Asset::find($component_assets->asset_id);

            event(new CheckoutableCheckedIn($component, $asset, auth()->user(), $request->input('note'), Carbon::now()));

            session()->put(['redirect_option' => $request->get('redirect_option')]);

            return Helper::getRedirectOption($request, $component->id, 'Components')
                ->with('success', trans('admin/components/message.checkin.success'));
        }

        return redirect()->route('components.index')->with('error', trans('admin/components/message.does_not_exist'));
    }
}
