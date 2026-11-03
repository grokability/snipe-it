<?php

namespace App\Events;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutableCheckedOut
{
    use Dispatchable, SerializesModels;

    /**
     * Set by whichever notification listener (email or webhook) creates the
     * CheckoutAcceptance first, so the other listener reuses it instead of
     * creating a duplicate row for the same checkout.
     */
    public ?CheckoutAcceptance $checkoutAcceptance = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public $checkoutable,
        public $checkedOutTo,
        public User $checkedOutBy,
        public $note,
        public $originalValues = [],
        public int $quantity = 1,
        public bool $signInPlace = false
    ) {
    }
}
