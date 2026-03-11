<?php

namespace App\Custom\Models;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    protected $table = 'purchase_requests';

    protected $fillable = [
        'request_number',
        'spare_part_id',
        'requested_quantity',
        'estimated_cost',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'supplier_id',
        'work_order_id',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
    ];

    // ── Auto-generate request_number ──

    protected static function booted(): void
    {
        static::creating(function (PurchaseRequest $pr) {
            if (empty($pr->request_number)) {
                $pr->request_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $prefix = "ST-{$year}-";

        $last = static::where('request_number', 'like', $prefix . '%')
            ->orderByDesc('request_number')
            ->value('request_number');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
