<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CalendarEvent;
use App\Models\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        if (array_key_exists('checkout_counter', $attributes) && array_key_exists('checkout_counter', $attributesOriginal)) {
            $same_checkout_counter = (($attributes['checkout_counter'] == $attributesOriginal['checkout_counter']));
        }

        if (array_key_exists('checkin_counter', $attributes) && array_key_exists('checkin_counter', $attributesOriginal)) {
            $same_checkin_counter = (($attributes['checkin_counter'] == $attributesOriginal['checkin_counter']));
        }

        // Loggable::log_lifecycle_events() now handles suppression of update
        // logs during request-counter changes.
        if (array_key_exists('requests_counter', $attributes) && array_key_exists('requests_counter', $attributesOriginal)) {
            $same_requests_counter = (($attributes['requests_counter'] == $attributesOriginal['requests_counter']));
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
                // intval() rather than cast because $number could be '01234'
                // which would otherwise be read as octal
                $next_asset_tag = intval($number, 10) + 1;

                // Server-side UPDATE so two concurrent asset
                // creates can't clobber each other's catch-up write. If another
                // request already pushed next_auto_tag_base past our
                // value between our read and save, the update matches
                // zero rows and we leave their (higher) value in place.
                // PHP_INT_MAX guard matches the pre-#19744 behavior so an
                // asset tag at the ceiling can't push the base to a value
                // that overflows on the next bump.
                //
                // No $settings->refresh() here. The static Setting cache
                // is a cross-request optimization that survives a
                // DB::transaction rollback in the same PHP process, so
                // mutating its next_auto_tag_base in-place (via refresh
                // or direct model save) leaves subsequent asset-tag
                // reads in that process ahead of the committed DB value
                // on any rollback. autoincrement_asset reads
                // next_auto_tag_base straight from the DB for exactly
                // this reason, so an in-transaction UPDATE is still
                // visible to later reads in the same transaction (#19954).
                if ($next_asset_tag < PHP_INT_MAX) {
                    Setting::where('id', $settings->id)
                        ->where('next_auto_tag_base', '<', $next_asset_tag)
                        ->update(['next_auto_tag_base' => $next_asset_tag]);
                }
            } else {
                // Legacy method. Atomic server-side increment through
                // the query builder so the Setting model instance
                // (static cache) isn't mutated. Same rollback-safety
                // rationale as the modern branch above.
                Setting::where('id', $settings->id)->increment('next_auto_tag_base');
            }
        }

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
        // Sync-adapter identity rows would otherwise become orphans
        // that violate the unique(source, external_id) constraint on
        // the next sync run when the vendor keeps reporting the same
        // host. If the admin later restores the asset the link is gone,
        // and the next sync creates a fresh identity for it.
        DB::table('asset_external_sources')->where('asset_id', $asset->id)->delete();
    }

    /**
     * Restore reservation calendar events when the asset is restored.
     *
     * @return void
     */
    public function restoring(Asset $asset)
    {
        $this->reservationCalendarEventsFor($asset)
            ->whereNotNull('deleted_at')
            ->restore();
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
    }

    private function reservationCalendarEventsFor(Asset $asset)
    {
        // we need this because this is an _intersection_ of two different traits -
        // Requestable, and HasCalendarEvents
        $requestIds = CheckoutRequest::withTrashed()
            ->where('requestable_type', Asset::class)
            ->where('requestable_id', $asset->id)
            ->select('id');

        return CalendarEvent::withTrashed()
            ->where('source_type', CheckoutRequest::class)
            ->whereIn('source_id', $requestIds)
            ->where('event_type', 'request.reservation');
    }
}
