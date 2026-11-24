<?php

namespace App\Actions\CheckoutRequests;

use App\Exceptions\DuplicateCheckoutRequest;
use App\Exceptions\ItemNotRequestable;
use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RequestAssetNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Log;

class CreateCheckoutRequestAction
{
    /**
     * Polymorphic request-submit path for every requestable type
     * (Asset, AssetModel, Accessory, Consumable, Component, License).
     *
     * Consolidates the three previous submit paths (this action, the
     * web-side ViewAssetsController::handleSubmitRequest, and the API-
     * side Api\CheckoutRequest::createRequestFor) into a single flow
     * that:
     *   - validates $requestable is flagged requestable AND FMCS-reachable
     *   - rejects duplicate open requests from the same (user, item) pair
     *   - writes a single actionlog row (uniform across all six types)
     *   - persists the request via the trait's request() method
     *   - bumps assets.requests_counter (Asset only - the counter is
     *     an Asset-specific denormalization; other types don't have one)
     *   - fires the admin-alert notification
     *
     * @param  Accessory|Asset|AssetModel|Component|Consumable|License  $requestable
     * @param  int|null  $qty  Quantity for qty-tracked types (defaults to 1)
     * @param  string|null  $startDate  Optional reservation window start
     * @param  string|null  $endDate  Optional reservation window end
     * @param  string|null  $notes  Optional requester note
     *
     * @throws ItemNotRequestable
     * @throws AuthorizationException
     * @throws DuplicateCheckoutRequest
     */
    public static function run(
        Model $requestable,
        User $user,
        ?int $qty = 1,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $notes = null,
    ): bool {
        if (! $requestable->isFlaggedRequestable()) {
            throw new ItemNotRequestable;
        }
        if (! Company::isCurrentUserHasAccess($requestable)) {
            throw new AuthorizationException;
        }

        // Enforce single-active-request-per-user-per-item. Without this
        // gate the same POST fires repeatedly, adding duplicate pending
        // rows for the same (user, item) pair. Downstream aggregators
        // (open_requests count, admin queue, requester tab) all reason
        // in terms of "one open row per pair" so duplicates would
        // double-count everywhere.
        if ($requestable->isRequestedBy($user)) {
            throw new DuplicateCheckoutRequest;
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'item' => $requestable,
            'item_type' => Helper::shortTypeName($requestable),
            'item_quantity' => $qty ?? 1,
            'target' => $user,
            'requested_by' => $user->display_name,
            'user_id' => auth()->id(),
            'asset_id' => $requestable->getKey(),
            'requested_date' => $now,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'note' => $notes,
        ];

        $logaction = new Actionlog;
        $logaction->item_id = $requestable->getKey();
        $logaction->item_type = $requestable::class;
        $logaction->created_at = $now;
        $logaction->created_by = auth()->id();
        $logaction->target_id = auth()->id();
        $logaction->target_type = User::class;
        $logaction->location_id = $user->location_id ?? null;
        $logaction->logaction('requested');

        // Row write + counter increment share one transaction so a
        // partial failure can't leave the counter incremented without a
        // matching row (or vice versa). Counter is the denormalized
        // fast-path for open-request reads on the assets list; state
        // on the row is the source of truth, counter is a materialized
        // view kept in sync at write time. Only Asset carries the
        // counter column, so the increment is guarded on type.
        DB::transaction(function () use ($requestable, $qty, $startDate, $endDate, $notes) {
            $requestable->request($qty ?? 1, $startDate, $endDate, $notes);
            if ($requestable instanceof Asset) {
                $requestable->increment('requests_counter', 1);
            }
        });

        $settings = Setting::getSettings();
        if ($settings->alert_email != '' && $settings->alerts_enabled == '1' && ! config('app.lock_passwords')) {
            try {
                $settings->notify((new RequestAssetNotification($data))->locale($settings->locale));
            } catch (\Exception $e) {
                Log::warning('Could not send asset request notification: '.$e->getMessage());
            }
        }

        return true;
    }
}
