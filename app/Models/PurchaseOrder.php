<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id', 
        'status',
        'order_date',
        'expected_delivery_date',
        'total_amount',
        'notes',
        'created_by'
    ];

    protected $dates = [
        'order_date',
        'expected_delivery_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery_date' => 'date'
    ];

    /**
     * Get the supplier for this purchase order
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    /**
     * Get the items for this purchase order
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the user who created this purchase order
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Generate a unique PO number
     */
    public function generatePoNumber()
    {
        $year = date('Y');
        $prefix = "PO-{$year}-";
        
        // Get all PO numbers for this year including soft-deleted ones
        $existingNumbers = self::withTrashed()
                              ->where('po_number', 'like', $prefix . '%')
                              ->pluck('po_number')
                              ->map(function($poNumber) use ($year) {
                                  if (preg_match('/^PO-' . $year . '-(\d{4})$/', $poNumber, $matches)) {
                                      return intval($matches[1]);
                                  }
                                  return 0;
                              })
                              ->filter(function($num) { return $num > 0; })
                              ->sort()
                              ->values();
        
        // Find the next available number
        $nextNumber = 1;
        if ($existingNumbers->isNotEmpty()) {
            $nextNumber = $existingNumbers->max() + 1;
        }
        
        // Generate and ensure uniqueness (including soft-deleted)
        do {
            $poNumber = sprintf("PO-%s-%04d", $year, $nextNumber);
            $exists = self::withTrashed()->where('po_number', $poNumber)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);
        
        return $poNumber;
    }

    /**
     * Calculate total amount from items
     */
    public function calculateTotal()
    {
        $total = $this->items()->sum('total_price');
        $this->update(['total_amount' => $total]);
        return $total;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'info',
            'ordered' => 'primary',
            'received' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }
}
