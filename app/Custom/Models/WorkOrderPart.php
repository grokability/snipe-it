<?php

namespace App\Custom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderPart extends Model
{
    protected $table = 'work_order_parts';

    protected $fillable = [
        'work_order_id',
        'spare_part_id',
        'quantity_used',
        'notes',
    ];

    // ── Relationships ──

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
