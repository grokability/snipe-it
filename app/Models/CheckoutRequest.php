<?php

namespace App\Models;

use App\Enums\CheckoutRequestState;
use App\Exceptions\InvalidCheckoutRequestTransition;
use App\Models\Builders\CheckoutRequestQueryBuilder;
use App\Models\Traits\HasCalendarEvents;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property \App\Enums\CheckoutRequestState $state
 *
 * @mixin IdeHelperCheckoutRequest
 */
class CheckoutRequest extends Model
{
    use HasCalendarEvents;
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'quantity',
        'fulfilled_quantity',
        'start_date',
        'end_date',
        'notes',
        'state',
    ];

    protected $table = 'checkout_requests';

    /**
     * Direct-column search targets for TextSearch (called by
     * Api\CheckoutRequest::index's ?search= handler).
     */
    protected $searchableAttributes = ['notes'];

    /**
     * Relation-scoped search targets. The requester side goes here;
     * the polymorphic requestable side is handled in
     * advancedTextSearch() below since Searchable doesn't understand
     * morphTo.
     */
    protected $searchableRelations = [
        'user' => ['first_name', 'last_name', 'username', 'email'],
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'canceled_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'quantity' => 'integer',
        'fulfilled_quantity' => 'integer',
        'state' => CheckoutRequestState::class,
    ];

    protected $attributes = [
        'state' => 'pending',
        'fulfilled_quantity' => 0,
    ];

    /**
     * Remaining qty on this request. Requested minus fulfilled;
     * capped at 0 so an accidental over-fulfill upstream can't
     * return a negative here. Used by the partial-fulfillment
     * flow to figure out how much of an incoming fulfillment
     * should count against this row.
     */
    public function remainingQuantity(): int
    {
        return max(0, (int) $this->quantity - (int) $this->fulfilled_quantity);
    }

    /**
     * Only pending rows publish a calendar event. Accessors return
     * null once the request has been fulfilled or canceled so the
     * HasCalendarEvents trait's null-check deletes the row on the
     * transition (state is listed in trigger_fields so the observer
     * re-runs the sync when state changes).
     */
    protected function getCalendarStartAttribute(): mixed
    {
        return $this->state === CheckoutRequestState::Pending
            ? $this->start_date
            : null;
    }

    protected function getCalendarEndAttribute(): mixed
    {
        return $this->state === CheckoutRequestState::Pending
            ? $this->end_date
            : null;
    }

    /**
     * Calendar-event URL / color for the requestable this row points
     * at. Reused by CalendarEventsTransformer, which falls back to
     * these methods when a source model isn't wired through the
     * Presenter/Presentable chain (which CheckoutRequest isn't - it
     * extends the plain Eloquent Model, not SnipeModel).
     *
     * Reservations link to the checkout confirmation screen with a
     * `request_id` query hint, so an admin clicking a today-widget
     * row lands on the pre-filled fulfill flow for that specific
     * request. Every checkout controller under Assets / Accessories
     * / Components / Consumables / Licenses honors the hint. Types
     * without a dedicated checkout screen (AssetModel) fall back to
     * the presenter's calendarUrl, which points at the item's show
     * page.
     */
    public function calendarUrl(): ?string
    {
        $item = $this->itemRequested();
        if (! $item) {
            return null;
        }

        $checkoutRoute = match (true) {
            $item instanceof Asset => ['hardware.checkout.create', ['asset' => $item->id]],
            $item instanceof Accessory => ['accessories.checkout.show', ['accessory' => $item->id]],
            $item instanceof Component => ['components.checkout.show', ['componentID' => $item->id]],
            $item instanceof Consumable => ['consumables.checkout.show', ['consumablesID' => $item->id]],
            $item instanceof License => ['licenses.checkout', ['license' => $item->id]],
            // AssetModel reservations point at the multi-fulfill
            // screen where an admin picks specific assets of that
            // model to fulfill the request against.
            $item instanceof AssetModel => ['models.fulfill-requests.create', ['model' => $item->id]],
            default => null,
        };

        if ($checkoutRoute !== null) {
            [$name, $params] = $checkoutRoute;

            return route($name, array_merge($params, ['request_id' => $this->id]));
        }

        if (method_exists($item, 'present')) {
            $presenter = $item->present();
            if (method_exists($presenter, 'calendarUrl')) {
                return $presenter->calendarUrl();
            }
        }

        return null;
    }

    public function calendarColor(): ?string
    {
        $item = $this->itemRequested();
        if ($item && method_exists($item, 'present')) {
            $presenter = $item->present();
            if (method_exists($presenter, 'calendarColor')) {
                return $presenter->calendarColor();
            }
        }

        return null;
    }

    /**
     * Emit the reservation window as a single all-day range event.
     * Uses the calendar_start / calendar_end accessors above so
     * fulfilled + canceled rows drop off the calendar without
     * touching the underlying date columns (which the notification
     * templates and admin queue still read from).
     *
     * @return array<int, array<string, mixed>>
     */
    public function calendarEventDefinitions(): array
    {
        return [
            [
                'field' => 'calendar_start',
                'end_field' => 'calendar_end',
                'event_type' => 'request.reservation',
                'trigger_fields' => ['state', 'start_date', 'end_date'],
                'all_day' => true,
            ],
        ];
    }

    /**
     * True when at least one unit has been fulfilled but the row
     * hasn't been fully satisfied yet. Renders as an in-between
     * badge on the admin queue + requester tab so both parties
     * see "partial" as distinct from "untouched pending".
     */
    public function isPartiallyFulfilled(): bool
    {
        return $this->state === CheckoutRequestState::Pending
            && $this->fulfilled_quantity > 0
            && $this->fulfilled_quantity < $this->quantity;
    }

    /**
     * Keep canceled_at + state in lockstep for the "one field says
     * WHEN, the other says WHAT" pattern the migration comment
     * calls out. Anyone assigning canceled_at directly (factories
     * with legacy `'canceled_at' => now()` shape, older callsites
     * that predate the state machine) gets state=canceled for free
     * so the pending-state scopes stay accurate.
     *
     * fulfilled_at is deliberately NOT mirrored here. FulfillCheckoutRequestAction
     * is the only fulfillment path (via the checkout-hook listener)
     * and sets both explicitly, so a synthetic mutator would just
     * hide bugs where someone reaches around the action.
     */
    protected static function booted(): void
    {
        static::saving(function (self $request): void {
            if ($request->canceled_at !== null
                && $request->state === CheckoutRequestState::Pending
            ) {
                $request->state = CheckoutRequestState::Canceled;
            }
        });
    }

    /**
     * Flip the row to canceled without firing the full model-event
     * cascade (archive-time cleanup paths on Accessory / Component /
     * Consumable rely on this so a parent-entity update does not
     * trigger a storm of per-request notification observers), but
     * still explicitly re-sync the calendar_events index so the
     * reservation drops off the calendar on cancel. The persistent-
     * calendar-event bug reported against the archive and user-
     * initiated cancel paths traced back to a saveQuietly() /
     * query-builder update() combination that bypassed the
     * HasCalendarEvents observer entirely.
     */
    public function cancelAndSync(): bool
    {
        $this->canceled_at = now();
        $this->state = CheckoutRequestState::Canceled;
        $saved = $this->saveQuietly();

        if ($saved) {
            $this->forceSyncCalendarEvents();
        }

        return $saved;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function requestingUser()
    {
        return $this->user()->withTrashed()->first();
    }

    public function requestedItem(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('requestable');
    }

    /**
     * Standard-name morphTo. Same shape as requestedItem() above;
     * exists so Eloquent morph helpers (whereHasMorph, morphEager
     * loads) that infer the relation name from the column pair
     * requestable_type/requestable_id find it under the expected
     * key. Older callers keep using requestedItem() unchanged.
     */
    public function requestable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function itemRequested() // Workaround for laravel polymorphic issue that's not being solved :(
    {
        return $this->requestedItem()->first();
    }

    public function itemType()
    {
        return snake_case(class_basename($this->requestable_type));
    }

    public function location()
    {
        return $this->itemRequested()->location;
    }

    public function name()
    {
        $item = $this->itemRequested();

        if (! $item) {
            return '';
        }

        return $item->display_name;
    }

    /**
     * Accessor mirror of name() so property-style access (`->name`) doesn't
     * fall through Eloquent's __get to `getRelationValue('name')`, which
     * treats the plain name() method as a candidate relation and throws
     * `LogicException: name must return a relationship instance` when it
     * gets a string back. `hasGetMutator('name')` fires first and returns
     * this accessor's value before relation resolution ever runs.
     *
     * The specific symptom is `CalendarEventsTransformer::titleFor()` which
     * coalesces `$source->display_name ?? $source->name ?? ...` for any
     * source without a Presentable presenter (CheckoutRequest doesn't have
     * one); pre-accessor, that crashed the calendar API for any install
     * with a reservation-shape CheckoutRequest on the calendar.
     */
    public function getNameAttribute(): ?string
    {
        return $this->name();
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by', 'id');
    }

    public function checkoutActionlog(): BelongsTo
    {
        return $this->belongsTo(Actionlog::class, 'checkout_actionlog_id', 'id');
    }

    /**
     * Open = still awaiting resolution. Drives the admin queue
     * default filter, the /account/requested tab default, and the
     * Requestable::isRequestedBy() gate. Terminal-state rows (
     * fulfilled, canceled) are excluded.
     */
    public function newEloquentBuilder($query): CheckoutRequestQueryBuilder
    {
        return new CheckoutRequestQueryBuilder($query);
    }

    public function isReservation(): bool
    {
        return $this->start_date !== null || $this->end_date !== null;
    }

    /**
     * Extend TextSearch with the polymorphic requestable side. The
     * standard Searchable trait only understands direct columns and
     * named relations; it has no way to spell "search the .name of
     * whatever requestable this row points at." This override adds
     * an orWhereHasMorph across the six requestable types so a
     * search hits the requestable name, location, category, and
     * company in the same query as the notes / requester side.
     *
     * Per-morph-type branching is needed because:
     *   - Asset uses display_name AND asset_tag AND serial as its
     *     primary identifiers; the rest use `name`.
     *   - Asset's category is nested through model.category; every
     *     other type has a direct category relation.
     *   - AssetModel has no company relation (asset models are
     *     shared across companies), so its branch skips company.
     */
    public function advancedTextSearch(Builder $outer, Builder $nested, array $terms): void
    {
        if (empty($terms)) {
            return;
        }

        $morphTypes = [
            Asset::class,
            AssetModel::class,
            Accessory::class,
            Consumable::class,
            Component::class,
            License::class,
        ];

        $nested->orWhereHasMorph('requestable', $morphTypes, function (Builder $q, string $type) use ($terms): void {
            $q->where(function (Builder $inner) use ($type, $terms): void {
                foreach ($terms as $term) {
                    $like = '%'.$term.'%';

                    // Primary name column varies by type. Asset uses
                    // display_name (computed) so we search the
                    // underlying storage columns instead.
                    if ($type === Asset::class) {
                        $inner->orWhere('name', 'LIKE', $like)
                            ->orWhere('asset_tag', 'LIKE', $like)
                            ->orWhere('serial', 'LIKE', $like);
                    } else {
                        $inner->orWhere('name', 'LIKE', $like);
                    }

                    // Category path is nested through model on Asset,
                    // direct on every other type.
                    if ($type === Asset::class) {
                        $inner->orWhereHas('model.category', fn (Builder $r) => $r->where('name', 'LIKE', $like));
                    } else {
                        $inner->orWhereHas('category', fn (Builder $r) => $r->where('name', 'LIKE', $like));
                    }

                    // Location is only on the physical types (asset,
                    // accessory, consumable, component). License is
                    // intangible (no location); AssetModel is a
                    // template (no location either).
                    if (in_array($type, [Asset::class, Accessory::class, Consumable::class, Component::class], true)) {
                        $inner->orWhereHas('location', fn (Builder $r) => $r->where('name', 'LIKE', $like));
                    }

                    // Company is direct on every FMCS-scoped type
                    // (all except AssetModel, which is shared across
                    // companies).
                    if ($type !== AssetModel::class) {
                        $inner->orWhereHas('company', fn (Builder $r) => $r->where('name', 'LIKE', $like));
                    }
                }
            });
        });
    }

    /**
     * Transition guard. Throws when the caller tries to move a row
     * from a terminal state OR to a state that the current state
     * doesn't permit. Every state-machine Action calls this before
     * writing. Terminal states (fulfilled, canceled) are irreversible.
     */
    public function assertCanTransitionTo(CheckoutRequestState $target): void
    {
        // Same-state writes are a no-op transition and shouldn't
        // throw - callers rely on idempotent semantics for retry
        // safety (e.g. the fulfillment hook firing twice for a race
        // during a rapid double-submit).
        if ($this->state === $target) {
            return;
        }

        if ($this->state->isTerminal()) {
            throw new InvalidCheckoutRequestTransition($this, $this->state, $target);
        }

        // Currently only pending -> fulfilled and pending -> canceled
        // are legal. If more states land later (approved / denied /
        // expired), extend this matrix instead of dropping the guard.
        $legal = [
            CheckoutRequestState::Pending->value => [
                CheckoutRequestState::Fulfilled,
                CheckoutRequestState::Canceled,
            ],
        ];

        $permitted = $legal[$this->state->value] ?? [];
        if (! in_array($target, $permitted, true)) {
            throw new InvalidCheckoutRequestTransition($this, $this->state, $target);
        }
    }

    /**
     * Resolve the context needed to render the "you got here from a
     * /requests row" panel on the various checkout screens. Guards
     * against URL twiddling: the request row has to exist, be open
     * (pending state), target THIS specific requestable (both id and
     * polymorphic type), and belong to a still-existing user. Any
     * miss returns nulls / empty so callers can render nothing.
     *
     * @return array{checkoutRequest: ?self, requestingUserId: ?int, otherPendingRequests: Collection<int, self>}
     */
    public static function contextForCheckout(?int $requestId, string $requestableType, int $requestableId): array
    {
        $empty = [
            'checkoutRequest' => null,
            'requestingUserId' => null,
            'otherPendingRequests' => collect(),
        ];

        if (! $requestId) {
            return $empty;
        }

        $checkoutRequest = static::where('id', $requestId)
            ->where('requestable_id', $requestableId)
            ->where('requestable_type', $requestableType)
            ->pending()
            ->with('user')
            ->first();

        if (! $checkoutRequest || ! $checkoutRequest->user) {
            return $empty;
        }

        // Every other open request for the same requestable, oldest
        // first (waiting-list semantic). Drops the current row and
        // any rows whose requester has since been hard-deleted.
        $otherPendingRequests = static::where('requestable_id', $requestableId)
            ->where('requestable_type', $requestableType)
            ->where('id', '!=', $checkoutRequest->id)
            ->pending()
            ->with('user')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (self $r) => $r->user !== null)
            ->values();

        return [
            'checkoutRequest' => $checkoutRequest,
            'requestingUserId' => $checkoutRequest->user->id,
            'otherPendingRequests' => $otherPendingRequests,
        ];
    }
}
