<?php

namespace App\Custom\Models;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use SoftDeletes;

    protected $table = 'spare_parts';

    protected $fillable = [
        'name',
        'part_number',
        'description',
        'category_id',
        'manufacturer_id',
        'supplier_id',
        'location_id',
        'quantity_on_hand',
        'minimum_quantity',
        'unit_cost',
        'lead_time_days',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    protected $appends = ['stock_status'];

    // ── Relationships ──

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(SparePartMovement::class);
    }

    public function workOrderParts(): HasMany
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function assetModels(): BelongsToMany
    {
        return $this->belongsToMany(AssetModel::class, 'spare_part_asset_model')
            ->withPivot('is_critical', 'recommended_qty');
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    // ── Scopes ──

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'minimum_quantity');
    }

    // ── Accessors ──

    /**
     * Stock status: 'critical' (zero), 'low' (<= minimum), 'ok' (above minimum).
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_on_hand <= 0) {
            return 'critical';
        }

        if ($this->quantity_on_hand <= $this->minimum_quantity) {
            return 'low';
        }

        return 'ok';
    }
}
