<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'asset_id',
        'asset_model_id',
        'model_name',
        'quantity',
        'unit_price',
        'total_price',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Get the purchase order this item belongs to
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the asset for this item
     */
    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class);
    }

    /**
     * Get the asset model for this item
     */
    public function assetModel()
    {
        return $this->belongsTo(\App\Models\AssetModel::class, 'asset_model_id');
    }

    /**
     * Calculate total price when quantity or unit price changes
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        return $this->total_price;
    }

    /**
     * Boot method to automatically calculate total price
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            // Update purchase order total when item is saved
            if ($item->purchaseOrder) {
                $item->purchaseOrder->calculateTotal();
            }
        });

        static::deleted(function ($item) {
            // Update purchase order total when item is deleted
            if ($item->purchaseOrder) {
                $item->purchaseOrder->calculateTotal();
            }
        });
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2);
    }
}
