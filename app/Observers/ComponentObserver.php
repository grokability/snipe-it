<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\CheckoutRequest;
use App\Models\Component;

class ComponentObserver
{
    /**
     * Listen to the User created event.
     *
     * @return void
     */
    public function updated(Component $component)
    {

        $changed = [];

        foreach ($component->getRawOriginal() as $key => $value) {
            if ($key === 'updated_at') {
                continue;
            }
            // SoftDeletes drives the delete + restore flows through
            // save(), which fires `updated` with deleted_at as the
            // changed field. The `deleting`/`restored` observers log
            // those actions directly, so excluding deleted_at here
            // keeps us from double-logging ("delete" plus an "update"
            // row that only shows the deleted_at diff).
            if ($key === 'deleted_at') {
                continue;
            }

            // Check and see if the value changed
            if ($component->getRawOriginal()[$key] != $component->getAttributes()[$key]) {
                $changed[$key]['old'] = $component->getRawOriginal()[$key];
                $changed[$key]['new'] = $component->getAttributes()[$key];
            }
        }

        if (count($changed) > 0) {
            $logAction = new Actionlog;
            $logAction->item_type = Component::class;
            $logAction->item_id = $component->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->action_date = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            if ($component->imported) {
                $logAction->setActionSource('importer');
            }
            $logAction->logaction('update');
        }

        // Archive-time request cleanup.
        if ($component->wasChanged('archived') && $component->archived) {
            $component->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());
        }
    }

    /**
     * Listen to the Component created event when
     * a new component is created.
     *
     * @return void
     */
    public function created(Component $component)
    {
        $component->writeInitialInventoryCreate();
    }

    /**
     * Listen to the Component deleting event. Mirrors the archive-time
     * cancellation in updated() so the two lifecycle transitions
     * have the same posture: open CheckoutRequest rows get canceled
     * so they stop surfacing on /account/requested while the parent
     * is unavailable. The actionlog write preserves the audit trail
     * either way.
     *
     * @return void
     */
    public function deleting(Component $component)
    {
        $component->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());

        $logAction = new Actionlog;
        $logAction->item_type = Component::class;
        $logAction->item_id = $component->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
