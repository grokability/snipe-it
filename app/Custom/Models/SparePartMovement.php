<?php

namespace App\Custom\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartMovement extends Model
{
    public $timestamps = false;

    protected $table = 'spare_part_movements';

    protected $fillable = [
        'spare_part_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'performed_by',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relationships ──

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ── Polymorphic reference helper ──

    public function reference(): ?Model
    {
        if ($this->reference_type && $this->reference_id) {
            $class = match ($this->reference_type) {
                'work_order' => WorkOrder::class,
                'purchase'   => PurchaseRequest::class,
                default      => null,
            };

            return $class ? $class::find($this->reference_id) : null;
        }

        return null;
    }
}
