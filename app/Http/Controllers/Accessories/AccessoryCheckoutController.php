<?php

namespace App\Http\Controllers\Accessories;

use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccessoryCheckoutRequest;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccessoryCheckoutController extends Controller
{
    use CheckInOutRequest;

    /**
     * Return the form to checkout an Accessory to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $id
     */
    public function create(Accessory $accessory): View|RedirectResponse
    {

        $this->authorize('checkout', $accessory);

        if ($accessory->category) {
            // Make sure there is at least one available to checkout
            if ($accessory->numRemaining() <= 0) {
                return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkout.unavailable'));
            }

            // Return the checkout view
            return view('accessories/checkout', compact('accessory'));
        }

        // Invalid category
        return redirect()->route('accessories.edit', ['accessory' => $accessory->id])
            ->with('error', trans('general.invalid_item_category_single', ['type' => trans('general.accessory')]));

    }

    /**
     * Save the Accessory checkout information.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  Request  $request
     */
    public function store(AccessoryCheckoutRequest $request, Accessory $accessory): RedirectResponse
    {

        $this->authorize('checkout', $accessory);

        $target = $this->determineCheckoutTarget();
        session()->put(['checkout_to_type' => $target]);

        $accessory->checkout_qty = $request->input('checkout_qty', 1);

        for ($i = 0; $i < $accessory->checkout_qty; $i++) {

            $accessory_checkout = new AccessoryCheckout([
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'assigned_to' => $target->id,
                'assigned_type' => $target::class,
                'note' => $request->input('note'),
            ]);

            $accessory_checkout->created_by = auth()->id();
            $accessory_checkout->save();
        }

        event(new CheckoutableCheckedOut(
            $accessory,
            $target,
            auth()->user(),
            $request->input('note'),
            [],
            $accessory->checkout_qty,
            $request->boolean('sign_in_place'),
        ));

        $request->request->add(['checkout_to_type' => request('checkout_to_type')]);
        $request->request->add(['assigned_to' => $target->id]);

        session()->put([
            'redirect_option' => $request->input('redirect_option'),
            'checkout_to_type' => $request->input('checkout_to_type'),
            'sign_in_place' => $request->boolean('sign_in_place'),
        ]);

        // When sign_in_place is requested for a user checkout, redirect to the
        // acceptance/signature page so the user can sign in person.
        if ($request->boolean('sign_in_place') && ! in_array($request->input('checkout_to_type'), ['asset', 'location'], true)) {
            $targetUser = User::find($target->id);

            if (! $targetUser instanceof User) {
                return redirect()->route('accessories.checkout.show', $accessory)
                    ->with('error', trans('admin/accessories/message.checkout.user_does_not_exist'));
            }

            $acceptance = CheckoutAcceptance::where('checkoutable_type', Accessory::class)
                ->where('checkoutable_id', $accessory->id)
                ->where('assigned_to_id', $targetUser->id)
                ->pending()
                ->latest()
                ->first();

            // If requireAcceptance() is false the listener won't have created one; create it now.
            if (! $acceptance) {
                $acceptance = new CheckoutAcceptance;
                $acceptance->checkoutable()->associate($accessory);
                $acceptance->assignedTo()->associate($targetUser);
                $acceptance->qty = $accessory->checkout_qty;
                $acceptance->save();
            }

            session([
                'sign_in_place_acceptance_id' => $acceptance->id,
                'sign_in_place_item_id' => $accessory->id,
                'sign_in_place_resource_type' => 'Accessories',
            ]);

            return redirect()->route('account.accept.item', $acceptance->id)
                ->with('success', trans('admin/accessories/message.checkout.success'));
        }

        // Redirect to the new accessory page
        return Helper::getRedirectOption($request, $accessory->id, 'Accessories')
            ->with('success', trans('admin/accessories/message.checkout.success'));
    }
}
