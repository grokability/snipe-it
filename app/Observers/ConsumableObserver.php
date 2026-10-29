<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\CheckoutAcceptance;
use App\Models\CheckoutRequest;
use App\Models\Consumable;

class ConsumableObserver
{
    /**
     * Listen to the User created event.
     *
     * @return void
     */
    public function updated(Consumable $consumable)
    {

        $changed = [];

        foreach ($consumable->getRawOriginal() as $key => $value) {
            if ($key === 'updated_at') {
                continue;
            }
            // SoftDeletes drives the delete + restore flows through
            // save(), which fires `updated` with deleted_at as the
            // changed field. The `deleting`/`restored` observers log
            // those actions directly, so excluding deleted_at here
            // keeps us from double-logging a "delete" plus an "update"
            // row that only shows the deleted_at diff.
            if ($key === 'deleted_at') {
                continue;
            }
            if ($consumable->getRawOriginal()[$key] != $consumable->getAttributes()[$key]) {
                $changed[$key]['old'] = $consumable->getRawOriginal()[$key];
                $changed[$key]['new'] = $consumable->getAttributes()[$key];
            }
        }

        if (count($changed) > 0) {
            $logAction = new Actionlog;
            $logAction->item_type = Consumable::class;
            $logAction->item_id = $consumable->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');
        }

        // Archive-time request cleanup.
        if ($consumable->wasChanged('archived') && $consumable->archived) {
            $consumable->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());
        }
    }

    /**
     * Listen to the Consumable created event when
     * a new consumable is created.
     *
     * @return void
     */
    public function created(Consumable $consumable)
    {
        $consumable->writeInitialInventoryCreate();
    }

    /**
     * Listen to the Consumable deleting event.
     *
     * Soft-delete preserves pivot history, uploads, and the image so a
     * restore brings the item back to the pre-delete state. The
     * destructive actions here are limited to lifecycle records that
     * admins and users can't act on while the parent is soft-deleted:
     * pending CheckoutAcceptance rows (post-checkout acknowledgment)
     * and open CheckoutRequest rows (pre-checkout wish list) both
     * get cleared. The CheckoutRequest cleanup mirrors the archive
     * flow in updated() so both lifecycle transitions have the same
     * posture toward pending requests. Pivot rows, uploads, and the
     * image are cleaned up by the Consumable model's forceDeleted
     * hook so the lifecycle data survives soft-delete + restore.
     *
     * @return void
     */
    public function deleting(Consumable $consumable)
    {
        CheckoutAcceptance::pending()
            ->where('checkoutable_type', Consumable::class)
            ->where('checkoutable_id', $consumable->id)
            ->delete();

        $consumable->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());

        $logAction = new Actionlog;
        $logAction->item_type = Consumable::class;
        $logAction->item_id = $consumable->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
