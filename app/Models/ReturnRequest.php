<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnRequest extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'asset_id',
        'requested_by',
        'from_location_id',
        'warehouse_location_id',
        'requested_at',
        'in_transit_at',
        'received_at',
        'checked_in_at',   // ✅ add
        'closed_at',       // ✅ add
        'canceled_at',
        'note',
    ];

    protected $casts = [
	  'requested_at'  => 'datetime',
	  'in_transit_at' => 'datetime',
	  'received_at'   => 'datetime',
	  'checked_in_at' => 'datetime',
	  'closed_at'     => 'datetime',
	  'canceled_at'   => 'datetime',
	];


    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
    }

    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }
}

