<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'predefined_kit_id',
        'title',
        'description',
        'frequency',
        'frequency_interval',
        'start_date',
        'next_due_date',
        'last_completed_date',
        'priority',
        'status',
        'assigned_to',
        'supplier_id',
        'estimated_duration',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_due_date' => 'date',
        'last_completed_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function kit()
    {
        return $this->belongsTo(PredefinedKit::class, 'predefined_kit_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function maintenanceHistory()
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('next_due_date', '<=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('next_due_date', '<', now())->where('status', 'active');
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('next_due_date', [now(), now()->addDays($days)])
                     ->where('status', 'active');
    }

    /**
     * Mutators
     */
    public function getDisplayNameAttribute()
    {
        return $this->title;
    }

    public function isPastDue()
    {
        return $this->next_due_date < now() && $this->status === 'active';
    }

    public function updateNextDueDate()
    {
        $interval = $this->frequency_interval;
        
        switch ($this->frequency) {
            case 'daily':
                $this->next_due_date = $this->next_due_date->addDays($interval);
                break;
            case 'weekly':
                $this->next_due_date = $this->next_due_date->addWeeks($interval);
                break;
            case 'monthly':
                $this->next_due_date = $this->next_due_date->addMonths($interval);
                break;
            case 'quarterly':
                $this->next_due_date = $this->next_due_date->addMonths(3 * $interval);
                break;
            case 'yearly':
                $this->next_due_date = $this->next_due_date->addYears($interval);
                break;
        }
        
        $this->save();
    }
}
