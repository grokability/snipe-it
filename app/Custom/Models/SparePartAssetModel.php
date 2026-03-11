<?php

namespace App\Custom\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SparePartAssetModel extends Pivot
{
    protected $table = 'spare_part_asset_model';

    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_critical'    => 'boolean',
        'recommended_qty' => 'integer',
    ];
}
