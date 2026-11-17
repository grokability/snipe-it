<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\OrderItemsTransformer;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Standard REST endpoint for OrderItems. Backs the Orders tab on
 * accessory / consumable / component / model view pages, and any
 * external API consumer that needs to page / filter / sort acquisition
 * lines directly rather than through their parent inventory item.
 *
 * Filters:
 *   item_type       fully-qualified model class ("App\Models\Accessory")
 *   item_id         id of that item
 *   asset_model_id  aggregate: all OrderItems for every Asset of this model
 *
 * Sort covers OrderItem-native columns and cross-table columns
 * (order_number, purchase_date, supplier, currency) via a leftJoin
 * onto orders + suppliers. Search hits order_number, purchase_order,
 * and suppliers.name across the same joined query.
 */
class OrderItemsController extends Controller
{
    /**
     * Sortable columns that live on joined tables (orders / suppliers).
     * Keys are the API `sort` values; values are the qualified column
     * name the ORDER BY uses. Non-mapped values fall back to `id DESC`.
     */
    private const SORT_COLUMN_MAP = [
        'order_number' => 'orders.order_number',
        'purchase_date' => 'orders.purchase_date',
        'currency' => 'orders.currency',
        'supplier' => 'suppliers.name',
        'qty' => 'order_items.qty',
        'unit_cost' => 'order_items.price',
        'created_at' => 'order_items.created_at',
        'created_by' => 'order_items.created_by',
    ];

    public function index(Request $request): JsonResponse
    {
        $this->authorizeOrderItemsRequest($request);

        $query = $this->baseOrderItemsQuery();
        $this->applyScopeFilters($query, $request);
        $this->applyJoinsAndSearch($query, $request);
        $this->applySort($query, $request);

        $total = (clone $query)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $lines = (clone $query)->skip($offset)->take($limit)->get();

        return response()->json(
            (new OrderItemsTransformer)->transformOrderItems($lines, $total),
            200,
            ['Content-Type' => 'application/json;charset=utf8'],
            JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * Authorization: if the request narrows to a specific parent,
     * require view permission on THAT parent. Otherwise fall back to
     * requiring superuser — unfiltered "list every OrderItem" is a
     * superuser-only capability until an OrderPolicy exists.
     */
    private function authorizeOrderItemsRequest(Request $request): void
    {
        $assetModelId = $request->input('asset_model_id');
        $itemType = $request->input('item_type');
        $itemId = $request->input('item_id');

        if ($assetModelId) {
            $this->authorize('view', AssetModel::findOrFail($assetModelId));

            return;
        }

        if ($itemType && $itemId && class_exists($itemType)) {
            $this->authorize('view', $itemType::findOrFail($itemId));

            return;
        }

        if (! auth()->user()?->isSuperUser()) {
            abort(403);
        }
    }

    private function baseOrderItemsQuery(): Builder
    {
        return OrderItem::query()
            ->with([
                'admin:id,first_name,last_name,username',
                'order:id,order_number,supplier_id,currency,purchase_date,notes,created_by',
                'order.supplier:id,name',
                'order.admin:id,first_name,last_name,username',
                // action_log carries the receipt filename uploaded via
                // the adjust-quantity modal; surfaced on each Orders-tab
                // row so the file lives with the acquisition record
                // rather than only in the item's Files tab.
                'actionlog:id,order_item_id,filename,item_id,item_type',
            ])
            // Orders tab shows purchases only. Corrections / consumption
            // events (zero or negative-qty OrderItems) belong in the
            // item's history tab, not on the "Orders" list.
            ->where('order_items.qty', '>', 0)
            // Drop rows whose parent Order has no order_number. Those
            // are initial-inventory anchors (writeInitialOrderIfStocked)
            // or seeded placeholders without a real acquisition
            // reference. Including them makes the Orders tab show
            // rows that look blank to the admin and inflates the
            // tab badge past what the user considers a real order.
            ->whereHas('order', fn ($q) => $q->whereNotNull('order_number'));
    }

    private function applyScopeFilters(Builder $query, Request $request): void
    {
        $assetModelId = $request->input('asset_model_id');
        $itemType = $request->input('item_type');
        $itemId = $request->input('item_id');

        if ($assetModelId) {
            $query->where('order_items.item_type', Asset::class)
                ->whereIn('order_items.item_id', Asset::where('model_id', $assetModelId)->select('id'));

            return;
        }

        if ($itemType && $itemId) {
            $query->where('order_items.item_type', $itemType)
                ->where('order_items.item_id', $itemId);
        }
    }

    /**
     * leftJoin onto orders + suppliers so cross-table sort / search
     * can hit those columns. Select order_items.* so the model hydrates
     * cleanly and the joined columns don't leak into the OrderItem
     * attributes. Search covers order_number, supplier name, and the
     * order notes field.
     */
    private function applyJoinsAndSearch(Builder $query, Request $request): void
    {
        $query->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'orders.supplier_id')
            ->select('order_items.*');

        if (! $request->filled('search')) {
            return;
        }

        $needle = '%'.$request->input('search').'%';
        $query->where(function ($q) use ($needle) {
            $q->where('orders.order_number', 'like', $needle)
                ->orWhere('suppliers.name', 'like', $needle)
                ->orWhere('orders.notes', 'like', $needle);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = $request->input('sort');

        if ($sort === 'total_cost') {
            // qty * price sort is computed rather than a stored column.
            $query->orderByRaw('(order_items.qty * COALESCE(order_items.price, 0)) '.$order);

            return;
        }

        $column = self::SORT_COLUMN_MAP[$sort] ?? null;
        if ($column === null) {
            $query->orderBy('order_items.id', 'desc');

            return;
        }

        $query->orderBy($column, $order);
    }
}
