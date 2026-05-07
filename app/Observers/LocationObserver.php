<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\Location;

class LocationObserver
{
    /**
     * Listen to the User created event.
     *
     * @return void
     */
    public function updating(Location $location)
    {

        $changed = [];

        foreach ($location->getRawOriginal() as $key => $value) {
            // Check and see if the value changed
            if ($location->getRawOriginal()[$key] != $location->getAttributes()[$key]) {
                $changed[$key]['old'] = $location->getRawOriginal()[$key];
                $changed[$key]['new'] = $location->getAttributes()[$key];
            }
        }

        if (count($changed) > 0) {
            $logAction = new Actionlog;
            $logAction->item_type = Location::class;
            $logAction->item_id = $location->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');
        }

    }

    /**
     * Listen to the Location created event when
     * a new location is created.
     *
     * @return void
     */
    public function created(Location $location)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Location::class;
        $logAction->item_id = $location->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if ($location->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');
    }

    /**
     * Listen to the Location deleting event.
     *
     * @return void
     */
    public function deleting(Location $location)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Location::class;
        $logAction->item_id = $location->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }

    public function restoring(Location $location)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Location::class;
        $logAction->item_id = $location->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('restore');
    }
}
