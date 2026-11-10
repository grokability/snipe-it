<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Helpers\IconHelper;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Response shape for GET /api/v1/requests, the admin-scoped list of
 * pending checkout requests. Mirrors what the /requests
 * Blade view surfaces so an integrator building a mobile / kiosk
 * approval UI has the same fields to work with, AND so the internal
 * admin page can hydrate itself from the same endpoint instead of
 * rendering rows server-side.
 */
class CheckoutRequestsTransformer
{
    public function transformCheckoutRequests(Collection $requests, int $total): array
    {
        $requesterMap = self::pendingRequestersMap($requests);

        $rows = [];
        foreach ($requests as $request) {
            $rows[] = self::transformCheckoutRequest($request, $requesterMap);
        }

        return (new DatatablesTransformer)->transformDatatables($rows, $total);
    }

    public function transformCheckoutRequest(CheckoutRequest $request, array $requesterMap = []): array
    {
        // `requestable` is polymorphic and can point at an Asset, Accessory,
        // Consumable, or Component. Each surfaces its display name through
        // a different attribute, so read the resolved item once and fall
        // back gracefully for models that don't expose display_name. Use
        // itemRequested() (which hydrates the model) rather than
        // requestedItem() (which returns the MorphTo relation itself).
        $item = $request->itemRequested();
        /** @var \App\Models\User|null $requester */
        $requester = $request->user;

        return [
            'id' => (int) $request->id,
            'quantity' => (int) $request->quantity,
            // Lifecycle position. Drives the admin queue's default
            // filter, the requester tab's state badge, and any
            // integrator UI that wants to render pending/fulfilled/
            // canceled differently. Emits both the raw enum value
            // for programmatic filtering and a pre-labeled +
            // pre-colored pair for the JS state-badge formatter so
            // the client doesn't need its own state->label/color map.
            'state' => [
                'value' => $request->state->value,
                'label' => $request->state->label(),
                'color' => $request->state->color(),
            ],
            'fulfilled_at' => $request->fulfilled_at
                ? Helper::getFormattedDateObject($request->fulfilled_at, 'datetime')
                : null,
            'canceled_at' => $request->canceled_at
                ? Helper::getFormattedDateObject($request->canceled_at, 'datetime')
                : null,
            'requested_at' => Helper::getFormattedDateObject($request->created_at, 'datetime'),
            // Reservation window (nullable). Rendered on the admin
            // queue via the presenter's dateDisplayFormatter columns;
            // also carried in the "new request" notification via the
            // matching keys on $data (see ViewAssetsController::
            // getRequestItem for the create + cancel paths).
            'start_date' => $request->start_date
                ? Helper::getFormattedDateObject($request->start_date, 'date')
                : null,
            'end_date' => $request->end_date
                ? Helper::getFormattedDateObject($request->end_date, 'date')
                : null,
            'notes' => $request->notes ? e($request->notes) : null,
            'user' => $requester ? [
                'id' => (int) $requester->id,
                'name' => e($requester->display_name),
                'username' => e($requester->username),
                'email' => e($requester->email),
                'url' => route('users.show', $requester->id),
                'deleted' => (bool) $requester->trashed(),
            ] : null,
            'requestable' => $item ? self::transformRequestable($item, $request) : null,
            'pending_requesters' => self::pendingRequestersFor($request, $requesterMap),
            'available_actions' => self::availableActions($item),
        ];
    }

    /**
     * Batch-build the map of "other open requesters per requestable"
     * for every distinct requestable in the given page. One query per
     * page instead of one query per row - the naive per-row shape is
     * O(N) queries in an admin queue that can easily hit hundreds of
     * rows.
     *
     * The map value is a list of [request_id => user_display_name]
     * pairs so the per-row transformer can drop the row's own
     * requester (see pendingRequestersFor). That leaves the column
     * meaning "who ELSE is waiting on this item" - a request that's
     * the only one for its item renders as an empty column, and a
     * row that has N-1 other pending requests renders those N-1
     * names via the compact ordersSummaryFormatter.
     *
     * @return array<string, array<int, string>> Keyed by "type:id",
     *                                           inner array keyed by CheckoutRequest id.
     */
    private static function pendingRequestersMap(Collection $requests): array
    {
        $keys = $requests
            ->map(fn (CheckoutRequest $r) => [
                'type' => $r->requestable_type,
                'id' => $r->requestable_id,
            ])
            ->filter(fn (array $pair) => $pair['type'] && $pair['id'])
            ->unique(fn (array $pair) => $pair['type'].':'.$pair['id'])
            ->values();

        if ($keys->isEmpty()) {
            return [];
        }

        $query = CheckoutRequest::whereNull('canceled_at')
            ->with('user')
            // Waiting-list semantic: oldest request first. The
            // compact ordersSummaryFormatter renders the first
            // element inline and puts the rest in a tooltip, so the
            // person who queued up earliest is what shows on the row
            // and later joiners fill the tooltip in the order they
            // arrived. Ties (same-second created_at) fall back to id
            // for a stable order.
            ->orderBy('created_at')
            ->orderBy('id');
        $query->where(function ($outer) use ($keys) {
            foreach ($keys as $pair) {
                $outer->orWhere(function ($q) use ($pair) {
                    $q->where('requestable_type', $pair['type'])
                        ->where('requestable_id', $pair['id']);
                });
            }
        });

        $grouped = [];
        foreach ($query->get() as $r) {
            $name = $r->user?->display_name;
            if (! $name) {
                continue;
            }
            $key = $r->requestable_type.':'.$r->requestable_id;
            $grouped[$key][$r->id] = $name;
        }

        return $grouped;
    }

    /**
     * @param  array<string, array<int, string>>  $requesterMap
     * @return array<int, string>
     */
    private static function pendingRequestersFor(CheckoutRequest $request, array $requesterMap): array
    {
        if (! $request->requestable_id || ! $request->requestable_type) {
            return [];
        }

        $key = $request->requestable_type.':'.$request->requestable_id;
        $entries = array_key_exists($key, $requesterMap)
            ? $requesterMap[$key]
            // Single-request callers (transforming one row on its own,
            // e.g. from a detail-page transformer or test) fall through
            // to a one-off query. The page path always pre-populates
            // the map so this path is a correctness fallback, not the
            // hot path.
            : CheckoutRequest::where('requestable_type', $request->requestable_type)
                ->where('requestable_id', $request->requestable_id)
                ->pending()
                ->with('user')
                // Same waiting-list order as the batched path.
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn (CheckoutRequest $r) => [$r->id => $r->user?->display_name])
                ->filter()
                ->all();

        // Exclude the current row's requester so the column reads as
        // "also requested by" - the row's own requester is already
        // rendered in the sibling "Requested By" column.
        unset($entries[$request->id]);

        return array_values($entries);
    }

    /**
     * @return array<string, mixed>
     */
    private static function transformRequestable($item, CheckoutRequest $request): array
    {
        // Only Asset carries expected_checkin / an assignment slot;
        // other requestable types (models, accessories, etc.) skip
        // both fields rather than emit misleading nulls. The show URL
        // comes from the model's presenter->viewUrl(), which every
        // requestable class already ships as part of Snipe-IT's
        // standard presenter convention.
        $isAsset = $item instanceof Asset;

        // Remaining count for the "N remaining" column on /requests
        // and the replenish modal's prefill. Every requestable
        // type gets a value:
        //   - Asset: 1 when unassigned, 0 when checked out
        //     (inherently 1:1 - the asset itself is either
        //     available or not)
        //   - AssetModel: pool count via availableAssets()
        //     (models don't have their own numRemaining)
        //   - Accessory / Consumable / Component / License: their
        //     numRemaining() (qty / seat count)
        if ($item instanceof Asset) {
            $remainingCount = empty($item->assigned_to) ? 1 : 0;
        } elseif ($item instanceof AssetModel) {
            $remainingCount = (int) $item->availableAssets()->count();
        } elseif (method_exists($item, 'numRemaining')) {
            $remainingCount = (int) $item->numRemaining();
        } else {
            $remainingCount = null;
        }

        // Font Awesome class the JS name-formatter prefixes so the
        // admin queue reads at a glance which polymorphic type each
        // row is (barcode for assets, keyboard for accessories,
        // boxes for models, etc.). Sourced through IconHelper so
        // this stays a single source of truth instead of drifting
        // from the app's other icon-per-type callsites. The key
        // strings are the ones IconHelper's switch accepts (see the
        // 'assetModel' / 'accessory' / etc. cases).
        $iconKey = match ($request->requestable_type) {
            AssetModel::class => 'assetModel',
            Accessory::class => 'accessory',
            Consumable::class => 'consumable',
            Component::class => 'component',
            License::class => 'license',
            default => 'asset',
        };

        return [
            'id' => (int) $item->id,
            'type' => class_basename($request->requestable_type),
            'icon' => IconHelper::icon($iconKey),
            'name' => e($item->display_name ?? $item->name ?? ''),
            'image' => method_exists($item, 'getImageUrl') ? $item->getImageUrl() : null,
            'url' => method_exists($item, 'present') ? $item->present()->viewUrl() : null,
            'location' => $item->location ? [
                'id' => (int) $item->location->id,
                'name' => e($item->location->name),
            ] : null,
            // Category is direct on AssetModel/Accessory/Consumable/
            // Component/License but nested through the model on
            // Asset. Resolve via a small polymorphic lookup so the
            // API surfaces the same {id,name} shape for every type.
            'category' => self::resolveCategory($item),
            // Company is direct on Asset/Accessory/Consumable/
            // Component/License. AssetModel has no company relation
            // (models are shared across companies) so its rows emit
            // null here. Same {id,name} shape as location/category.
            'company' => ($item->company ?? null) ? [
                'id' => (int) $item->company->id,
                'name' => e($item->company->name),
            ] : null,
            'expected_checkin' => $isAsset && $item->expected_checkin
                ? Helper::getFormattedDateObject($item->expected_checkin, 'datetime')
                : null,
            'assigned' => $isAsset ? ! empty($item->assigned_to) : null,
            'remaining' => $remainingCount,
            // Custom-field values flagged for the requestable list.
            // Only asset rows have per-instance custom-field values
            // (via the model's fieldset), so every other requestable
            // type emits an empty object here. The presenter emits
            // one column per requestable-flagged field, and the JS
            // formatter renders row.custom_fields.<db_column> when
            // present, empty otherwise - so non-asset rows just show
            // blanks for those columns.
            'custom_fields' => self::customFieldsFor($item),
        ];
    }

    /**
     * Custom-field values for a requestable. Only Asset has per-
     * instance fields (via its model's fieldset); every other
     * requestable type returns an empty stdClass so the JSON
     * response carries `{}` for consistent shape (an empty array
     * would serialize to `[]` and break formatters that dot-walk
     * `row.custom_fields.foo`). Encrypted + non-requestable-flagged
     * fields are omitted so admin custom fields (SSNs etc) don't
     * leak into the /requests queue when a random admin brings up
     * their column-picker.
     *
     * @return array<string, string>|\stdClass
     */
    private static function customFieldsFor($item): array|\stdClass
    {
        if (! $item instanceof Asset || ! $item->custom_fieldset) {
            return new \stdClass;
        }

        $values = [];
        foreach ($item->custom_fieldset->fields as $field) {
            if ($field->field_encrypted == '1' || $field->show_in_requestable_list != '1') {
                continue;
            }
            $raw = $item->{$field->db_column};
            if (in_array($field->format, ['DATE', 'DATETIME'], true) && $raw !== null && $raw !== '') {
                $raw = Helper::getFormattedDateObject(
                    $raw,
                    $field->format === 'DATETIME' ? 'datetime' : 'date',
                    false,
                );
            }
            $values[$field->db_column] = $field->element === 'markdown-textarea'
                ? Helper::renderMarkdown($raw)
                : e($raw);
        }

        return $values ?: new \stdClass;
    }

    /**
     * Resolve the category for any requestable. Asset stores it via
     * the parent AssetModel; every other type (AssetModel /
     * Accessory / Consumable / Component / License) exposes it
     * directly. Returns the standard {id, name} shape the JS
     * category formatter expects, or null when the item has no
     * category or the relation isn't loaded.
     *
     * @return ?array{id: int, name: string}
     */
    private static function resolveCategory($item): ?array
    {
        $category = $item instanceof Asset
            ? $item->model?->category
            : ($item->category ?? null);

        if (! $category) {
            return null;
        }

        return [
            'id' => (int) $category->id,
            'name' => e($category->name),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function availableActions($item): array
    {
        $cancelable = $item !== null;

        // Replenish is only meaningful for qty-tracked types (they're
        // the ones the adjust-quantity modal is wired for). Gate on
        // the caller's update perm for the specific model class so
        // an admin who can't edit consumables doesn't see the button
        // on a consumable request row. The server side re-authorizes
        // on POST anyway; this flag is just a UI hint.
        $replenishable = false;
        if ($item instanceof Accessory
            || $item instanceof Consumable
            || $item instanceof Component
        ) {
            $replenishable = Gate::allows('update', $item);
        }

        // Checkout availability per requestable type:
        //   - Asset: unassigned = show Checkout; assigned = show Checkin.
        //   - Accessory / Consumable / Component: qty-tracked, so
        //     Checkout when numRemaining() > 0 (nothing to check out
        //     if fully depleted).
        //   - License: seat-tracked, so Checkout when free_seats_count > 0.
        //   - AssetModel: template row (not a concrete unit), so no
        //     checkout affordance from the queue at all - admin picks
        //     a specific asset via the model's own show page.
        // Server-side re-authorizes on the checkout POST, so this
        // flag is a UI hint only.
        $canCheckout = false;
        $canCheckin = false;
        if ($item instanceof Asset) {
            $canCheckout = empty($item->assigned_to);
            $canCheckin = ! empty($item->assigned_to);
        } elseif ($item instanceof Accessory || $item instanceof Consumable || $item instanceof Component) {
            $canCheckout = $item->numRemaining() > 0;
        } elseif ($item instanceof License) {
            // numRemaining counts free (unassigned + not-to-asset)
            // seats; more reliable than free_seats_count which
            // isn't eager-loaded by the /requests endpoint.
            $canCheckout = $item->numRemaining() > 0;
        } elseif ($item instanceof \App\Models\AssetModel) {
            // AssetModel fulfills via handing out a specific Asset
            // of the model. Show the checkout button whenever the
            // model has at least one available (RTD) asset - the
            // bulk-fulfill screen handles the per-row picker.
            $canCheckout = $item->availableAssets()->exists();
        }

        return [
            // Cancel-by-admin is available whenever the item still
            // exists on the row. The action itself re-authorizes on
            // the server side, so this flag is only a UI hint.
            'cancel' => $cancelable,
            'replenish' => $replenishable,
            'checkout' => $canCheckout,
            'checkin' => $canCheckin,
            // Bulk-cancel widget reads this. Row gets checkbox-enabled
            // iff at least one bulk_selectable entry is true; keeping
            // the shape as a sub-array so future bulk actions (approve,
            // deny, ...) can grow without breaking the existing wiring.
            'bulk_selectable' => [
                'cancel' => $cancelable,
            ],
        ];
    }
}
