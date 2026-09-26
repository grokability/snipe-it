<?php

namespace App\Http\Controllers\Assets;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Traits\CheckInOutTrait;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AssetCheckoutController extends Controller
{
    use CheckInOutTrait;

    /**
     * Returns a view that presents a form to check an asset out to a
     * user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $assetId
     *
     * @since [v1.0]
     *
     * @return View
     */
    public function create(Asset $asset): View|RedirectResponse
    {

        $this->authorize('checkout', $asset);

        if (! $asset->model) {
            return redirect()->route('hardware.show', $asset)
                ->with('error', trans('admin/hardware/general.model_invalid_fix'));
        }

        // Invoke the validation to see if the audit will complete successfully
        $asset->setRules($asset->getRules() + $asset->customFieldValidationRules());

        if ($asset->isInvalid()) {
            // Also flash the specific validation messages via
            // multi_error_messages so they surface in the top alert
            // on the edit page. See the matching block in
            // AssetCheckinController::create() for the reasoning.
            return redirect()->route('hardware.edit', $asset)
                ->withErrors($asset->getErrors())
                ->with('multi_error_messages', $asset->getErrors()->all());
        }

        if ($asset->availableForCheckout()) {
            return view('hardware/checkout', compact('asset'))
                ->with('statusLabel_list', Helper::deployableStatusLabelList())
                ->with('table_name', 'Assets')
                ->with('item', $asset);
        }

        return redirect()->route('hardware.index')
            ->with('error', trans('admin/hardware/message.checkout.not_available'));
    }

    /**
     * Validate and process the form data to check out an asset to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function store(AssetCheckoutRequest $request, $assetId): RedirectResponse
    {

        try {
            // Check if the asset exists
            if (! $asset = Asset::find($assetId)) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            } elseif (! $asset->availableForCheckout()) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
            }
            $this->authorize('checkout', $asset);

            if (! $asset->model) {
                return redirect()->route('hardware.show', $asset)->with('error', trans('admin/hardware/general.model_invalid_fix'));
            }

            $admin = auth()->user();

            $target = $this->determineCheckoutTarget();

            $asset = $this->updateAssetLocation($asset, $target);

            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->input('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->input('checkout_at');
            }

            $expected_checkin = '';
            if ($request->filled('expected_checkin')) {
                $expected_checkin = $request->input('expected_checkin');
            }

            if ($request->filled('status_id')) {
                $asset->status_id = $request->input('status_id');
            }

            // Two-way toggle: checked = requestable, unchecked (or absent) =
            // not. The form pre-populates the checkbox with the asset's current
            // state so users can flip either direction (e.g. mark "no longer
            // requestable" during checkout because the item is now assigned).
            $asset->requestable = $request->boolean('requestable');

            if (! empty($asset->licenseseats->all())) {
                if (request('checkout_to_type') == 'user') {
                    foreach ($asset->licenseseats as $seat) {
                        $seat->assigned_to = $target->id;
                        $seat->save();
                    }
                }
            }

            // Add any custom fields that should be included in the checkout
            $asset->customFieldsForCheckinCheckout('display_checkout');

            if (! $asset->canCheckoutTo($target)) {
                $targetType = match (class_basename($target)) {
                    'User' => trans('general.user'),
                    'Location' => trans('general.location'),
                    default => trans('general.asset'),
                };

                return redirect()->route('hardware.checkout.create', $asset)->with('error', trans('general.error_checkout_company_mismatch', [
                    'item' => trans('general.asset').' "'.$asset->display_name.'"',
                    'item_company' => $asset->company?->name ?? trans('general.unassigned'),
                    'target' => $targetType.' "'.($target->name ?? $target->username ?? $target->id).'"',
                ]));
            }

            session()->put([
                'redirect_option' => $request->input('redirect_option'),
                'checkout_to_type' => $request->input('checkout_to_type'),
                'sign_in_place' => $request->boolean('sign_in_place'),
            ]);

            // Concurrency guard. availableForCheckout() above ran on an
            // unlocked read, so two simultaneous form submits can both
            // observe the asset as available and both proceed through
            // checkOut(), producing duplicate checkout-history rows and
            // double-incrementing checkout_counter on a single-assignment
            // asset. Re-fetch the row under lockForUpdate INSIDE a
            // transaction and re-check availability against the locked
            // snapshot; the second request blocks until the first commits
            // and then sees the asset as no longer available. Mirrors the
            // pattern in Api\AssetsController::checkout and
            // ConsumablesController::store (GHSA-x4g2-87xc-m5jm).
            $checkedOut = DB::transaction(function () use ($asset, $target, $admin, $checkout_at, $expected_checkin, $request): bool {
                $locked = Asset::whereKey($asset->id)->lockForUpdate()->first();
                if (! $locked || ! $locked->availableForCheckout()) {
                    return false;
                }

                return (bool) $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, $request->input('note'), $request->input('name'), null, $request->boolean('sign_in_place'));
            });

            if ($checkedOut) {

                // Phase 1 documents: generate a checkout document for user
                // targets. The checkout is already committed — a document
                // failure is surfaced, never fatal (spec §4).
                if ($target instanceof User) {
                    $checkoutLogId = \App\Models\Actionlog::where('item_type', Asset::class)
                        ->where('item_id', $asset->id)
                        ->where('target_id', $target->id)
                        ->where('action_type', 'checkout')
                        ->latest('id')
                        ->value('id');

                    try {
                        $checkoutDocument = app(\App\Services\Documents\CheckoutDocumentHook::class)
                            ->afterCheckout($asset, $target, $admin, $checkoutLogId);

                        if ($checkoutDocument) {
                            session()->flash('success', trans('admin/hardware/message.checkout.success')
                                .' '.trans('documents.general.checkout_document_generated', ['number' => $checkoutDocument->number]));
                        }
                    } catch (\RuntimeException $e) {
                        session()->flash('warning', trans('admin/hardware/message.checkout.success').' '.$e->getMessage());
                    }
                }

                // When sign_in_place is requested and the target is a user, redirect to the
                // acceptance/signature page so the user can sign in person. The signature is
                // attributed to the target user, not the admin.
                if ($request->boolean('sign_in_place') && $target instanceof User) {
                    $acceptance = CheckoutAcceptance::where('checkoutable_type', Asset::class)
                        ->where('checkoutable_id', $asset->id)
                        ->where('assigned_to_id', $target->id)
                        ->pending()
                        ->latest()
                        ->first();

                    // If requireAcceptance() is false the listener won't have created one; create it now.
                    if (! $acceptance) {
                        $acceptance = CreateCheckoutAcceptanceAction::run($asset, $target);
                    }

                    session([
                        'sign_in_place_acceptance_id' => $acceptance->id,
                        'sign_in_place_item_id' => $asset->id,
                        'sign_in_place_resource_type' => 'Assets',
                    ]);

                    return redirect()->route('account.accept.item', $acceptance->id)
                        ->with('success', trans('admin/hardware/message.checkout.success'));
                }

                return Helper::getRedirectOption($request, $asset->id, 'Assets')
                    ->with('success', trans('admin/hardware/message.checkout.success'));
            }

            // Redirect back to the checkout form with the specific
            // validation messages surfaced via multi_error_messages
            // (replaces the previous stringified MessageBag concat).
            return redirect()->route('hardware.checkout.create', $asset)
                ->with('error', trans('admin/hardware/message.checkout.error'))
                ->with('multi_error_messages', $asset->getErrors()->all());
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', trans('admin/hardware/message.checkout.error'))
                ->withErrors($asset->getErrors())
                ->with('multi_error_messages', $asset->getErrors()->all());
        } catch (CheckoutNotAllowed $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
