<?php

namespace App\Observers;

use App\Models\Accessory;
use App\Models\Actionlog;
use Illuminate\Support\Facades\Auth;

class AccessoryObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Accessory  $accessory
     * @return void
     */
    public function updated(Accessory $accessory)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Accessory::class;
        $logAction->item_id = $accessory->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('update');
    }

    /**
     * Listen to the Accessory created event when
     * a new accessory is created.
     *
     * @param  Accessory  $accessory
     * @return void
     */
    public function created(Accessory $accessory)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Accessory::class;
        $logAction->item_id = $accessory->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if($accessory->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');
    }

    /**
     * Listen to the Accessory deleting event.
     *
     * @param  Accessory  $accessory
     * @return void
     */
    public function deleting(Accessory $accessory)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Accessory::class;
        $logAction->item_id = $accessory->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
