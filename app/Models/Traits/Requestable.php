<?php

namespace App\Models\Traits;

use App\Enums\CheckoutRequestState;
use App\Models\CheckoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

// $asset->requests
// $asset->openRequests
// $asset->isRequestedBy($user)
// $asset->whereRequestedBy($user)
trait Requestable
{
    /**
     * Cascade-delete every open + historical CheckoutRequest for this
     * item when the parent is FORCE-deleted. Soft-delete deliberately
     * does not fire this: soft-deleting an item keeps its request
     * history around so an admin can still see who was asking for it,
     * and a subsequent restore keeps the requests intact.
     *
     * The scheduled `snipeit:purge` command runs raw query-builder
     * DELETEs against the models table, which bypasses Eloquent events,
     * so it does its own explicit cascade against `checkout_requests`
     * (see Purge::$childTables). This trait hook covers the direct
     * `$item->forceDelete()` path that skips the purge command.
     */
    public static function bootRequestable(): void
    {
        static::forceDeleted(function ($model): void {
            // CheckoutRequest uses SoftDeletes, so a plain ->delete()
            // would leave `deleted_at`-flagged orphans pointing at a
            // requestable_id that no longer exists. Force-delete so
            // the polymorphic child rows disappear alongside the
            // parent - matches the intent of "if the item is really
            // gone, so are its pending requests".
            $model->requests()->forceDelete();
        });
    }

    public function requests(): MorphMany
    {
        return $this->morphMany(CheckoutRequest::class, 'requestable');
    }

    /**
     * Open requests only - pending state. Callers that want the full
     * history (including canceled + fulfilled) hit ->requests()
     * directly. This one is what the admin queue, the
     * assigned_to_self flag on the requestable-tab API rows, and the
     * upcoming withCount('openRequests') replacement for the
     * requests_counter denorm all read from.
     */
    public function openRequests(): MorphMany
    {
        return $this->requests()->where('state', CheckoutRequestState::Pending->value);
    }

    /**
     * True when this specific row passes the model's own scopeRequestable
     * filter, which is the single source of truth for "an admin can
     * receive a request against this item". Delegating to the scope
     * keeps per-type semantics intact - Asset layers on a deployable-
     * status check + explicit FMCS on top of the boolean flag, and the
     * other five types just check the flag. Callers should use this
     * instead of hand-rolling `Model::requestable()->find($id)`.
     */
    public function isFlaggedRequestable(): bool
    {
        return static::query()
            ->requestable()
            ->whereKey($this->getKey())
            ->exists();
    }

    public function isRequestedBy(User $user)
    {
        // Fresh query rather than filtering the loaded ->requests
        // collection so a same-request-cycle check-then-cancel sees
        // current DB state instead of a possibly stale eager-loaded
        // snapshot. State-gated: only pending rows count as "already
        // requested" (fulfilled + canceled rows are done and don't
        // block a fresh request).
        return $this->requests()
            ->where('state', CheckoutRequestState::Pending->value)
            ->where('user_id', $user->id)
            ->first();
    }

    public function scopeRequestedBy($query, User $user)
    {
        return $query->whereHas(
            'requests', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        );
    }

    public function request($qty = 1, $startDate = null, $endDate = null, $notes = null)
    {
        $this->requests()->save(
            new CheckoutRequest([
                'user_id' => auth()->id(),
                'quantity' => $qty,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'notes' => $notes,
                'state' => CheckoutRequestState::Pending->value,
            ])
        );
    }

    public function deleteRequest()
    {
        $this->requests()->where('user_id', auth()->id())->delete();
    }

    /**
     * Mark every open CheckoutRequest for $user_id (or the current
     * auth user) as canceled. Returns the number of rows actually
     * flipped so callers can gate side effects (notifications, log
     * entries) on real work happening. The pending-state filter
     * prevents already-terminal rows (fulfilled/canceled) from
     * getting their canceled_at bumped, and prevents no-op
     * cancellations from looking like real events downstream.
     *
     * Iterate + save (via cancelAndSync) rather than a query-builder
     * ->update() so the HasCalendarEvents observer fires per row and
     * the matching calendar_events index row gets cleaned up. The
     * cancel count on this path is small (one requester's open
     * requests for one item) so the N+1 shape is deliberate.
     */
    public function cancelRequest($user_id = null): int
    {
        if (! $user_id) {
            $user_id = auth()->id();
        }

        $pending = $this->requests()
            ->where('user_id', $user_id)
            ->where('state', CheckoutRequestState::Pending->value)
            ->get();

        $affected = 0;
        /** @var CheckoutRequest $request */
        foreach ($pending as $request) {
            if ($request->cancelAndSync()) {
                $affected++;
            }
        }

        return $affected;
    }

    /**
     * Info-panel side-widget descriptor for the "N open requests"
     * link on qty-tracked requestable types. Returns
     *
     *     ['url' => string, 'count' => int]
     *
     * when a bulk-fulfill screen exists for the item's type AND
     * there are ≥2 open requests AND the item has stock available
     * to hand out AND the current user can checkout the type.
     * Returns null in every other case so the info-panel skips the
     * row cleanly (Blade uses one method_exists check + one
     * truthiness check, no computation logic in the view per the
     * project's no-@php rule).
     *
     * @return array{url: string, count: int}|null
     */
    public function bulkFulfillmentLink(): ?array
    {
        $routeMap = [
            \App\Models\Accessory::class => 'accessories.fulfill-requests.create',
            \App\Models\Consumable::class => 'consumables.fulfill-requests.create',
            \App\Models\Component::class => 'components.fulfill-requests.create',
            \App\Models\License::class => 'licenses.fulfill-requests.create',
            \App\Models\AssetModel::class => 'models.fulfill-requests.create',
        ];

        $routeName = $routeMap[static::class] ?? null;
        if ($routeName === null) {
            return null;
        }

        // Permission check per-type. AssetModel fulfills via asset
        // checkout, so it rides on the Asset checkout gate; every
        // other type uses its own gate.
        $gateModel = static::class === \App\Models\AssetModel::class
            ? \App\Models\Asset::class
            : static::class;
        if (! auth()->user()?->can('checkout', $gateModel)) {
            return null;
        }

        $count = $this->openRequests()->count();
        if ($count < 2) {
            return null;
        }

        // Availability check varies per type. If nothing's on
        // hand to hand out, hide the link so admins don't land on
        // an empty fulfillment screen.
        if (! $this->hasStockForBulkFulfillment()) {
            return null;
        }

        return [
            'url' => route($routeName, $this->getKey()),
            'count' => $count,
        ];
    }

    /**
     * Per-type availability check for bulk-fulfillment eligibility.
     * Default probes numRemaining() (the qty-tracked types), but
     * License + AssetModel override this in their own model to
     * check seat counts / available assets respectively.
     */
    protected function hasStockForBulkFulfillment(): bool
    {
        return method_exists($this, 'numRemaining')
            && $this->numRemaining() > 0;
    }
}
