<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\Maintenance;
use App\Models\Asset;

class MaintenanceObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Maintenance  $maintenance
     * @return void
     */
    public function updated(Maintenance $maintenance)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Maintenance::class;
        $logAction->item_id = $maintenance->id;
        $logAction->target_type = Asset::class;
        $logAction->target_id = $maintenance->asset_id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if($maintenance->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('update');
    }

    /**
     * Listen to the Component created event when
     * a new component is created.
     *
     * @param  Maintenance  $maintenance
     * @return void
     */
    public function created(Maintenance $maintenance)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Maintenance::class;
        $logAction->item_id = $maintenance->id;
        $logAction->target_type = Asset::class;
        $logAction->target_id = $maintenance->asset_id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if($maintenance->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');
    }

    /**
     * Listen to the Component deleting event.
     *
     * @param  Maintenance  $maintenance
     * @return void
     */
    public function deleting(Maintenance $maintenance)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Maintenance::class;
        $logAction->item_id = $maintenance->id;
        $logAction->target_type = Asset::class;
        $logAction->target_id = $maintenance->asset_id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
