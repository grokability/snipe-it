<?php

namespace App\Listeners;

use App\Events\CheckoutAccepted as CheckoutAcceptedEvent;
use App\Models\Actionlog;
use App\Models\LicenseSeat;
use Illuminate\Support\Facades\Log;

class CheckoutAccepted
{
    public function handle(CheckoutAcceptedEvent $event)
    {

        Log::debug('event passed to the onCheckoutAccepted listener:');
        $logaction = new Actionlog;
        $logaction->item()->associate($event->acceptance->checkoutable);
        $logaction->target()->associate($event->acceptance->assignedTo);
        $logaction->accept_signature = $event->acceptance->signature_filename;
        $logaction->filename = $event->acceptance->stored_eula_file;
        $logaction->note = $event->acceptance->note;
        $logaction->action_type = 'accepted';
        $logaction->action_date = $event->acceptance->accepted_at;
        $logaction->quantity = $event->acceptance->qty ?? 1;
        $logaction->created_by = auth()->user()->id;

        // TODO: log the actual license seat that was checked out
        if ($event->acceptance->checkoutable instanceof LicenseSeat) {
            $logaction->item()->associate($event->acceptance->checkoutable->license);
        }

        $logaction->save();
    }

}