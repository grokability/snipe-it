<?php

namespace App\Listeners;

use App\Models\Actionlog;
use App\Models\LicenseSeat;
use App\Events\CheckoutDeclined as CheckoutDeclinedEvent;

class CheckoutDeclined
{
    public function handle(CheckoutDeclinedEvent $event)
    {
        $logaction = new Actionlog;
        $logaction->item()->associate($event->acceptance->checkoutable);
        $logaction->target()->associate($event->acceptance->assignedTo);
        $logaction->accept_signature = $event->acceptance->signature_filename;
        $logaction->note = $event->acceptance->note;
        $logaction->action_type = 'declined';
        $logaction->action_date = $event->acceptance->declined_at;
        $logaction->quantity = $event->acceptance->qty ?? 1;
        $logaction->created_by = auth()->user()->id;

        // TODO: log the actual license seat that was checked out
        if ($event->acceptance->checkoutable instanceof LicenseSeat) {
            $logaction->item()->associate($event->acceptance->checkoutable->license);
        }

        $logaction->save();
    }


}