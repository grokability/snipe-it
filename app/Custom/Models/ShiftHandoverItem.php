<?php

namespace App\Custom\Models;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftHandoverItem extends Model
{
    protected $table = 'shift_handover_items';

    protected $fillable = [
        'shift_handover_id',
        'asset_id',
        'condition',
        'notes',
        'photo_path',
        'work_order_id',
    ];

    // ── Relationships ──

    public function handover(): BelongsTo
    {
        return $this->belongsTo(ShiftHandover::class, 'shift_handover_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
