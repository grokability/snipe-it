<?php

namespace App\Http\Controllers\Components;

use App\Actions\CheckoutRequests\FulfillCheckoutRequestAction;
use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ComponentCheckoutController extends Controller
{
    /**
     * Returns a view that allows the checkout of a component to an asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentCheckoutController::store() method that stores the data.
     * @since [v3.0]
     *
     * @param  int  $id
     *
     * @throws AuthorizationException
     */
    public function create(Request $request, $id): View|RedirectResponse
    {

        if ($component = Component::find($id)) {

            $this->authorize('checkout', $component);

            if ($component->archived) {
                return redirect()->route('components.index')
                    ->with('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.component')]));
            }

            // Make sure the category is valid
            if ($component->category) {

                // Make sure there is at least one available to checkout
                if ($component->numRemaining() <= 0) {
                    return redirect()->route('components.index')
                        ->with('error', trans('admin/components/message.checkout.unavailable'));
                }

                // Optional ?request_id hint. Present when the admin
                // reached this screen from a /requests row (see the
                // components branch of checkoutRequestActionsFormatter).
                // Drives two view affordances: the picker pre-scopes to
                // the requester's assigned assets, and a side-panel box
                // surfaces who asked for it (plus anyone else still
                // waiting) so the admin has the full context in one
                // place. CheckoutRequest::contextForCheckout does the
                // URL-twiddle guards (row exists, is open, targets
                // THIS component, requester still exists) - a miss
                // returns nulls / empty so the picker stays unscoped
                // and the side panel renders nothing.
                $context = CheckoutRequest::contextForCheckout(
                    $request->integer('request_id') ?: null,
                    Component::class,
                    $component->id,
                );

                return view('components/checkout', compact('component'))
                    ->with('snipe_component', $component)
                    ->with('requestingUserId', $context['requestingUserId'])
                    ->with('checkoutRequest', $context['checkoutRequest'])
                    ->with('otherPendingRequests', $context['otherPendingRequests']);
            }

            // Invalid category
            return redirect()->route('components.edit', ['component' => $component->id])
                ->with('error', trans('general.invalid_item_category_single', ['type' => trans('general.component')]));
        }

        // Not found
        return redirect()->route('components.index')->with('error', trans('admin/components/message.not_found'));

    }

    /**
     * Validate and store checkout data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentCheckoutController::create() method that returns the form.
     * @since [v3.0]
     *
     * @param  int  $componentId
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(Request $request, $componentId)
    {
        // Check if the component exists
        if (! $component = Component::find($componentId)) {
            // Redirect to the component management page with error
            return redirect()->route('components.index')->with('error', trans('admin/components/message.not_found'));
        }

        $this->authorize('checkout', $component);

        if ($component->archived) {
            return redirect()->route('components.index')
                ->with('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.component')]));
        }

        $max_to_checkout = $component->numRemaining();

        // Make sure there are at least the requested number of components available to checkout
        if ($max_to_checkout < $request->input('assigned_qty')) {
            return redirect()->back()->withInput()->with('error', trans('admin/components/message.checkout.unavailable', ['remaining' => $max_to_checkout, 'requested' => $request->input('assigned_qty')]));
        }

        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|exists:assets,id',
            'assigned_qty' => "required|numeric|min:1|digits_between:1,$max_to_checkout",
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if the asset exists
        $asset = Asset::find($request->input('asset_id'));

        if (! $component->canCheckoutTo($asset)) {
            return redirect()->route('components.checkout.show', $componentId)->with('error', trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.component').' "'.$component->name.'"',
                'item_company' => $component->company?->name ?? trans('general.unassigned'),
                'target' => trans('general.asset').' "'.$asset->display_name.'"',
            ]));
        }

        $component->checkout_qty = $request->input('assigned_qty');

        // Concurrency guard. The numRemaining() check above is an unlocked
        // read, so two simultaneous checkout requests could both pass, both
        // attach a pivot row, and land the register at -1. Re-fetch the
        // parent under lockForUpdate INSIDE a transaction, re-check against
        // the locked snapshot, then write. Mirrors the License checkout
        // locking pattern.
        $overAllocated = false;

        DB::transaction(function () use ($component, $request, &$overAllocated): void {
            $locked = Component::whereKey($component->id)->lockForUpdate()->first();

            if (! $locked || $locked->numRemaining() < $component->checkout_qty) {
                $overAllocated = true;

                return;
            }

            $component->asset_id = $request->input('asset_id');
            $component->assets()->attach($component->id, [
                'component_id' => $component->id,
                'created_by' => auth()->user()->id,
                'created_at' => date('Y-m-d H:i:s'),
                'assigned_qty' => $component->checkout_qty,
                'asset_id' => $request->input('asset_id'),
                'note' => $request->input('note'),
            ]);
        });

        if ($overAllocated) {
            return redirect()->back()->withInput()->with('error', trans('admin/components/message.checkout.unavailable', [
                'remaining' => $component->fresh()->numRemaining(),
                'requested' => $component->checkout_qty,
            ]));
        }

        event(new CheckoutableCheckedOut(
            $component,
            $asset,
            auth()->user(),
            $request->input('note'),
            [],
            $component->checkout_qty,
        ));

        $request->request->add(['checkout_to_type' => 'asset']);
        $request->request->add(['assigned_asset' => $asset->id]);

        session()->put(['redirect_option' => $request->input('redirect_option'), 'checkout_to_type' => $request->input('checkout_to_type')]);

        return Helper::getRedirectOption($request, $component->id, 'Components')
            ->with('success', trans('admin/components/message.checkout.success'));
    }

    /**
     * Bulk-fulfill screen for the component's pending-request
     * queue. Unlike the user-target types (accessory/consumable/
     * license), a component checkout targets an Asset (component
     * gets installed INTO an asset), so each row here picks an
     * asset from the requester's assigned assets rather than a
     * user. The available-asset set is pre-computed per row so the
     * picker is a plain <select> - requester-scoped lists are
     * typically small and don't warrant ajax pagination.
     *
     * Rows for requesters with zero assigned assets render disabled
     * with a "no assets to install into" message so the admin can
     * see WHY that row can't be fulfilled from this screen (they'd
     * need to check out an asset to the requester first).
     */
    public function bulkFulfillCreate(Component $component)
    {
        $this->authorize('checkout', $component);

        if ($component->numRemaining() <= 0) {
            return redirect()->route('components.show', $component)
                ->with('error', trans('admin/components/message.checkout.unavailable'));
        }

        $pendingRequests = CheckoutRequest::pending()
            ->where('requestable_type', Component::class)
            ->where('requestable_id', $component->id)
            ->with('user')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (CheckoutRequest $r) => $r->user !== null)
            ->values();

        if ($pendingRequests->isEmpty()) {
            return redirect()->route('components.show', $component)
                ->with('info', trans('admin/hardware/message.requests.no_active'));
        }

        // Pre-compute the picker options per row. Requester-scoped:
        // "which of MY assets should this component get installed
        // into?" is the natural framing. Bulk-load all requester
        // ids in one query to avoid N per-row queries.
        $requesterIds = $pendingRequests->pluck('user_id')->unique()->all();
        $assetsByRequester = Asset::where('assigned_type', User::class)
            ->whereIn('assigned_to', $requesterIds)
            ->with('model')
            ->get()
            ->groupBy('assigned_to');

        $rowContext = [];
        foreach ($pendingRequests as $request) {
            $available = $assetsByRequester->get($request->user_id, collect());
            $rowContext[$request->id] = [
                'availableAssets' => $available,
                'emptyMessage' => $available->isEmpty()
                    ? trans('admin/hardware/message.requests.no_target_assets_for_user', [
                        'user' => $request->user->display_name,
                    ])
                    : null,
            ];
        }

        return view('checkouts/fulfill-multiple-to-asset', [
            'item' => $component,
            'pendingRequests' => $pendingRequests,
            'rowContext' => $rowContext,
            'formRoute' => route('components.fulfill-requests.store', $component),
            'remaining' => (int) $component->numRemaining(),
        ]);
    }

    /**
     * Iterate + fulfill. Each ticked row installs the component
     * into the admin-picked asset in its own transaction (per-row
     * partial-success matching the sibling bulk-fulfill controllers).
     * Because component checkouts target an Asset (not a User),
     * the state-machine event listener - which filters on User
     * targets - would not close the matching request. So we call
     * FulfillCheckoutRequestAction explicitly per successful row.
     */
    public function bulkFulfillStore(Request $request, Component $component): RedirectResponse
    {
        $this->authorize('checkout', $component);

        // Checkboxes post as enabled_requests[<request_id>]="1",
        // keyed by request id (unchecked boxes don't post at all).
        $enabledIds = collect(array_keys((array) $request->input('enabled_requests', [])))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($enabledIds->isEmpty()) {
            return redirect()->route('components.fulfill-requests.create', $component)
                ->with('error', trans('admin/hardware/message.requests.no_selection'));
        }

        $assetInputs = (array) $request->input('asset_id', []);
        $qtyInputs = (array) $request->input('qty', []);
        $noteInputs = (array) $request->input('notes', []);

        $requests = CheckoutRequest::pending()
            ->where('requestable_type', Component::class)
            ->where('requestable_id', $component->id)
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

            $assetId = (int) ($assetInputs[$requestId] ?? 0);
            $qty = (int) ($qtyInputs[$requestId] ?? $checkoutRequest->quantity);
            $note = $noteInputs[$requestId] ?? $checkoutRequest->notes;

            if ($qty < 1) {
                $errors[] = trans('admin/hardware/message.requests.row_qty_invalid', ['id' => $requestId]);

                continue;
            }

            $targetAsset = Asset::find($assetId);
            if (! $targetAsset) {
                $errors[] = trans('admin/hardware/message.requests.row_asset_missing', ['id' => $requestId]);

                continue;
            }

            // Requester-scoping guard. Prevent a hand-crafted POST
            // from installing the component into an asset that
            // doesn't belong to the requester.
            if ((int) $targetAsset->assigned_to !== (int) $checkoutRequest->user_id
                || $targetAsset->assigned_type !== User::class
            ) {
                $errors[] = trans('admin/hardware/message.requests.row_asset_not_requesters', [
                    'id' => $requestId,
                ]);

                continue;
            }

            if (! $component->canCheckoutTo($targetAsset)) {
                $errors[] = trans('admin/hardware/message.requests.row_company_mismatch', [
                    'id' => $requestId,
                    'user' => $targetAsset->display_name,
                ]);

                continue;
            }

            $overAllocated = false;

            DB::transaction(function () use ($component, $targetAsset, $qty, $note, $adminUser, &$overAllocated): void {
                $locked = Component::whereKey($component->id)->lockForUpdate()->first();

                if (! $locked || $locked->numRemaining() < $qty) {
                    $overAllocated = true;

                    return;
                }

                $component->assets()->attach($component->id, [
                    'component_id' => $component->id,
                    'created_by' => $adminUser->id,
                    'created_at' => now(),
                    'assigned_qty' => $qty,
                    'asset_id' => $targetAsset->id,
                    'note' => $note,
                ]);
            });

            if ($overAllocated) {
                $errors[] = trans('admin/hardware/message.requests.row_over_allocated', ['id' => $requestId]);

                continue;
            }

            try {
                event(new CheckoutableCheckedOut(
                    $component,
                    $targetAsset,
                    $adminUser,
                    $note,
                    [],
                    $qty,
                ));
            } catch (\Throwable $e) {
                Log::warning('Bulk-fulfill event dispatch failed for request '.$requestId.': '.$e->getMessage());
            }

            // Component checkouts target an Asset, and the state-
            // machine listener only fires fulfillment for User-
            // target checkouts (see FulfillCheckoutRequestListener).
            // So we close the request explicitly here, passing the
            // admin's per-row qty so partial-fulfillment tracking
            // stays accurate (request for 5 of the component that
            // admin only had 3 of stays pending with 2 remaining).
            try {
                FulfillCheckoutRequestAction::run($checkoutRequest, null, $qty);
            } catch (\Throwable $e) {
                Log::warning('Bulk-fulfill state-machine close failed for request '.$requestId.': '.$e->getMessage());
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
