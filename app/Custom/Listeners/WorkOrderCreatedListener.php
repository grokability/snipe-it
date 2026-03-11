<?php

namespace App\Custom\Listeners;

use App\Custom\Events\WorkOrderCreated;
use App\Custom\Notifications\NewWorkOrderNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class WorkOrderCreatedListener
{
    public function handle(WorkOrderCreated $event): void
    {
        $wo = $event->workOrder;

        // Load relations for notification content
        $wo->loadMissing(['asset', 'reportedBy', 'location']);

        // Notify all users with maintenance-related permissions.
        // In a full implementation this would filter by fom.technician / fom.maintenance_engineer.
        // For now, notify superadmins as a safe default.
        $recipients = User::where('activated', 1)
            ->where('permissions->superuser', '1')
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewWorkOrderNotification($wo));
        }

        Log::channel('single')->info("FOM: İş emri oluşturuldu — {$wo->work_order_number}", [
            'asset_id'  => $wo->asset_id,
            'priority'  => $wo->priority,
            'reporter'  => $wo->reported_by,
        ]);
    }
}
