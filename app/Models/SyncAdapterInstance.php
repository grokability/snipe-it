<?php

namespace App\Models;

use App\SyncAdapters\AdapterRegistry;
use App\SyncAdapters\HostInventoryAdapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One configured instance of a sync adapter. Multiple instances of the
 * same adapter_type are allowed (e.g., two Fleet installs, one Fleet + one
 * Kandji), each with independent credentials, own tab on the settings
 * page, own row in asset_external_sources where source = the instance slug.
 *
 * Slugs auto-generate from the label at create time and are immutable
 * after, because asset_external_sources.source references them and reassigning
 * slugs later would orphan assets.
 */
class SyncAdapterInstance extends Model
{
    protected $fillable = ['adapter_type', 'label', 'slug', 'active', 'built_in', 'company_id'];

    protected $casts = [
        'active' => 'boolean',
        'built_in' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Bind route params to slug instead of numeric id so URLs are
     * self-documenting (/admin/adapters/fleet-prod/sync).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        // Auto-generate slug from label with a uniqueness suffix so
        // "Production Fleet" and "Prod Fleet" don't collide, and
        // "Fleet" + "Fleet" produces fleet + fleet-2.
        static::creating(function (self $instance) {
            if (empty($instance->slug)) {
                $instance->slug = self::generateUniqueSlug($instance->label);
            }
        });
    }

    /**
     * Resolve this instance's adapter_type to the shipped adapter class
     * and return a hydrated, instance-aware adapter. Returns null if the
     * type is not registered (which shouldn't normally happen but can if
     * an adapter class is removed while instances still reference it).
     */
    public function adapter(): ?HostInventoryAdapter
    {
        return AdapterRegistry::hydrate($this);
    }

    private static function generateUniqueSlug(string $label): string
    {
        $base = Str::slug($label);
        if ($base === '') {
            $base = 'adapter';
        }

        $candidate = $base;
        $suffix = 2;
        while (self::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
