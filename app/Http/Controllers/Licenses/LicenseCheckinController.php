<?php

namespace App\Http\Controllers\Licenses;

use App\Events\CheckoutableCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LicenseCheckinController extends Controller
{
    /**
     * Makes the form view to check a license seat back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     *
     * @param  int  $seatId
     * @param  string  $backTo
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create(LicenseSeat $licenseSeat, $backTo = null)
    {
        // Check if the asset exists
        $license = License::find($licenseSeat->license_id);
        $this->authorize('checkin', $license);

        return view('licenses/checkin', compact('licenseSeat'))->with('backto', $backTo);
    }

    /**
     * Validates and stores the license checkin action.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see LicenseCheckinController::create() method that provides the form view
     * @since [v1.0]
     *
     * @param  int  $seatId
     * @param  string  $backTo
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(Request $request, $seatId = null, $backTo = null)
    {
        // Check if the asset exists
        if (is_null($licenseSeat = LicenseSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        $license = License::find($licenseSeat->license_id);

        // LicenseSeat is not assigned, it can't be checked in
        if (is_null($licenseSeat->assigned_to) && is_null($licenseSeat->asset_id)) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkin.error'));
        }

        $this->authorize('checkin', $license);

        // Declare the rules for the form validation
        $rules = [
            'notes' => 'string|nullable',
        ];

        // Create a new validator instance from our validation rules
        $validator = Validator::make($request->all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return redirect()->back()->withInput()->withErrors($validator);
        }

        if ($licenseSeat->assigned_to != null) {
            $return_to = User::withTrashed()->find($licenseSeat->assigned_to);
            if ($return_to) {
                session()->put('checkedInFrom', $return_to->id);
            }
        } else {
            $return_to = Asset::find($licenseSeat->asset_id);
        }

        // Update the asset data
        $licenseSeat->assigned_to = null;
        $licenseSeat->asset_id = null;
        $licenseSeat->notes = $request->input('notes');
        if (! $licenseSeat->license->reassignable) {
            $licenseSeat->unreassignable_seat = true;
        }

        session()->put(['redirect_option' => $request->input('redirect_option')]);
        if ($request->input('redirect_option') === 'target') {
            session()->put(['checkout_to_type' => 'user']);
        }

        // Was the asset updated?
        if ($licenseSeat->save()) {
            event(new CheckoutableCheckedIn($licenseSeat, $return_to, auth()->user(), $licenseSeat->notes));

            return Helper::getRedirectOption($request, $license->id, 'Licenses')
                ->with('success', trans('admin/licenses/message.checkin.success'));
        }

        // Redirect to the license page with error
        return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkin.error'));
    }

    /**
     * Bulk checkin all license seats
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see LicenseCheckinController::create() method that provides the form view
     * @since [v6.1.1]
     *
     * @throws AuthorizationException
     */
    public function bulkCheckinSelected(Request $request): RedirectResponse
    {
        $this->authorize('checkin', License::class);

        $seatIds = $request->input('ids', []);

        if (empty($seatIds)) {
            return redirect()->back()->with('warning', trans('admin/licenses/general.bulk.checkin_selected.no_seats_selected'));
        }

        $seats = LicenseSeat::whereIn('id', $seatIds)
            ->where(function ($query) {
                $query->whereNotNull('assigned_to')->orWhereNotNull('asset_id');
            })
            ->with('license', 'user', 'asset')
            ->get();

        $count = 0;
        foreach ($seats as $seat) {
            if (! $seat->license || ! Gate::allows('checkin', $seat->license)) {
                continue;
            }
            $target = $seat->user ?? $seat->asset;
            $seat->assigned_to = null;
            $seat->asset_id = null;
            if (! $seat->license->reassignable) {
                $seat->unreassignable_seat = true;
            }
            if ($seat->save()) {
                event(new CheckoutableCheckedIn($seat, $target, auth()->user(), null));
                $count++;
            }
        }

        return redirect()->back()->with('success', trans_choice('admin/licenses/general.bulk.checkin_selected.success', $count, ['count' => $count]));
    }

    public function bulkCheckin(Request $request, $licenseId)
    {

        $license = License::findOrFail($licenseId);
        $this->authorize('checkin', $license);

        $licenseSeatsByUser = LicenseSeat::where('license_id', '=', $licenseId)
            ->whereNotNull('assigned_to')
            ->with('user', 'license')
            ->get();

        $license = $licenseSeatsByUser->first()?->license;
        foreach ($licenseSeatsByUser as $user_seat) {
            $user_seat->assigned_to = null;
            if ($license && ! $license->reassignable) {
                $user_seat->unreassignable_seat = true;
            }
            if ($user_seat->save()) {
                Log::debug('Checking in '.$license->name.' from user '.$user_seat->username);
                $user_seat->logCheckin($user_seat->user, trans('admin/licenses/general.bulk.checkin_all.log_msg'));
            }
        }

        $licenseSeatsByAsset = LicenseSeat::where('license_id', '=', $licenseId)
            ->whereNotNull('asset_id')
            ->with('asset')
            ->get();

        $count = 0;
        $license = $licenseSeatsByAsset->first()?->license;
        foreach ($licenseSeatsByAsset as $asset_seat) {
            $asset_seat->asset_id = null;
            if ($license && ! $license->reassignable) {
                $asset_seat->unreassignable_seat = true;
            }
            if ($asset_seat->save()) {
                Log::debug('Checking in '.$license->name.' from asset '.$asset_seat->asset_tag);
                $asset_seat->logCheckin($asset_seat->asset, trans('admin/licenses/general.bulk.checkin_all.log_msg'));
                $count++;
            }
        }

        return redirect()->back()->with('success', trans_choice('admin/licenses/general.bulk.checkin_all.success', 2, ['count' => $count]));

    }
}
