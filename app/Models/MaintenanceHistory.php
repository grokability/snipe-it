<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance_history';

    protected $fillable = [
        'asset_id',
        'work_order_id',
        'maintenance_schedule_id',
        'predefined_kit_id',
        'title',
        'description',
        'type',
        'performed_at',
        'performed_by',
        'duration',
        'cost',
        'work_performed',
        'parts_used',
        'components_replaced',
        'consumables_used',
        'outcome',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'cost' => 'decimal:2',
        'components_replaced' => 'array',
        'consumables_used' => 'array',
    ];

    /**
     * Relationships
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function maintenanceSchedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function kit()
    {
        return $this->belongsTo(PredefinedKit::class, 'predefined_kit_id');
    }

    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scopes
     */
    public function scopeByAsset($query, $assetId)
    {
        return $query->where('asset_id', $assetId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('outcome', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('outcome', 'failed');
    }

    /**
     * Accessors
     */
    public function getTotalCostAttribute()
    {
        return $this->cost ?? 0;
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return 'N/A';
        }

        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }
}
