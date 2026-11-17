<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;

/**
 * Request-scoped {id => name} lookup, used by label-rendering paths
 * that would otherwise fire a Model::find per rendered picker option
 * (see MappingTargets::labelFor, category-select.blade.php, etc).
 *
 * The cache is keyed by `static::class` so multiple descendants of the
 * same base class (Category, Statuslabel, CustomFieldset, ...) each
 * get their own entry rather than stepping on each other. The lookup
 * uses late static binding so `static::query()` runs against the
 * actual called class.
 */
trait HasCachedNameMap
{
    /** @var array<class-string, \Illuminate\Support\Collection<int, string>> */
    private static array $nameMapCache = [];

    public static function nameMap(): Collection
    {
        $class = static::class;
        if (! isset(self::$nameMapCache[$class])) {
            self::$nameMapCache[$class] = static::query()->pluck('name', 'id');
        }

        return self::$nameMapCache[$class];
    }
}
