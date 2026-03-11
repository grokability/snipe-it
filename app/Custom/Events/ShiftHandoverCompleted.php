<?php

namespace App\Custom\Events;

use App\Custom\Models\ShiftHandover;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShiftHandoverCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public ShiftHandover $handover)
    {
    }
}
