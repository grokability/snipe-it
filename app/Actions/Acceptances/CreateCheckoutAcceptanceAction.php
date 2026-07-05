<?php

namespace App\Actions\Acceptances;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateCheckoutAcceptanceAction
{
    public static function run(
        Model $checkoutable,
        User $assignedTo,
        ?int $qty = null,
        ?int $alertOnResponseId = null,
    ): CheckoutAcceptance {
        $acceptance = new CheckoutAcceptance;
        $acceptance->checkoutable()->associate($checkoutable);
        $acceptance->assignedTo()->associate($assignedTo);
        $acceptance->qty = $qty;
        $acceptance->alert_on_response_id = $alertOnResponseId;
        $acceptance->save();

        return $acceptance;
    }
}
