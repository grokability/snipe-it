<?php

namespace App\Http\Controllers\Licenses;

use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LicenseCheckoutRequest;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LicenseCheckoutController extends Controller
{
    /**
     * Provides the form view for checking out a license to a user.
     * Here we pass the license seat ID instead of the license ID,
     * because licenses themselves are never checked out to anyone,
     * only the seats associated with them.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param $id
     * @return \Illuminate\Contracts\View\View |\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(License $license)
    {
        $this->authorize('checkout', $license);

        if ($license->category) {

            // Make sure there is at least one available to checkout
            if ($license->availCount()->count() < 1) {
                return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.not_enough_seats'));
            }

            // Make sure the license is expired or terminated
            if ($license->isInactive()) {
                return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.license_is_inactive'));
            }

            // We don't currently allow checking out licenses to locations, so we'll reset that to user if needed
            if (session()->get('checkout_to_type') == 'location') {
                session()->put(['checkout_to_type' => 'user']);
            }

            // Return the checkout view
            return view('licenses/checkout', compact('license'));
        }

        // Invalid category
        return redirect()->route('licenses.edit', ['license' => $license->id])
            ->with('error', trans('general.invalid_item_category_single', ['type' => trans('general.license')]));

    }

    /**
     * Validates and stores the license checkout action.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param LicenseCheckoutRequest $request
     * @param $licenseId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(LicenseCheckoutRequest $request, $licenseId, $seatId = null)
    {
        if (! $license = License::find($licenseId)) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }


        $this->authorize('checkout', $license);

        // Make sure there is at least one available to checkout
        if ($license->availCount()->count() < 1) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.not_enough_seats'));
        }

        // Make sure the license is expired or terminated
        if ($license->isInactive()) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.license_is_inactive'));
        }

        $licenseSeat = $this->findLicenseSeatToCheckout($license, $seatId);
        $licenseSeat->created_by = auth()->id();
        $licenseSeat->notes = $request->input('notes');

        if ($request->filled('asset_id')) {
            session()->put(['checkout_to_type' => 'asset']);
            $checkoutTarget = $this->checkoutToAsset($licenseSeat);
            $request->request->add(['assigned_asset' => $checkoutTarget->id]);
            session()->put(['redirect_option' => $request->get('redirect_option'), 'checkout_to_type' => 'asset']);

        } elseif ($request->filled('assigned_to')) {
            session()->put(['checkout_to_type' => 'user']);
            $checkoutTarget = $this->checkoutToUser($licenseSeat);
            $request->request->add(['assigned_user' => $checkoutTarget->id]);
            session()->put(['redirect_option' => $request->get('redirect_option'), 'checkout_to_type' => 'user']);
        }



        if ($checkoutTarget) {

            return Helper::getRedirectOption($request, $license->id, 'Licenses')
                ->with('success', trans('admin/licenses/message.checkout.success'));
        }



        return redirect()->route('licenses.index')->with('error', trans('Something went wrong handling this checkout.'));
    }

    protected function findLicenseSeatToCheckout($license, $seatId)
    {
        $licenseSeat = LicenseSeat::find($seatId) ?? $license->freeSeat();

        if (! $licenseSeat) {
            if ($seatId) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.unavailable')));
            }
            
            throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.not_enough_seats')));
        }


        if (! $licenseSeat->license->is($license)) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.checkout.mismatch')));
        }

        return $licenseSeat;
    }

    protected function checkoutToAsset($licenseSeat)
    {
        if (is_null($target = Asset::find(request('asset_id')))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.asset_does_not_exist'));
        }
        $licenseSeat->asset_id = request('asset_id');

        // Override asset's assigned user if available
        if ($target->checkedOutToUser()) {
            $licenseSeat->assigned_to = $target->assigned_to;
        }
        if ($licenseSeat->save()) {
            event(new CheckoutableCheckedOut($licenseSeat, $target, auth()->user(), request('notes')));
            return $target;
        }

        return false;
    }

    protected function checkoutToUser($licenseSeat)
    {
        // Fetch the target and set the license user
        if (is_null($target = User::find(request('assigned_to')))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.user_does_not_exist'));
        }
        $licenseSeat->assigned_to = request('assigned_to');

        if ($licenseSeat->save()) {
            event(new CheckoutableCheckedOut($licenseSeat, $target, auth()->user(), request('notes')));
            return $target;
        }

        return false;
    }

    /**
     * Bulk checkin all license seats
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicenseCheckinController::create() method that provides the form view
     * @since [v6.1.1]
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function bulkCheckout($licenseId) {

        Log::debug('Checking out '.$licenseId.' via bulk');
        $license = License::findOrFail($licenseId);
        $this->authorize('checkin', $license);
        $avail_count = $license->getAvailSeatsCountAttribute();

        $users = User::whereNull('deleted_at')->where('autoassign_licenses', '=', 1)->with('licenses')->get();
        Log::debug($avail_count.' will be assigned');

        if ($users->count() > $avail_count) {
            Log::debug('You do not have enough free seats to complete this task, so we will check out as many as we can. ');
        }

        // If the license is valid, check that there is an available seat
        if ($license->availCount()->count() < 1) {
            return redirect()->back()->with('error', trans('admin/licenses/general.bulk.checkout_all.error_no_seats'));
        }


        $assigned_count = 0;

        foreach ($users as $user) {

            // Check to make sure this user doesn't already have this license checked out to them
            if ($user->licenses->where('id', '=', $licenseId)->count()) {
                Log::debug($user->username.' already has this license checked out to them. Skipping... ');
                continue;
            }

            $licenseSeat = $license->freeSeat();

            // Update the seat with checkout info
            $licenseSeat->assigned_to = $user->id;

            if ($licenseSeat->save()) {
                $avail_count--;
                $assigned_count++;
                $licenseSeat->logCheckout(trans('admin/licenses/general.bulk.checkout_all.log_msg'), $user);
                Log::debug('License '.$license->name.' seat '.$licenseSeat->id.' checked out to '.$user->username);
            }

            if ($avail_count ==  0) {
                return redirect()->back()->with('warning', trans('admin/licenses/general.bulk.checkout_all.warn_not_enough_seats', ['count' => $assigned_count]));
            }
        }

        if ($assigned_count ==  0) {
            return redirect()->back()->with('warning', trans('admin/licenses/general.bulk.checkout_all.warn_no_avail_users', ['count' => $assigned_count]));
        }

        return redirect()->back()->with('success', trans_choice('admin/licenses/general.bulk.checkout_all.success', 2, ['count' => $assigned_count] ));


    }
}
