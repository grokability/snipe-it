<?php

namespace App\Http\Controllers\Accessories;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccessoryCheckoutRequest;
use App\Http\Traits\CheckInOutTrait;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\CheckoutAcceptance;
use App\Models\CheckoutRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccessoryCheckoutController extends Controller
{
    use CheckInOutTrait;

    /**
     * Return the form to checkout an Accessory to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function create(Request $request, Accessory $accessory): View|RedirectResponse
    {

        $this->authorize('checkout', $accessory);

        if ($accessory->archived) {
            return redirect()->route('accessories.index')
                ->with('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.accessory')]));
        }

        if ($accessory->category) {
            // Make sure there is at least one available to checkout
            if ($accessory->numRemaining() <= 0) {
                return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkout.unavailable'));
            }

            // Optional ?request_id hint. Present when the admin
            // reached this screen from a /requests row. Drives the
            // side-panel context box (who asked + waiting list).
            // CheckoutRequest::contextForCheckout handles the URL-
            // twiddle guards; a miss returns nulls / empty so the
            // panel renders nothing.
            $context = CheckoutRequest::contextForCheckout(
                $request->integer('request_id') ?: null,
                Accessory::class,
                $accessory->id,
            );

            return view('accessories/checkout', compact('accessory'))
                ->with('checkoutRequest', $context['checkoutRequest'])
                ->with('otherPendingRequests', $context['otherPendingRequests']);
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

        if ($accessory->archived) {
            return redirect()->route('accessories.index')
                ->with('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.accessory')]));
        }

        $target = $this->determineCheckoutTarget();

        if (! $accessory->canCheckoutTo($target)) {
            $targetType = match (class_basename($target)) {
                'User' => trans('general.user'),
                'Location' => trans('general.location'),
                default => trans('general.asset'),
            };

            return redirect()->back()->with('error', trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.accessory').' "'.$accessory->name.'"',
                'item_company' => $accessory->company?->name ?? trans('general.unassigned'),
                'target' => $targetType.' "'.($target->name ?? $target->username ?? $target->id).'"',
            ]));
        }

        $accessory->checkout_qty = $request->input('checkout_qty', 1);

        // Concurrency guard. AccessoryCheckoutRequest's
        // number_remaining_after_checkout rule runs on an unlocked read of
        // numRemaining(), so two simultaneous checkout requests could both
        // pass validation, both attach rows, and land the register at -1.
        // Re-fetch the parent row under lockForUpdate INSIDE a transaction,
        // re-check availability against the locked snapshot, and only then
        // write. Mirrors the License checkout locking pattern.
        $overAllocated = false;

        DB::transaction(function () use ($accessory, $request, $target, &$overAllocated): void {
            $locked = Accessory::whereKey($accessory->id)->lockForUpdate()->first();

            if (! $locked || $locked->numRemaining() < $accessory->checkout_qty) {
                $overAllocated = true;

                return;
            }

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
        });

        if ($overAllocated) {
            return redirect()->back()->with('error', trans('admin/accessories/message.checkout.unavailable'));
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
                $acceptance = CreateCheckoutAcceptanceAction::run($accessory, $targetUser, $accessory->checkout_qty);
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

    /**
     * Bulk-fulfill screen: list every pending CheckoutRequest for
     * this accessory so an admin can process the waiting list in
     * one pass. Reached from the "Fulfill Multiple" trigger on the
     * /requests admin queue when ≥2 pending requests exist for the
     * same accessory. The per-row shape is the shared
     * <x-checkout.recipient-row> component; the store method below
     * iterates the ticked rows.
     */
    public function bulkFulfillCreate(Accessory $accessory): View|RedirectResponse
    {
        $this->authorize('checkout', $accessory);

        if ($accessory->numRemaining() <= 0) {
            return redirect()->route('accessories.show', $accessory)
                ->with('error', trans('admin/accessories/message.checkout.unavailable'));
        }

        $pendingRequests = CheckoutRequest::pending()
            ->where('requestable_type', Accessory::class)
            ->where('requestable_id', $accessory->id)
            ->with('user')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (CheckoutRequest $r) => $r->user !== null)
            ->values();

        if ($pendingRequests->isEmpty()) {
            return redirect()->route('accessories.show', $accessory)
                ->with('info', trans('admin/hardware/message.requests.no_active'));
        }

        return view('checkouts/fulfill-multiple', [
            'item' => $accessory,
            'pendingRequests' => $pendingRequests,
            'formRoute' => route('accessories.fulfill-requests.store', $accessory),
            'remaining' => (int) $accessory->numRemaining(),
        ]);
    }

    /**
     * Iterate the ticked rows and fulfill each in its own
     * transaction (partial-success semantics matching the license
     * bulk-checkout pattern). Each successful fulfillment fires
     * CheckoutableCheckedOut, which the state-machine listener
     * picks up to close the matching request row.
     *
     * Rolls per-row rather than per-batch so one bad target (company
     * mismatch, deleted user, over-allocated qty) doesn't nuke the
     * whole batch. The response summarizes fulfilled + skipped
     * counts and any per-row error messages so the admin knows what
     * to re-try.
     */
    public function bulkFulfillStore(Request $request, Accessory $accessory): RedirectResponse
    {
        $this->authorize('checkout', $accessory);

        // Checkboxes post as enabled_requests[<request_id>]="1",
        // keyed by request id (unchecked boxes don't post at all).
        // Grab the array keys as the ticked-id list.
        $enabledIds = collect(array_keys((array) $request->input('enabled_requests', [])))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($enabledIds->isEmpty()) {
            return redirect()->route('accessories.fulfill-requests.create', $accessory)
                ->with('error', trans('admin/hardware/message.requests.no_selection'));
        }

        $qtyInputs = (array) $request->input('qty', []);
        $userInputs = (array) $request->input('user_id', []);
        $noteInputs = (array) $request->input('notes', []);

        $requests = CheckoutRequest::pending()
            ->where('requestable_type', Accessory::class)
            ->where('requestable_id', $accessory->id)
            ->whereIn('id', $enabledIds)
            ->with('user')
            ->get()
            ->keyBy('id');

        $fulfilled = 0;
        $errors = [];

        foreach ($enabledIds as $requestId) {
            /** @var CheckoutRequest|null $checkoutRequest */
            $checkoutRequest = $requests->get($requestId);
            if (! $checkoutRequest) {
                $errors[] = trans('admin/hardware/message.requests.row_stale', ['id' => $requestId]);

                continue;
            }

            $targetUserId = (int) ($userInputs[$requestId] ?? $checkoutRequest->user_id);
            $qty = (int) ($qtyInputs[$requestId] ?? $checkoutRequest->quantity);
            $note = $noteInputs[$requestId] ?? $checkoutRequest->notes;

            if ($qty < 1) {
                $errors[] = trans('admin/hardware/message.requests.row_qty_invalid', ['id' => $requestId]);

                continue;
            }

            $targetUser = User::find($targetUserId);
            if (! $targetUser) {
                $errors[] = trans('admin/hardware/message.requests.row_user_missing', ['id' => $requestId]);

                continue;
            }

            if (! $accessory->canCheckoutTo($targetUser)) {
                $errors[] = trans('admin/hardware/message.requests.row_company_mismatch', [
                    'id' => $requestId,
                    'user' => $targetUser->display_name,
                ]);

                continue;
            }

            $overAllocated = false;

            DB::transaction(function () use ($accessory, $targetUser, $qty, $note, &$overAllocated): void {
                $locked = Accessory::whereKey($accessory->id)->lockForUpdate()->first();

                if (! $locked || $locked->numRemaining() < $qty) {
                    $overAllocated = true;

                    return;
                }

                for ($i = 0; $i < $qty; $i++) {
                    $accessory_checkout = new AccessoryCheckout([
                        'accessory_id' => $accessory->id,
                        'created_at' => Carbon::now(),
                        'assigned_to' => $targetUser->id,
                        'assigned_type' => User::class,
                        'note' => $note,
                    ]);
                    $accessory_checkout->created_by = auth()->id();
                    $accessory_checkout->save();
                }
            });

            if ($overAllocated) {
                $errors[] = trans('admin/hardware/message.requests.row_over_allocated', [
                    'id' => $requestId,
                ]);

                continue;
            }

            try {
                event(new CheckoutableCheckedOut(
                    $accessory,
                    $targetUser,
                    auth()->user(),
                    $note,
                    [],
                    $qty,
                    false,
                ));
            } catch (\Throwable $e) {
                Log::warning('Bulk-fulfill event dispatch failed for request '.$requestId.': '.$e->getMessage());
            }

            $fulfilled++;
        }

        $summary = trans('admin/hardware/message.requests.bulk_summary', [
            'fulfilled' => $fulfilled,
            'total' => $enabledIds->count(),
        ]);

        return redirect()->route('requests.index')
            ->with($fulfilled > 0 ? 'success' : 'warning', $summary)
            ->with('multi_error_messages', $errors);
    }
}
