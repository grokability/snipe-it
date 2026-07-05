<?php

namespace App\Observers;

use App\Models\Accessory;
use App\Models\Actionlog;

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
    }

    /**
     * Listen to the Accessory created event when
     * a new accessory is created.
     *
     * @return void
     */
    public function created(Accessory $accessory)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Accessory::class;
        $logAction->item_id = $accessory->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if ($accessory->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');
    }

    /**
     * Listen to the Accessory deleting event.
     *
     * @return void
     */
    public function deleting(Accessory $accessory)
    {
        $logAction = new Actionlog;
        $logAction->item_type = Accessory::class;
        $logAction->item_id = $accessory->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
