<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sync-adapter side row for an asset. Combines the identity mapping
 * (which adapter sourced this asset via source + external_id) with
 * the last-known network / OS inventory the vendor reported
 * (primary_mac / primary_ip / os / os_version / last_seen).
 *
 * One row per asset today. SyncHostFromAdapter upserts on the
 * (source, external_id) unique index and updates the inventory
 * columns on every sync run.
 */
class AssetExternalSource extends Model
{
    protected $table = 'asset_external_sources';

    protected $fillable = [
        'company_id',
        'asset_id',
        'source',
        'external_id',
        'primary_mac',
        'primary_ip',
        'os',
        'os_version',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
