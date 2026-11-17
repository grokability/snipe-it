<?php

namespace App\Models;

use App\SyncAdapters\SyncAdapter;
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
 *
 * @mixin IdeHelperSyncAdapterInstance
 */
class SyncAdapterInstance extends Model
{
    protected $fillable = ['adapter_type', 'label', 'slug', 'active', 'company_id', 'config'];

    protected $casts = [
        'active' => 'boolean',
        'last_synced_at' => 'datetime',
        // Per-instance settings blob. SyncAdapterConfig::get/put/forget
        // read and write individual keys against this cast array.
        'config' => 'array',
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
    public function adapter(): ?SyncAdapter
    {
        return SyncAdapter::factory($this);
    }

    /**
     * Shared mutex key for an in-flight sync operation on this
     * instance. Pull and Push on the same instance acquire the same
     * key, so one blocks the other, but Fleet pulling doesn't block
     * Jamf pulling. CLI (snipeit:pull-inventory / snipeit:push-inventory)
     * and the UI controller methods both acquire via this key, so cron
     * + admin click can't collide on the same vendor.
     */
    public function lockKey(): string
    {
        return 'sync-adapter:'.$this->slug.':running';
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
