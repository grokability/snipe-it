<?php

namespace App\Events;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutableCheckedOut
{
    use Dispatchable, SerializesModels;

    public $checkoutable;

    public $checkedOutTo;

    public $checkedOutBy;

    public $note;

    public $originalValues;

    public int $quantity;

    public bool $signInPlace;

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
    public function __construct($checkoutable, $checkedOutTo, User $checkedOutBy, $note, $originalValues = [], $quantity = 1, bool $signInPlace = false)
    {
        $this->checkoutable = $checkoutable;
        $this->checkedOutTo = $checkedOutTo;
        $this->checkedOutBy = $checkedOutBy;
        $this->note = $note;
        $this->originalValues = $originalValues;
        $this->quantity = $quantity;
        $this->signInPlace = $signInPlace;
    }
}
