<?php

namespace App\Listeners;

use App\Events\CheckoutableCheckedOut;

class CheckoutableCheckedOutLogCheckout
{
    public function handle(CheckoutableCheckedOut $event)
    {
        $event->checkoutable->logCheckout(
            $event->note,
            $event->checkedOutTo,
            $event->checkoutable->last_checkout,
            $event->originalValues,
            $event->quantity
        );
    }
}