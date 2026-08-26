<?php

namespace App\Listeners;

use App\Events\CheckoutableCheckedIn;

class CheckoutableCheckedInLogCheckin
{
    public function handle(CheckoutableCheckedIn $event)
    {
        $event->checkoutable->logCheckin($event->checkedOutTo, $event->note, $event->action_date, $event->originalValues);
    }
}