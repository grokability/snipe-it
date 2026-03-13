<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutableCheckedIn
{
    use Dispatchable, SerializesModels;

    public $checkoutable;
    public $checkedOutTo;
    public $checkedInBy;
    public $note;
    public $action_date; // Date setted in the hardware.checkin view at the checkin_at input, for the action log
    public $originalValues;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($checkoutable, $checkedOutTo, User $checkedInBy, $note, $action_date = null, $originalValues = [])
    {
        $this->checkoutable = $checkoutable;
        $this->checkedOutTo = $checkedOutTo;
        $this->checkedInBy = $checkedInBy;
        $this->note = $note;
        $this->action_date = $action_date ?? date('Y-m-d H:i:s');
        $this->originalValues = $originalValues;
    }
}
