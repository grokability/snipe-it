<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Carbon\Carbon;

class AssetObserver
{
    /**
     * Listen to the Asset updating event. This fires automatically every time an existing asset is saved.
     *
     * @return void
     */
    public function updating(Asset $asset)
    {
        $attributes = $asset->getAttributes();
        $attributesOriginal = $asset->getRawOriginal();
        $same_checkout_counter = false;
        $same_checkin_counter = false;
        $same_requests_counter = false;
        $restoring_or_deleting = false;

        // This is a gross hack to prevent the double logging when restoring an asset
        if (array_key_exists('deleted_at', $attributes) && array_key_exists('deleted_at', $attributesOriginal)) {
            $restoring_or_deleting = (($attributes['deleted_at'] != $attributesOriginal['deleted_at']));
        }

        if (array_key_exists('checkout_counter', $attributes) && array_key_exists('checkout_counter', $attributesOriginal)) {
            $same_checkout_counter = (($attributes['checkout_counter'] == $attributesOriginal['checkout_counter']));
        }

        if (array_key_exists('checkin_counter', $attributes) && array_key_exists('checkin_counter', $attributesOriginal)) {
            $same_checkin_counter = (($attributes['checkin_counter'] == $attributesOriginal['checkin_counter']));
        }

        // requests_counter is a denorm bump fired by CreateCheckoutRequestAction /
        // CancelCheckoutRequestAction. The `requested` and `request canceled`
        // actionlogs those actions write already cover the user-visible history;
        // this gate keeps the counter save from adding a redundant `update` row
        // for the same event.
        if (array_key_exists('requests_counter', $attributes) && array_key_exists('requests_counter', $attributesOriginal)) {
            $same_requests_counter = (($attributes['requests_counter'] == $attributesOriginal['requests_counter']));
        }

        // If the asset isn't being checked out, log the update.
        // (Checkout/checkin/audit actions already create their own log entries; the audit
        // path uses unsetEventDispatcher() so it never reaches this observer.)
        if (array_key_exists('assigned_to', $attributes) && array_key_exists('assigned_to', $attributesOriginal)
            && ($attributes['assigned_to'] == $attributesOriginal['assigned_to'])
            && ($same_checkout_counter) && ($same_checkin_counter) && ($same_requests_counter)
            && ($attributes['last_checkout'] == $attributesOriginal['last_checkout']) && (! $restoring_or_deleting)) {
            $changed = [];

            foreach ($asset->getRawOriginal() as $key => $value) {
                if ((array_key_exists($key, $asset->getAttributes())) && ($asset->getRawOriginal()[$key] != $asset->getAttributes()[$key])) {
                    $changed[$key]['old'] = $asset->getRawOriginal()[$key];
                    $changed[$key]['new'] = $asset->getAttributes()[$key];
                }
            }

            if (empty($changed)) {
                return;
            }

            $logAction = new Actionlog;
            $logAction->item_type = Asset::class;
            $logAction->item_id = $asset->id;
            $logAction->action_date = date('Y-m-d H:i:s');
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');
        }
    }

    /**
     * Listen to the Asset created event, and increment
     * the next_auto_tag_base value in the settings table when i
     * a new asset is created.
     *
     * @return void
     */
    public function created(Asset $asset)
    {
        if ($settings = Setting::getSettings()) {
            $tag = $asset->asset_tag;
            $prefix = (string) ($settings->auto_increment_prefix ?? '');
            $number = substr($tag, strlen($prefix));
            // IF - auto_increment_assets is on, AND (there is no prefix OR the prefix matches the start of the tag)
            //      AND the rest of the string after the prefix is all digits, THEN...
            if ($settings->auto_increment_assets && ($prefix == '' || strpos($tag, $prefix) === 0) && preg_match('/\d+/', $number) === 1) {
                // new way of auto-trueing-up auto_increment ID's
                $next_asset_tag = intval($number, 10) + 1;
                // we had to use 'intval' because the $number could be '01234' and
                // might get interpreted in Octal instead of decimal

                // only modify the 'next' one if it's *bigger* than the stored base
                //
                if ($next_asset_tag > $settings->next_auto_tag_base && $next_asset_tag < PHP_INT_MAX) {
                    $settings->next_auto_tag_base = $next_asset_tag;
                    $settings->save();
                }

            } else {
                // legacy method
                $settings->increment('next_auto_tag_base');
                $settings->save();
            }
        }

        $logAction = new Actionlog;
        $logAction->item_type = Asset::class; // can we instead say $logAction->item = $asset ?
        $logAction->item_id = $asset->id;
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_at = date('Y-m-d H:i:s');
        // See AssetModelObserver::created for the seeder-friendly
        // auth fallback rationale.
        $logAction->created_by = auth()->id() ?? $asset->created_by;
        if ($asset->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');

        // Every new asset is a transaction: supplier, price, currency,
        // date. Record it as Order + OrderItem regardless of whether
        // the operator typed an order_number. Only dedupe on the tuple
        // when a real order_number label is present. A blank label is a
        // distinct transaction each time. Skip if the AssetImporter
        // already wrote the OrderItem for this row.
        $existingLine = OrderItem::where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->exists();

        if (! $existingLine) {
            $orderNumber = trim((string) ($asset->order_number ?? '')) ?: null;

            if ($orderNumber !== null) {
                $order = Order::firstOrNew(
                    [
                        'order_number' => $orderNumber,
                        'supplier_id' => $asset->supplier_id,
                        'company_id' => $asset->company_id,
                    ],
                    [
                        'purchase_date' => $asset->purchase_date,
                    ],
                );
                if (! $order->exists) {
                    $order->created_by = auth()->id();
                    $order->save();
                }
            } else {
                $order = new Order([
                    'order_number' => null,
                    'supplier_id' => $asset->supplier_id,
                    'company_id' => $asset->company_id,
                    'purchase_date' => $asset->purchase_date,
                ]);
                $order->created_by = auth()->id();
                $order->save();
            }

            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'item_type' => Asset::class,
                'item_id' => $asset->id,
                'qty' => 1,
                'price' => $asset->purchase_cost,
            ]);
            $orderItem->created_by = $asset->created_by ?? auth()->id();
            $orderItem->save();
        }
    }

    /**
     * Keep the asset's linked OrderItem (and its parent Order) in
     * step with edits to `purchase_cost` / `order_number` so downstream
     * order-side reads (lastOrderDefaults, order-based reports) don't
     * diverge from the current asset row. Only fires when those
     * specific fields actually change, so unrelated saves stay cheap.
     *
     * Scope note: this handles the two fields the bulk edit form
     * writes on the order-details side. Broader drift on
     * `supplier_id`, `purchase_date`, `company_id`, and the missing
     * per-asset `currency` column are captured in the orders-sync
     * follow-up. See GH #19564.
     */
    public function updated(Asset $asset): void
    {
        if (! $asset->wasChanged(['purchase_cost', 'order_number'])) {
            return;
        }

        $orderItem = OrderItem::where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->first();

        if (! $orderItem) {
            // Legacy assets that predate the orders refactor may not
            // have an OrderItem row. Skip silently — the create-path
            // is the only place that mints one.
            return;
        }

        if ($asset->wasChanged('purchase_cost')) {
            $orderItem->price = $asset->purchase_cost;
            $orderItem->save();
        }

        if ($asset->wasChanged('order_number')) {
            $newNumber = trim((string) ($asset->order_number ?? '')) ?: null;
            $oldOrderId = $orderItem->order_id;

            // Rebuild the (order_number, supplier_id, company_id)
            // dedupe key from the create-path. A blank order_number
            // becomes a distinct Order each time, matching create.
            if ($newNumber !== null) {
                $targetOrder = Order::firstOrNew(
                    [
                        'order_number' => $newNumber,
                        'supplier_id' => $asset->supplier_id,
                        'company_id' => $asset->company_id,
                    ],
                    [
                        'purchase_date' => $asset->purchase_date,
                    ],
                );
                if (! $targetOrder->exists) {
                    $targetOrder->created_by = auth()->id() ?? $asset->created_by;
                    $targetOrder->save();
                }
            } else {
                $targetOrder = new Order([
                    'order_number' => null,
                    'supplier_id' => $asset->supplier_id,
                    'company_id' => $asset->company_id,
                    'purchase_date' => $asset->purchase_date,
                ]);
                $targetOrder->created_by = auth()->id() ?? $asset->created_by;
                $targetOrder->save();
            }

            $orderItem->order_id = $targetOrder->id;
            $orderItem->save();

            // Clean up the previous Order if nothing else points at
            // it. Avoids leaving orphan rows behind when a rename
            // happens to be the only OrderItem on the old Order.
            if ($oldOrderId && $oldOrderId !== $targetOrder->id) {
                $oldOrder = Order::find($oldOrderId);
                if ($oldOrder && $oldOrder->orderItems()->count() === 0) {
                    $oldOrder->delete();
                }
            }
        }
    }

    /**
     * Listen to the Asset deleting event.
     *
     * @return void
     */
    public function deleting(Asset $asset)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Asset::class;
        $logAction->item_id = $asset->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }

    /**
     * Listen to the Asset deleting event.
     *
     * @return void
     */
    public function restoring(Asset $asset)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Asset::class;
        $logAction->item_id = $asset->id;
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('restore');
    }

    /**
     * Executes every time an asset is saved.
     *
     * This matters specifically because any database fields affected here MUST already exist on
     * the assets table (and/or any related models), or related migrations WILL fail.
     *
     * For example, if there is a database migration that's a bit older and modifies an asset, if the save
     * fires before a field gets created in a later migration and that field in the later migration
     * is used in this observer, it doesn't actually exist yet and the migration will break unless we
     * use saveQuietly() in the migration which skips this observer.
     *
     * @see https://github.com/grokability/snipe-it/issues/13723#issuecomment-1761315938
     */
    public function saving(Asset $asset)
    {
        // determine if calculated eol and then calculate it - this should only happen on a new asset
        if (is_null($asset->asset_eol_date) && ! is_null($asset->purchase_date) && ($asset->model?->eol > 0)) {
            $asset->asset_eol_date = $asset->purchase_date->addMonths($asset->model->eol)->format('Y-m-d');
            $asset->eol_explicit = false;
        }

        // determine if explicit and set eol_explicit to true
        if (! is_null($asset->asset_eol_date) && ! is_null($asset->purchase_date)) {
            if ($asset->model?->eol > 0) {
                $months = (int) Carbon::parse($asset->asset_eol_date)->diffInMonths($asset->purchase_date, true);
                if ($months != $asset->model->eol) {
                    $asset->eol_explicit = true;
                }
            }
        } elseif (! is_null($asset->asset_eol_date) && is_null($asset->purchase_date)) {
            $asset->eol_explicit = true;
        }

        if ((! is_null($asset->asset_eol_date)) && (! is_null($asset->purchase_date)) && (is_null($asset->model?->eol) || ($asset->model?->eol == 0))) {
            $asset->eol_explicit = true;
        }
        if ($asset->isDirty(['purchase_date', 'warranty_months'])) {
            $asset->warranty_expires =
                $asset->purchase_date && $asset->warranty_months
                    ? Carbon::parse($asset->purchase_date)
                    ->addMonths((int)$asset->warranty_months)
                    : null;
        }
    }
}
