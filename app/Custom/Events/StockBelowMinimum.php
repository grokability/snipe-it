<?php

namespace App\Custom\Events;

use App\Custom\Models\SparePart;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockBelowMinimum
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SparePart $sparePart,
        public int $currentQty,
    ) {
    }
}
