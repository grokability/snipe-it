<?php

namespace App\Http\Transformers;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Models\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ComponentsTransformer
{
    public function transformComponents(Collection $components, $total)
    {
        $array = [];
        foreach ($components as $component) {
            $array[] = self::transformComponent($component);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformComponent(Component $component)
    {
        // See AccessoriesTransformer for the last-acquisition-with-fallback
        // resolution pattern for supplier / purchase_date / purchase_cost.
        $lastDefaults = $component->lastOrderDefaults();
        $lastSupplier = $component->lastAcquisitionSupplier();

        $array = [
            'id' => (int) $component->id,
            'name' => e($component->name),
            'image' => ($component->image) ? Storage::disk('public')->url(FileStorage::Components->publicPath().e($component->image)) : null,
            'qr_code_url' => route('qr_code/common', ['object_type' => 'components', 'id' => $component->id]),
            'serial' => ($component->serial) ? e($component->serial) : null,
            'location' => ($component->location) ? [
                'id' => (int) $component->location->id,
                'name' => e($component->location->name),
                'tag_color' => $component->location->tag_color ? e($component->location->tag_color) : null,
            ] : null,
            'qty' => ($component->qty != '') ? (int) $component->qty : null,
            'min_amt' => ($component->min_amt != '') ? (int) $component->min_amt : null,
            'category' => ($component->category) ? [
                'id' => (int) $component->category->id,
                'name' => e($component->category->name),
                'tag_color' => $component->category->tag_color ? e($component->category->tag_color) : null,
            ] : null,
            'supplier' => $lastSupplier ? [
                'id' => $lastSupplier->id,
                'name' => e($lastSupplier->name),
                'tag_color' => $lastSupplier->tag_color ? e($lastSupplier->tag_color) : null,
            ] : null,
            'manufacturer' => ($component->manufacturer) ? [
                'id' => $component->manufacturer->id,
                'name' => e($component->manufacturer->name),
                'tag_color' => $component->manufacturer->tag_color ? e($component->manufacturer->tag_color) : null,
            ] : null,
            'model_number' => ($component->model_number) ? e($component->model_number) : null,
            // See AccessoriesTransformer for why order_number is no longer
            // in the parent-level output.
            //
            // Distinct order numbers this component has been purchased on,
            // pulled from the eager-loaded orderItems.order relation
            // (Api\ComponentsController::index preloads it). Feeds the
            // datatable's ordersSummaryFormatter.
            'orders' => $component->orderItems->pluck('order.order_number')->filter()->unique()->values()->all(),
            'purchase_date' => ($lastDefaults['purchase_date'] ?? null) ? Helper::getFormattedDateObject($lastDefaults['purchase_date'], 'date') : null,
            'purchase_cost' => Helper::formatCurrencyOutput($lastDefaults['unit_cost'] ?? null),
            'total_cost' => Helper::formatCurrencyOutput($component->totalCostSum()),
            'remaining' => (int) $component->numRemaining(),
            'percent_remaining' => round($component->percentRemaining()),
            'company' => ($component->company) ? [
                'id' => (int) $component->company->id,
                'name' => e($component->company->name),
                'tag_color' => $component->company->tag_color ? e($component->company->tag_color) : null,
            ] : null,
            'notes' => ($component->notes) ? Helper::parseEscapedMarkedownInline($component->notes) : null,
            'requestable' => (bool) $component->requestable,
            'archived' => (bool) $component->archived,
            'created_by' => ($component->adminuser) ? [
                'id' => (int) $component->adminuser->id,
                'name' => e($component->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($component->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($component->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($component->deleted_at, 'datetime'),
            'user_can_checkout' => ($component->numRemaining() > 0) ? 1 : 0,
        ];

        // See AccessoriesTransformer for the assigned_to_self /
        // available_actions.request/cancel rationale. relationLoaded
        // gate keeps the standard-index path from firing an N+1 (only
        // the requestable() endpoint preloads `requests`).
        $userHasOpenRequest = auth()->check() && $component->relationLoaded('requests') && $component->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );
        $permissions_array = [];
        $permissions_array['assigned_to_self'] = $userHasOpenRequest;

        // Soft-deleted rows surface in the "Deleted" index view with
        // their write-ish actions rendered disabled, not hidden, by
        // the per-row formatter. Keep the policy-gated flags true so
        // the disabled stand-ins render. The formatter inspects
        // row.deleted_at to flip them to their disabled state.
        $isDeleted = $component->deleted_at !== null;
        $canUpdate = Gate::allows('update', $component);
        $canDelete = $component->isDeletable();

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', $component),
            'checkin' => Gate::allows('checkin', $component),
            'update' => $canUpdate && ! $isDeleted,
            'adjust_quantity' => $canUpdate,
            // Cloning a deleted component is explicitly allowed. It
            // produces a brand-new row and leaves the deleted parent
            // untouched, so it's safe as a restore-and-tweak shortcut.
            'clone' => Gate::allows('clone', $component),
            'delete' => $canDelete,
            // Top-level so genericActionsFormatter renders the per-row
            // restore button on the Deleted index. The bulk_selectable
            // mirror below drives the bulk-actions dropdown.
            'restore' => $isDeleted && Gate::allows('delete', $component),
            'request' => (bool) $component->requestable && ! $isDeleted && ! $userHasOpenRequest,
            'cancel' => (bool) $component->requestable && $userHasOpenRequest,
            // Dropdown-action gating: the bulk-actions dropdown asks
            // "can this row take THIS action?" and disables the
            // checkbox when the answer is no (see
            // applyActionDependentRowStyling + checkboxEnabledFormatter
            // in partials/bootstrap-table). Keys must match the
            // bulk_actions dropdown values on the components index.
            'bulk_selectable' => [
                // Edit and Delete only make sense for live rows. On
                // soft-deleted rows the bulk-actions dropdown offers
                // Restore instead, so flip both off here to keep the
                // dropdown's auto-uncheck guard honest.
                'edit' => $canUpdate && ! $isDeleted,
                'delete' => $canDelete && ! $isDeleted,
                // Restore is only meaningful on the Deleted index.
                // Treating it as a delete-tier action (same policy
                // choice as postRestore / AssetModelsController).
                'restore' => $isDeleted && Gate::allows('delete', $component),
            ],
        ];
        $array += $permissions_array;

        return $array;
    }

    public function transformRequestedComponents(Collection $components, $total)
    {
        $array = [];
        foreach ($components as $component) {
            $array[] = self::transformRequestedComponent($component);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRequestedComponent(Component $component)
    {
        $userHasOpenRequest = auth()->check() && $component->relationLoaded('requests') && $component->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );

        return [
            'id' => (int) $component->id,
            'name' => e($component->name),
            'image' => ($component->image) ? Storage::disk('public')->url(FileStorage::Components->publicPath().e($component->image)) : null,
            'serial' => ($component->serial) ? e($component->serial) : null,
            'model_number' => ($component->model_number) ? e($component->model_number) : null,
            'location' => ($component->location) ? [
                'id' => (int) $component->location->id,
                'name' => e($component->location->name),
                'tag_color' => $component->location->tag_color ? e($component->location->tag_color) : null,
            ] : null,
            'qty' => ($component->qty != '') ? (int) $component->qty : null,
            'remaining' => (int) $component->numRemaining(),
            'requestable' => (bool) $component->requestable,
            'assigned_to_self' => $userHasOpenRequest,
            'available_actions' => [
                'request' => (bool) $component->requestable && ! $userHasOpenRequest,
                'cancel' => (bool) $component->requestable && $userHasOpenRequest,
            ],
        ];
    }

    public function transformCheckedoutComponents(Collection $components_assets, $total)
    {
        $array = [];
        foreach ($components_assets as $asset) {
            $array[] = [
                // `id` as the pivot id so bs-table's row-id tracking
                // populates the ids[] array sent by bulk-select with
                // the pivot keys the bulk-checkin endpoint expects.
                // assigned_pivot_id is kept as a legacy duplicate
                // because the single-row checkin button formatter
                // reads it by name.
                'id' => (int) $asset->pivot->id,
                'assigned_pivot_id' => (int) $asset->pivot->id,
                'name' => $this->transformAssignedTo($asset),
                'qty' => $asset->pivot->assigned_qty,
                'assigned_qty' => $asset->pivot->assigned_qty,
                'note' => ($asset->pivot->note) ? e($asset->pivot->note) : null,
                'created_at' => Helper::getFormattedDateObject($asset->pivot->created_at, 'datetime'),
                'available_actions' => [
                    'checkin' => true,
                    'bulk_selectable' => [
                        'checkin' => true,
                    ],
                ],
            ];
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAssignedTo($componentCheckout)
    {
        return (new AssetsTransformer)->transformAssetCompact($componentCheckout);
    }
}
