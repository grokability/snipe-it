<?php

namespace App\Http\Controllers\Accessories;

use App\Events\CheckoutableCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AccessoryCheckinController extends Controller
{
    /**
     * Check the accessory back into inventory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int|null  $accessoryCheckoutId
     * @param  string|null  $backto
     */
    public function create($accessoryCheckoutId = null, $backto = null): View|RedirectResponse
    {
        if (is_null($accessory_checkout = AccessoryCheckout::find($accessoryCheckoutId))) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        $accessory = Accessory::find($accessory_checkout->accessory_id);
        $this->authorize('checkin', $accessory);

        // based on what the accessory is checked out to the target redirect option will be displayed accordingly.
        $target_option = match ($accessory_checkout->assigned_type) {
            'App\Models\Asset' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
            'App\Models\Location' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.location')]),
            default => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
        };

        return view('accessories/checkin', compact('accessory', 'target_option'))
            ->with('target', $accessory_checkout->assignedTo)
            ->with('backto', $backto)
            ->with('checkoutLog', $accessory_checkout);

    }

    /**
     * Check in the item so that it can be checked out again to someone else
     *
     * @uses Accessory::checkin_email() to determine if an email can and should be sent
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  null  $accessoryCheckoutId
     * @param  string  $backto
     */
    public function store(Request $request, $accessoryCheckoutId = null, $backto = null): RedirectResponse
    {
        if (is_null($accessory_checkout = AccessoryCheckout::find($accessoryCheckoutId))) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory = Accessory::find($accessory_checkout->accessory_id);
        $this->authorize('checkin', $accessory);

        session()->put('checkedInFrom', $accessory_checkout->assigned_to);
        session()->put('checkout_to_type', match ($accessory_checkout->assigned_type) {
            'App\Models\User' => 'user',
            'App\Models\Location' => 'location',
            'App\Models\Asset' => 'asset',
            default => null,
        });

        $checkin_hours = date('H:i:s');
        $checkin_at = date('Y-m-d H:i:s');
        if ($request->filled('checkin_at')) {
            $checkin_at = $request->input('checkin_at').' '.$checkin_hours;
        }

        // Was the accessory updated?
        if ($accessory_checkout->delete()) {
            event(new CheckoutableCheckedIn($accessory, $accessory_checkout->assignedTo, auth()->user(), $request->input('note'), $checkin_at));

            session()->put(['redirect_option' => $request->input('redirect_option')]);

            return Helper::getRedirectOption($request, $accessory->id, 'Accessories')
                ->with('success', trans('admin/accessories/message.checkin.success'));
        }

        // Redirect to the accessory management page with error
        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
    }

    /**
     * Render the bulk-checkin confirmation interstitial. The caller
     * ticks rows on the accessory show page's "Checked out" table and
     * posts their accessories_checkout ids here. The view lists the
     * selections and collects a single shared note that applies to
     * every row's checkin.
     */
    public function bulkCheckinShow(Request $request): View|RedirectResponse
    {
        $this->authorize('checkin', Accessory::class);

        $checkoutIds = array_filter((array) $request->input('ids', []));

        if (empty($checkoutIds)) {
            return redirect()->back()
                ->with('warning', trans('admin/accessories/message.bulk.checkin_selected.no_rows_selected'));
        }

        $checkouts = $this->scopedCheckoutsForBulkCheckin($checkoutIds);

        if ($checkouts->isEmpty()) {
            return redirect()->back()
                ->with('warning', trans('admin/accessories/message.bulk.checkin_selected.no_rows_selected'));
        }

        return view('accessories.bulk-checkin', [
            'checkouts' => $checkouts,
            'ids' => $checkouts->pluck('id')->all(),
            'return_url' => Helper::sameOriginUrl(URL::previous()) ?? route('accessories.index'),
        ]);
    }

    /**
     * Apply the bulk checkin after the admin confirms on the
     * interstitial. The shared `note` from the form flows into every
     * CheckoutableCheckedIn event, so a single operator note lands on
     * each per-row action_log entry.
     */
    public function bulkCheckinStore(Request $request): RedirectResponse
    {
        $this->authorize('checkin', Accessory::class);

        $redirectTo = Helper::sameOriginUrl($request->input('return_url')) ?? route('accessories.index');

        $checkoutIds = array_filter((array) $request->input('ids', []));

        if (empty($checkoutIds)) {
            return redirect($redirectTo)
                ->with('warning', trans('admin/accessories/message.bulk.checkin_selected.no_rows_selected'));
        }

        $checkouts = $this->scopedCheckoutsForBulkCheckin($checkoutIds);
        $note = $request->input('note');

        $count = 0;
        foreach ($checkouts as $checkout) {
            $accessory = $checkout->accessory;
            if (! $accessory || ! Gate::allows('checkin', $accessory)) {
                continue;
            }

            if ($checkout->delete()) {
                event(new CheckoutableCheckedIn(
                    $accessory,
                    $checkout->assignedTo,
                    auth()->user(),
                    $note,
                    Carbon::now(),
                ));
                $count++;
            }
        }

        return redirect($redirectTo)
            ->with('success', trans_choice('admin/accessories/message.bulk.checkin_selected.success', $count, ['count' => $count]));
    }

    /**
     * Defense in depth for FMCS / floater. AccessoryCheckout doesn't
     * carry CompanyableTrait, so a cross-company id POSTed by hand
     * would reach the loop and rely purely on the per-row Gate check.
     * Narrow the lookup to rows whose parent Accessory is reachable
     * via a subquery against Accessory's auto-applied CompanyableScope.
     *
     * @param  array<int, mixed>  $checkoutIds
     * @return \Illuminate\Database\Eloquent\Collection<int, AccessoryCheckout>
     */
    protected function scopedCheckoutsForBulkCheckin(array $checkoutIds)
    {
        return AccessoryCheckout::with('accessory')
            ->whereIn('id', $checkoutIds)
            ->whereIn('accessory_id', Accessory::query()->select('id'))
            ->get();
    }
}
