<?php

namespace App\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Events\UserMerged;
use App\Models\Actionlog;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogListener
{
    /**
     * These onBlah methods are used by the subscribe() method further down in this file.
     * This one creates an action_logs entry for the checkin
     *
     * @return void
     */
    public function onCheckoutableCheckedIn(CheckoutableCheckedIn $event)
    {
        $event->checkoutable->logCheckin($event->checkedOutTo, $event->note, $event->action_date, $event->originalValues);
    }

    /**
     * These onBlah methods are used by the subscribe() method further down in this file.
     * This one creates an action_logs entry for the checkout
     *
     * @return void
     */
    public function onCheckoutableCheckedOut(CheckoutableCheckedOut $event)
    {
        $event->checkoutable->logCheckout(
            $event->note,
            $event->checkedOutTo,
            $event->checkoutable->last_checkout,
            $event->originalValues,
            $event->quantity
        );
    }

    /**
     * These onBlah methods are used by the subscribe() method further down in this file.
     * This creates the entry in the action_logs table for the accept/decline action
     */
    public function onCheckoutAccepted(CheckoutAccepted $event)
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

    public function onCheckoutDeclined(CheckoutDeclined $event)
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

    public function onUserMerged(UserMerged $event)
    {

        $to_from_array = [
            'to_id' => $event->merged_to->id,
            'to_username' => $event->merged_to->username,
            'from_id' => $event->merged_from->id,
            'from_username' => $event->merged_from->username,
        ];

        // Add a record to the users being merged FROM
        Log::debug('Users merged: '.$event->merged_from->id.' ('.$event->merged_from->username.') merged into '.$event->merged_to->id.' ('.$event->merged_to->username.')');
        $logaction = new Actionlog;
        $logaction->item_id = $event->merged_from->id;
        $logaction->item_type = User::class;
        $logaction->target_id = $event->merged_to->id;
        $logaction->target_type = User::class;
        $logaction->action_type = 'merged';
        $logaction->note = trans('general.merged_log_this_user_from', $to_from_array);
        $logaction->created_by = $event->admin->id ?? null;
        $logaction->save();

        // Add a record to the users being merged TO
        $logaction = new Actionlog;
        $logaction->target_id = $event->merged_from->id;
        $logaction->target_type = User::class;
        $logaction->item_id = $event->merged_to->id;
        $logaction->item_type = User::class;
        $logaction->action_type = 'merged';
        $logaction->note = trans('general.merged_log_this_user_into', $to_from_array);
        $logaction->created_by = $event->admin->id ?? null;
        $logaction->save();

    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $list = [
            'CheckoutableCheckedIn',
            'CheckoutableCheckedOut',
            'CheckoutAccepted',
            'CheckoutDeclined',
            'UserMerged',
            'NoteAdded',
        ];

        foreach ($list as $event) {
            $events->listen(
                'App\Events\\'.$event,
                'App\Listeners\LogListener@on'.$event
            );
        }
    }
}
