<?php

namespace App\Listeners;

use App\Events\UserMerged as UserMergedEvent;
use App\Models\Actionlog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserMerged
{
    public function handle(UserMergedEvent $event)
    {

        $to_from_array = [
            'to_id' => $event->merged_to->id,
            'to_username' => $event->merged_to->username,
            'from_id' => $event->merged_from->id,
            'from_username' => $event->merged_from->username,
        ];

        // Add a record to the users being merged FROM
        Log::debug('Users merged: ' . $event->merged_from->id . ' (' . $event->merged_from->username . ') merged into ' . $event->merged_to->id . ' (' . $event->merged_to->username . ')');
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

}