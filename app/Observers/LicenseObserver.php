<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\License;

class LicenseObserver
{
    /**
     * Listen to the User created event.
     *
     * @return void
     */
    public function updated(License $license)
    {
        $logAction = new Actionlog;
        $logAction->item_type = License::class;
        $logAction->item_id = $license->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('update');
    }

    /**
     * Listen to the License created event when
     * a new license is created.
     *
     * @return void
     */
    public function created(License $license)
    {
        $logAction = new Actionlog;
        $logAction->item_type = License::class;
        $logAction->item_id = $license->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if ($license->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');
    }

    /**
     * Listen to the License deleting event.
     *
     * @return void
     */
    public function deleting(License $license)
    {
        $logAction = new Actionlog;
        $logAction->item_type = License::class;
        $logAction->item_id = $license->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }
}
