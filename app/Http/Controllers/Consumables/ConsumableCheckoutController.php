<?php

namespace App\Http\Controllers\Consumables;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsumableCheckoutController extends Controller
{
    /**
     * Return a view to checkout a consumable to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumableCheckoutController::store() method that stores the data.
     * @since [v1.0]
     *
     * @param  int  $id
     */
    public function create($id): View|RedirectResponse
    {

        if ($consumable = Consumable::find($id)) {

            $this->authorize('checkout', $consumable);

            // Make sure the category is valid
            if ($consumable->category) {

                // Make sure there is at least one available to checkout
                if ($consumable->numRemaining() <= 0) {
                    return redirect()->route('consumables.index')
                        ->with('error', trans('admin/consumables/message.checkout.unavailable', ['requested' => 1, 'remaining' => $consumable->numRemaining()]));
                }

                // Return the checkout view
                return view('consumables/checkout', compact('consumable'));
            }

            // Invalid category
            return redirect()->route('consumables.edit', ['consumable' => $consumable->id])
                ->with('error', trans('general.invalid_item_category_single', ['type' => trans('general.consumable')]));
        }

        // Not found
        return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.does_not_exist'));

    }

    /**
     * Saves the checkout information
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumableCheckoutController::create() method that returns the form.
     * @since [v1.0]
     *
     * @param  int  $consumableId
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(Request $request, $consumableId)
    {
        if (is_null($consumable = Consumable::with('users')->find($consumableId))) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.not_found'));
        }

        $this->authorize('checkout', $consumable);

        $request->validate([
            'checkout_to_type' => ['nullable', 'in:user,asset'],
            'assigned_user' => ['required_unless:checkout_to_type,asset'],
            'assigned_asset' => ['required_if:checkout_to_type,asset'],
        ]);

        // If the quantity is not present in the request or is not a positive integer, set it to 1
        $quantity = $request->input('checkout_qty');
        if (! isset($quantity) || ! ctype_digit((string) $quantity) || $quantity <= 0) {
            $quantity = 1;
        }

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0 || $quantity > $consumable->numRemaining()) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.checkout.unavailable', ['requested' => $quantity, 'remaining' => $consumable->numRemaining()]));
        }

        $admin_user = auth()->user();

        // Resolve the checkout target: user (default) or asset.
        $checkoutToType = $request->input('checkout_to_type', 'user');

        if ($checkoutToType === 'asset') {
            if (is_null($target = Asset::find($request->input('assigned_asset')))) {
                return redirect()->route('consumables.checkout.show', $consumable)->with('error', trans('admin/consumables/message.checkout.asset_does_not_exist'))->withInput();
            }
        } elseif (is_null($target = User::find($request->input('assigned_user')))) {
            return redirect()->route('consumables.checkout.show', $consumable)->with('error', trans('admin/consumables/message.checkout.user_does_not_exist'))->withInput();
        }

        if (! $consumable->canCheckoutTo($target)) {
            $targetType = $target instanceof User ? trans('general.user') : trans('general.asset');

            return redirect()->back()->with('error', trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.consumable').' "'.$consumable->name.'"',
                'item_company' => $consumable->company?->name ?? trans('general.unassigned'),
                'target' => $targetType.' "'.($target->name ?? $target->username ?? $target->id).'"',
            ]));
        }

        // Update the consumable data
        $consumable->assigned_to = $target->id;
        $consumable->checkout_qty = $quantity;

        // Concurrency guard. The unlocked numRemaining() check above is
        // advisory only — two simultaneous checkout requests could both
        // read "1 remaining", both pass the check, both attach a pivot
        // row, and land the register at -1. Re-fetch the parent row under
        // lockForUpdate INSIDE a transaction, re-check availability
        // against the locked snapshot, and only then write. Mirrors the
        // License checkout locking pattern.
        $overAllocated = false;

        DB::transaction(function () use ($consumable, $request, $admin_user, $quantity, $target, &$overAllocated): void {
            $locked = Consumable::whereKey($consumable->id)->lockForUpdate()->first();

            if (! $locked || $locked->numRemaining() < $quantity) {
                $overAllocated = true;

                return;
            }

            for ($i = 0; $i < $quantity; $i++) {
                // The target may be a user or an asset. We write the pivot row
                // directly via attach() (a raw insert) rather than through the
                // ConsumableAssignment model so we bypass its currently
                // user-only 'exists:users' validation. assigned_type records
                // which target class this row belongs to.
                $consumable->users()->attach($consumable->id, [
                    'consumable_id' => $consumable->id,
                    'created_by' => $admin_user->id,
                    'assigned_to' => $target->id,
                    'assigned_type' => $target::class,
                    'note' => $request->input('note'),
                ]);
            }
        });

        if ($overAllocated) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.checkout.unavailable', [
                'requested' => $quantity,
                'remaining' => $consumable->fresh()->numRemaining(),
            ]));
        }

        event(new CheckoutableCheckedOut(
            $consumable,
            $target,
            auth()->user(),
            $request->input('note'),
            [],
            $consumable->checkout_qty,
            $request->boolean('sign_in_place'),
        ));

        if ($target instanceof User) {
            $request->request->add(['checkout_to_type' => 'user']);
            $request->request->add(['assigned_user' => $target->id]);
        } else {
            $request->request->add(['checkout_to_type' => 'asset']);
            $request->request->add(['assigned_asset' => $target->id]);
        }

        session()->put([
            'redirect_option' => $request->input('redirect_option'),
            'checkout_to_type' => $request->input('checkout_to_type'),
            'sign_in_place' => $request->boolean('sign_in_place'),
        ]);

        // When sign_in_place is requested, redirect to the acceptance/signature page
        // so the user can sign in person. The signature is attributed to the target user.
        if ($request->boolean('sign_in_place') && $target instanceof User) {
            $acceptance = CheckoutAcceptance::where('checkoutable_type', Consumable::class)
                ->where('checkoutable_id', $consumable->id)
                ->where('assigned_to_id', $target->id)
                ->pending()
                ->latest()
                ->first();

            // If requireAcceptance() is false the listener won't have created one; create it now.
            if (! $acceptance) {
                $acceptance = CreateCheckoutAcceptanceAction::run($consumable, $target, $quantity);
            }

            session([
                'sign_in_place_acceptance_id' => $acceptance->id,
                'sign_in_place_item_id' => $consumable->id,
                'sign_in_place_resource_type' => 'Consumables',
            ]);

            return redirect()->route('account.accept.item', $acceptance->id)
                ->with('success', trans('admin/consumables/message.checkout.success'));
        }

        // Redirect to the new consumable page
        return Helper::getRedirectOption($request, $consumable->id, 'Consumables')
            ->with('success', trans('admin/consumables/message.checkout.success'));
    }
}
