<?php

namespace App\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CheckoutableCheckedInLogCheckin
{
    public function handle(CheckoutableCheckedIn $event): void
    {
        if ($event->checkedOutTo instanceof User && $event->checkoutable) {
            $this->retirePendingAcceptances($event->checkoutable, $event->checkedOutTo);
        }

        $event->checkoutable->logCheckin($event->checkedOutTo, $event->note, $event->action_date, $event->originalValues);
    }

    /**
     * Clear the holder's outstanding acceptance requests for checked-in item.
     *
     * Assets and license seats are 1:1 with their acceptance rows. Accessories
     * are not: accessories_checkout holds one row per unit while an acceptance
     * row covers a whole checkout action and carries its qty, so checking one
     * unit in retires one unit rather than a row that may be worth three.
     *
     * Only ever called for a User holder. Acceptances are created for users
     * alone, so assigned_to_id holds a user id — matching a Location or Asset
     * id against it would clear a different holder's rows by collision.
     */
    protected function retirePendingAcceptances(Model $checkoutable, User $checkedOutTo): void
    {
        $acceptances = CheckoutAcceptance::pending()
            ->where('checkoutable_type', $checkoutable->getMorphClass())
            ->where('checkoutable_id', $checkoutable->getKey())
            ->where('assigned_to_id', $checkedOutTo->id)
            ->orderBy('id')
            ->get();

        if ($checkoutable instanceof Accessory) {
            $this->retireOneUnitOfPendingQty($acceptances);

            return;
        }

        $acceptances->each(fn (CheckoutAcceptance $acceptance) => $acceptance->delete());
    }

    /**
     * Retire one unit from the oldest pending row, deleting it at zero.
     *
     * Accessory units are fungible — no serial, no tag — so there is no fact
     * about which unit came back; a checkin is defined to retire an unaccepted
     * one, and to do nothing when none are left.
     *
     * @param  Collection<int, CheckoutAcceptance>  $acceptances
     */
    protected function retireOneUnitOfPendingQty(Collection $acceptances): void
    {
        $acceptance = $acceptances->first();

        if (! $acceptance) {
            return;
        }

        // Null qty means one unit, as in AcceptanceController and LogListener.
        if (($acceptance->qty ?? 1) <= 1) {
            $acceptance->delete();

            return;
        }

        $acceptance->decrement('qty');
    }

}
