<?php

namespace App\Custom\Events;

use App\Custom\Models\WorkOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkOrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public WorkOrder $workOrder)
    {
    }
}
