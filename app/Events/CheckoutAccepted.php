<?php

namespace App\Events;

use App\Models\CheckoutAcceptance;
use App\Models\Contracts\Acceptable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutAccepted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CheckoutAcceptance $acceptance)
    {
        $this->acceptance = $acceptance;
    }
}
