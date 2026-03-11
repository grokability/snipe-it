<?php

namespace App\Custom\Models;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    protected $table = 'work_orders';

    protected $fillable = [
        'work_order_number',
        'asset_id',
        'reported_by',
        'assigned_to',
        'location_id',
        'priority',
        'status',
        'description',
        'resolution_notes',
        'photo_path',
        'time_spent_minutes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Auto-generate work_order_number ──

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $wo) {
            if (empty($wo->work_order_number)) {
                $wo->work_order_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $prefix = "IE-{$year}-";

        $last = static::withTrashed()
            ->where('work_order_number', 'like', $prefix . '%')
            ->orderByDesc('work_order_number')
            ->value('work_order_number');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function handoverItems(): HasMany
    {
        return $this->hasMany(ShiftHandoverItem::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    // ── Scopes ──

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['tamamlandi', 'iptal']);
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'kritik');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'bekliyor');
    }
}
