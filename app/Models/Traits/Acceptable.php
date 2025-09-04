<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Log;
/**
 * This trait allows models to have a callback after their checkout gets accepted or declined.
 *
 * @author Till Deeke <kontakt@tilldeeke.de>
 */
trait Acceptable
{
    /**
     * Run after the checkout acceptance was accepted by the user
     *
     * @param User   $acceptedBy
     * @param string $signature
     */
    public function acceptedCheckout(User $acceptedBy, $signature, $filename = null)
    {
        Log::debug('acceptedCheckout in Acceptable trait fired, tho it doesn\'t do anything?');
    }

    /**
     * Run after the checkout acceptance was declined by the user
     *
     * @param User   $acceptedBy
     * @param string $signature
     */
    public function declinedCheckout(User $declinedBy, $signature)
    {
    }
}
