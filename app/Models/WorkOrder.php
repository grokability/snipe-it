<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_number',
        'asset_id',
        'maintenance_schedule_id',
        'predefined_kit_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'assigned_to',
        'supplier_id',
        'created_by',
    'completed_by',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'estimated_duration',
        'actual_duration',
        'estimated_cost',
        'actual_cost',
        'work_performed',
        'parts_used',
        'notes',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    /**
     * Boot method to generate work order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workOrder) {
            if (empty($workOrder->work_order_number)) {
                $workOrder->work_order_number = self::generateWorkOrderNumber();
            }
        });
    }

    /**
     * Generate unique work order number
     */
    public static function generateWorkOrderNumber()
    {
        $prefix = 'WO';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', now())->latest('id')->first();
        $sequence = $lastOrder ? (int)substr($lastOrder->work_order_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Relationships
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function maintenanceSchedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function kit()
    {
        return $this->belongsTo(PredefinedKit::class, 'predefined_kit_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    public function maintenanceHistory()
    {
        return $this->hasOne(MaintenanceHistory::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_end', '<', now())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Mutators
     */
    public function getDisplayNameAttribute()
    {
        return $this->title;
    }

    public function isOverdue()
    {
        return $this->scheduled_end < now() && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function calculateActualDuration()
    {
        if ($this->actual_start && $this->actual_end) {
            $this->actual_duration = $this->actual_start->diffInMinutes($this->actual_end);
            $this->save();
        }
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->actual_end = now();
        $this->calculateActualDuration();
        $this->save();
    }
}
