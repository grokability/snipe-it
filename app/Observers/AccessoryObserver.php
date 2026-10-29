<?php

namespace App\Observers;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\CheckoutRequest;

class AccessoryObserver
{
    /**
     * Listen to the User created event.
     *
     * @return void
     */
    public function updated(Accessory $accessory)
    {
        $changed = [];

        foreach ($accessory->getRawOriginal() as $key => $value) {
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
            if ($accessory->getRawOriginal()[$key] != $accessory->getAttributes()[$key]) {
                $changed[$key]['old'] = $accessory->getRawOriginal()[$key];
                $changed[$key]['new'] = $accessory->getAttributes()[$key];
            }
        }

        if (count($changed) > 0) {
            $logAction = new Actionlog;
            $logAction->item_type = Accessory::class;
            $logAction->item_id = $accessory->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');
        }

        // Archive-time request cleanup.
        if ($accessory->wasChanged('archived') && $accessory->archived) {
            $accessory->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());
        }
    }

    /**
     * Listen to the Accessory created event when
     * a new accessory is created.
     *
     * @return void
     */
    public function created(Accessory $accessory)
    {
        $accessory->writeInitialInventoryCreate();
    }

    /**
     * Listen to the Accessory deleting event. Mirrors the archive-time
     * cancellation in updated() so the two lifecycle transitions
     * have the same posture: open CheckoutRequest rows get canceled
     * so they stop surfacing on /account/requested while the parent
     * is unavailable. The actionlog write preserves the audit trail
     * either way.
     *
     * @return void
     */
    public function deleting(Accessory $accessory)
    {
        $accessory->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());

        $logAction = new Actionlog;
        $logAction->item_type = Accessory::class;
        $logAction->item_id = $accessory->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
