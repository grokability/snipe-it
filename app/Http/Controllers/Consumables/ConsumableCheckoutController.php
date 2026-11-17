<?php

namespace App\Http\Controllers\Consumables;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Actionlog;
use App\Models\CheckoutAcceptance;
use App\Models\CheckoutRequest;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
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
    public function create(Request $request, $id): View|RedirectResponse
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

                // Optional ?request_id hint. Present when the admin
                // reached this screen from a /requests row. Drives
                // the side-panel context box (who asked + waiting
                // list). CheckoutRequest::contextForCheckout handles
                // the URL-twiddle guards; a miss returns nulls /
                // empty so the panel renders nothing.
                $context = CheckoutRequest::contextForCheckout(
                    $request->integer('request_id') ?: null,
                    Consumable::class,
                    $consumable->id,
                );

                return view('consumables/checkout', compact('consumable'))
                    ->with('checkoutRequest', $context['checkoutRequest'])
                    ->with('otherPendingRequests', $context['otherPendingRequests']);
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
        $assigned_to = e($request->input('assigned_user'));

        // Check if the user exists
        if (is_null($user = User::find($assigned_to))) {
            // Redirect to the consumable management page with error
            return redirect()->route('consumables.checkout.show', $consumable)->with('error', trans('admin/consumables/message.checkout.user_does_not_exist'))->withInput();
        }

        if (! $consumable->canCheckoutTo($user)) {
            return redirect()->back()->with('error', trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.consumable').' "'.$consumable->name.'"',
                'item_company' => $consumable->company?->name ?? trans('general.unassigned'),
                'target' => trans('general.user').' "'.$user->username.'"',
            ]));
        }

        // Update the consumable data
        $consumable->assigned_to = e($request->input('assigned_user'));
        $consumable->checkout_qty = $quantity;

        // Concurrency guard. The unlocked numRemaining() check above is
        // advisory only — two simultaneous checkout requests could both
        // read "1 remaining", both pass the check, both attach a pivot
        // row, and land the register at -1. Re-fetch the parent row under
        // lockForUpdate INSIDE a transaction, re-check availability
        // against the locked snapshot, and only then write. Mirrors the
        // License checkout locking pattern.
        $overAllocated = false;

        DB::transaction(function () use ($consumable, $request, $admin_user, $quantity, &$overAllocated): void {
            $locked = Consumable::whereKey($consumable->id)->lockForUpdate()->first();

            if (! $locked || $locked->numRemaining() < $quantity) {
                $overAllocated = true;

                return;
            }

            for ($i = 0; $i < $quantity; $i++) {
                $consumable->users()->attach($consumable->id, [
                    'consumable_id' => $consumable->id,
                    'created_by' => $admin_user->id,
                    'assigned_to' => e($request->input('assigned_user')),
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
            $user,
            auth()->user(),
            $request->input('note'),
            [],
            $consumable->checkout_qty,
            $request->boolean('sign_in_place'),
        ));

        $request->request->add(['checkout_to_type' => 'user']);
        $request->request->add(['assigned_user' => $user->id]);

        session()->put([
            'redirect_option' => $request->input('redirect_option'),
            'checkout_to_type' => $request->input('checkout_to_type'),
            'sign_in_place' => $request->boolean('sign_in_place'),
        ]);

        // When sign_in_place is requested, redirect to the acceptance/signature page
        // so the user can sign in person. The signature is attributed to the target user.
        if ($request->boolean('sign_in_place')) {
            $acceptance = CheckoutAcceptance::where('checkoutable_type', Consumable::class)
                ->where('checkoutable_id', $consumable->id)
                ->where('assigned_to_id', $user->id)
                ->pending()
                ->latest()
                ->first();

            // If requireAcceptance() is false the listener won't have created one; create it now.
            if (! $acceptance) {
                $acceptance = CreateCheckoutAcceptanceAction::run($consumable, $user, $quantity);
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

    /**
     * Bulk-fulfill screen. Mirrors AccessoryCheckoutController::
     * bulkFulfillCreate - see there for the design rationale.
     */
    public function bulkFulfillCreate(Consumable $consumable)
    {
        $this->authorize('checkout', $consumable);

        if ($consumable->numRemaining() <= 0) {
            return redirect()->route('consumables.show', $consumable)
                ->with('error', trans('admin/consumables/message.checkout.unavailable', [
                    'requested' => 1,
                    'remaining' => $consumable->numRemaining(),
                ]));
        }

        $pendingRequests = CheckoutRequest::pending()
            ->where('requestable_type', Consumable::class)
            ->where('requestable_id', $consumable->id)
            ->with('user')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (CheckoutRequest $r) => $r->user !== null)
            ->values();

        if ($pendingRequests->isEmpty()) {
            return redirect()->route('consumables.show', $consumable)
                ->with('info', trans('admin/hardware/message.requests.no_active'));
        }

        return view('checkouts/fulfill-multiple', [
            'item' => $consumable,
            'pendingRequests' => $pendingRequests,
            'formRoute' => route('consumables.fulfill-requests.store', $consumable),
            'remaining' => (int) $consumable->numRemaining(),
        ]);
    }

    /**
     * Iterate + fulfill. See AccessoryCheckoutController::
     * bulkFulfillStore for the shared per-row/partial-success
     * pattern.
     */
    public function bulkFulfillStore(Request $request, Consumable $consumable): RedirectResponse
    {
        $this->authorize('checkout', $consumable);

        // Checkboxes post as enabled_requests[<request_id>]="1",
        // keyed by request id (unchecked boxes don't post at all).
        $enabledIds = collect(array_keys((array) $request->input('enabled_requests', [])))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($enabledIds->isEmpty()) {
            return redirect()->route('consumables.fulfill-requests.create', $consumable)
                ->with('error', trans('admin/hardware/message.requests.no_selection'));
        }

        $qtyInputs = (array) $request->input('qty', []);
        $userInputs = (array) $request->input('user_id', []);
        $noteInputs = (array) $request->input('notes', []);

        $requests = CheckoutRequest::pending()
            ->where('requestable_type', Consumable::class)
            ->where('requestable_id', $consumable->id)
            ->whereIn('id', $enabledIds)
            ->with('user')
            ->get()
            ->keyBy('id');

        $adminUser = auth()->user();
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

            if (! $consumable->canCheckoutTo($targetUser)) {
                $errors[] = trans('admin/hardware/message.requests.row_company_mismatch', [
                    'id' => $requestId,
                    'user' => $targetUser->display_name,
                ]);

                continue;
            }

            $overAllocated = false;

            DB::transaction(function () use ($consumable, $targetUser, $qty, $note, $adminUser, &$overAllocated): void {
                $locked = Consumable::whereKey($consumable->id)->lockForUpdate()->first();

                if (! $locked || $locked->numRemaining() < $qty) {
                    $overAllocated = true;

                    return;
                }

                for ($i = 0; $i < $qty; $i++) {
                    $consumable->users()->attach($consumable->id, [
                        'consumable_id' => $consumable->id,
                        'created_by' => $adminUser->id,
                        'assigned_to' => $targetUser->id,
                        'note' => $note,
                    ]);
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
                    $consumable,
                    $targetUser,
                    $adminUser,
                    $note,
                    [],
                    $qty,
                    false,
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Bulk-fulfill event dispatch failed for request '.$requestId.': '.$e->getMessage());
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

    /**
     * Mark a single consumable_users pivot row as consumed. One-way:
     * once set, consumed_at never clears, so an already-consumed row
     * no-op redirects. Gated on consumables.checkout per the design
     * decision to let the same permission that distributes units also
     * close them out. Writes an audit-log entry so the lifecycle shows
     * up in the item's history tab.
     */
    public function consume(Consumable $consumable, ConsumableAssignment $assignment): RedirectResponse
    {
        $this->authorize('checkout', $consumable);

        if ($assignment->consumable_id !== $consumable->id) {
            abort(404);
        }

        if ($assignment->isConsumed()) {
            return redirect()->route('consumables.show', $consumable)
                ->with('warning', trans('admin/consumables/message.consumed.already'));
        }

        $assignment->consumed_at = now();
        if (! $assignment->save()) {
            return redirect()->route('consumables.show', $consumable)
                ->with('error', trans('admin/consumables/message.consumed.error'));
        }

        $this->writeConsumeLog($consumable, $assignment);

        return redirect()->route('consumables.show', $consumable)
            ->with('success', trans('admin/consumables/message.consumed.success'));
    }

    /**
     * Confirmation view for marking several pivot rows consumed at
     * once. The POST comes from the bootstrap-table bulk-actions
     * dropdown on the consumable show page's assigned tab. Already-
     * consumed rows and cross-consumable rows are filtered out here
     * so the confirmation page only lists valid targets.
     */
    public function bulkConsumeShow(Request $request): View|RedirectResponse
    {
        $pivotIds = (array) $request->input('ids', []);
        if ($pivotIds === []) {
            return redirect()->back()
                ->with('error', trans('admin/consumables/message.consumed.no_rows_selected'));
        }

        $assignments = ConsumableAssignment::with('consumable', 'user')
            ->whereIn('id', $pivotIds)
            ->whereNull('consumed_at')
            ->get();

        if ($assignments->isEmpty()) {
            return redirect()->back()
                ->with('error', trans('admin/consumables/message.consumed.no_rows_selected'));
        }

        // Every row has to belong to the same parent consumable since
        // this flow runs off the show page's assigned tab. Mixed-parent
        // submissions are a sign the UI was tampered with, so bail.
        $consumableIds = $assignments->pluck('consumable_id')->unique();
        if ($consumableIds->count() !== 1) {
            return redirect()->back()
                ->with('error', trans('admin/consumables/message.consumed.no_rows_selected'));
        }

        $consumable = $assignments->first()->consumable;
        $this->authorize('checkout', $consumable);

        return view('consumables.bulk-consume', [
            'consumable' => $consumable,
            'assignments' => $assignments,
            'ids' => $assignments->pluck('id')->all(),
        ]);
    }

    public function bulkConsumeStore(Request $request): RedirectResponse
    {
        $pivotIds = (array) $request->input('ids', []);
        if ($pivotIds === []) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.consumed.no_rows_selected'));
        }

        $assignments = ConsumableAssignment::with('consumable')
            ->whereIn('id', $pivotIds)
            ->whereNull('consumed_at')
            ->get();

        if ($assignments->isEmpty()) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.consumed.no_rows_selected'));
        }

        $consumableIds = $assignments->pluck('consumable_id')->unique();
        if ($consumableIds->count() !== 1) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.consumed.no_rows_selected'));
        }

        $consumable = $assignments->first()->consumable;
        $this->authorize('checkout', $consumable);

        $now = now();
        $consumed = 0;
        foreach ($assignments as $assignment) {
            $assignment->consumed_at = $now;
            if ($assignment->save()) {
                $consumed++;
                $this->writeConsumeLog($consumable, $assignment);
            }
        }

        return redirect()->route('consumables.show', $consumable)
            ->with('success', trans_choice('admin/consumables/message.consumed.bulk_success', $consumed, ['count' => $consumed]));
    }

    private function writeConsumeLog(Consumable $consumable, ConsumableAssignment $assignment): void
    {
        $log = new Actionlog;
        $log->item_type = Consumable::class;
        $log->item_id = $consumable->id;
        $log->target_type = User::class;
        $log->target_id = $assignment->assigned_to;
        $log->created_at = date('Y-m-d H:i:s');
        $log->created_by = auth()->id();
        $log->logaction('consumed');
    }
}
