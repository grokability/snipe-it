<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Consumable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class ConsumablesTransformer
{
    public function transformConsumables(Collection $consumables, $total)
    {
        $array = [];
        foreach ($consumables as $consumable) {
            $array[] = self::transformConsumable($consumable);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformConsumable(Consumable $consumable)
    {
        // See AccessoriesTransformer for the last-acquisition-with-fallback
        // resolution pattern for supplier / purchase_date / purchase_cost.
        $lastDefaults = $consumable->lastOrderDefaults();
        $lastSupplier = $consumable->lastAcquisitionSupplier();

        $array = [
            'id' => (int) $consumable->id,
            'name' => e($consumable->name),
            'image' => ($consumable->getImageUrl()) ? ($consumable->getImageUrl()) : null,
            'qr_code_url' => route('qr_code/common', ['object_type' => 'consumables', 'id' => $consumable->id]),
            'category' => ($consumable->category) ? [
                'id' => $consumable->category->id,
                'name' => e($consumable->category->name),
                'tag_color' => $consumable->category->tag_color ? e($consumable->category->tag_color) : null,
            ] : null,
            'company' => ($consumable->company) ? [
                'id' => (int) $consumable->company->id,
                'name' => e($consumable->company->name),
                'tag_color' => $consumable->company->tag_color ? e($consumable->company->tag_color) : null,
            ] : null,
            'item_no' => e($consumable->item_no),
            'location' => ($consumable->location) ? [
                'id' => (int) $consumable->location->id,
                'name' => e($consumable->location->name),
                'tag_color' => $consumable->location->tag_color ? e($consumable->location->tag_color) : null,
            ] : null,
            'manufacturer' => ($consumable->manufacturer) ? [
                'id' => (int) $consumable->manufacturer->id,
                'name' => e($consumable->manufacturer->name),
                'tag_color' => $consumable->manufacturer->tag_color ? e($consumable->manufacturer->tag_color) : null,
            ] : null,
            'supplier' => $lastSupplier ? [
                'id' => $lastSupplier->id,
                'name' => e($lastSupplier->name),
                'tag_color' => $lastSupplier->tag_color ? e($lastSupplier->tag_color) : null,
            ] : null,
            'min_amt' => (int) $consumable->min_amt,
            'model_number' => ($consumable->model_number != '') ? e($consumable->model_number) : null,
            'remaining' => $consumable->numRemaining(),
            'checked_out' => $consumable->numOutstanding(),
            'consumed' => $consumable->numConsumed(),
            'percent_remaining' => round($consumable->percentRemaining()),
            // See AccessoriesTransformer for why order_number is no longer
            // in the parent-level output.
            //
            // Distinct order numbers this consumable has been purchased on,
            // pulled from the eager-loaded orderItems.order relation
            // (Api\ConsumablesController::index preloads it). Feeds the
            // datatable's ordersSummaryFormatter.
            'orders' => $consumable->orderItems->pluck('order.order_number')->filter()->unique()->values()->all(),
            'purchase_cost' => Helper::formatCurrencyOutput($lastDefaults['unit_cost'] ?? null),
            'total_cost' => Helper::formatCurrencyOutput($consumable->totalCostSum()),
            'purchase_date' => ($lastDefaults['purchase_date'] ?? null) ? Helper::getFormattedDateObject($lastDefaults['purchase_date'], 'date') : null,
            'qty' => (int) $consumable->qty,
            'notes' => ($consumable->notes) ? Helper::parseEscapedMarkedownInline($consumable->notes) : null,
            'requestable' => (bool) $consumable->requestable,
            'archived' => (bool) $consumable->archived,
            'created_by' => ($consumable->adminuser) ? [
                'id' => (int) $consumable->adminuser->id,
                'name' => e($consumable->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($consumable->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($consumable->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($consumable->deleted_at, 'datetime'),
        ];

        $permissions_array = [];
        $permissions_array['user_can_checkout'] = false;

        if ($consumable->numRemaining() > 0) {
            $permissions_array['user_can_checkout'] = true;
        }

        // See AccessoriesTransformer for the assigned_to_self /
        // available_actions.request/cancel rationale. relationLoaded
        // gate keeps the standard-index path from firing an N+1 (only
        // the requestable() endpoint preloads `requests`).
        $userHasOpenRequest = auth()->check() && $consumable->relationLoaded('requests') && $consumable->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );
        $permissions_array['assigned_to_self'] = $userHasOpenRequest;

        // Soft-deleted rows surface in the Deleted index view with
        // their write-ish actions rendered disabled, not hidden, by
        // the per-row formatter. Keep the policy-gated flags true so
        // the disabled stand-ins render. The formatter inspects
        // row.deleted_at to flip them to their disabled state.
        $isDeleted = $consumable->deleted_at !== null;
        $canUpdate = Gate::allows('update', $consumable);
        $canDelete = $consumable->isDeletable();

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', $consumable),
            'checkin' => Gate::allows('checkin', $consumable),
            'update' => $canUpdate && ! $isDeleted,
            'adjust_quantity' => $canUpdate,
            'delete' => $canDelete,
            // Cloning a deleted consumable is explicitly allowed. It
            // produces a brand-new row and leaves the deleted parent
            // untouched, so it's safe as a restore-and-tweak shortcut.
            'clone' => Gate::allows('clone', $consumable),
            // Top-level so genericActionsFormatter renders the per-row
            // restore button on the Deleted index. The bulk_selectable
            // mirror below drives the bulk-actions dropdown.
            'restore' => $isDeleted && Gate::allows('delete', $consumable),
            'request' => (bool) $consumable->requestable && ! $isDeleted && ! $userHasOpenRequest,
            'cancel' => (bool) $consumable->requestable && $userHasOpenRequest,
            'bulk_selectable' => [
                // Edit/delete only make sense for live rows. On soft-
                // deleted rows the bulk-actions dropdown offers Restore.
                'edit' => $canUpdate && ! $isDeleted,
                'delete' => $canDelete && ! $isDeleted,
                // Restore is only meaningful on the Deleted index.
                // Delete-tier action (matches postRestore pattern).
                'restore' => $isDeleted && Gate::allows('delete', $consumable),
            ],
        ];
        $array += $permissions_array;

        return $array;
    }

    public function transformRequestedConsumables(Collection $consumables, $total)
    {
        $array = [];
        foreach ($consumables as $consumable) {
            $array[] = self::transformRequestedConsumable($consumable);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRequestedConsumable(Consumable $consumable)
    {
        $userHasOpenRequest = auth()->check() && $consumable->relationLoaded('requests') && $consumable->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );

        return [
            'id' => (int) $consumable->id,
            'name' => e($consumable->name),
            'image' => ($consumable->getImageUrl()) ? ($consumable->getImageUrl()) : null,
            'model_number' => ($consumable->model_number != '') ? e($consumable->model_number) : null,
            'location' => ($consumable->location) ? [
                'id' => (int) $consumable->location->id,
                'name' => e($consumable->location->name),
                'tag_color' => $consumable->location->tag_color ? e($consumable->location->tag_color) : null,
            ] : null,
            'qty' => (int) $consumable->qty,
            'remaining' => $consumable->numRemaining(),
            'checked_out' => $consumable->numOutstanding(),
            'consumed' => $consumable->numConsumed(),
            'requestable' => (bool) $consumable->requestable,
            'assigned_to_self' => $userHasOpenRequest,
            'available_actions' => [
                'request' => (bool) $consumable->requestable && ! $userHasOpenRequest,
                'cancel' => (bool) $consumable->requestable && $userHasOpenRequest,
            ],
        ];
    }

    public function transformCheckedoutConsumables(Collection $consumables_users, $total)
    {
        $array = [];
        foreach ($consumables_users as $user) {
            $array[] = (new UsersTransformer)->transformUser($user);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }
}
