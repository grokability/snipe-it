<?php

namespace App\SyncAdapters;

use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Addigy\AddigyAdapter;
use App\SyncAdapters\Fleet\FleetAdapter;
use App\SyncAdapters\Intune\IntuneAdapter;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\JamfSchool\JamfSchoolAdapter;
use App\SyncAdapters\JumpCloud\JumpCloudAdapter;
use App\SyncAdapters\Kandji\KandjiAdapter;
use App\SyncAdapters\MerakiSystemsManager\MerakiSystemsManagerAdapter;
use App\SyncAdapters\Mosyle\MosyleAdapter;
use App\SyncAdapters\NinjaOne\NinjaOneAdapter;
use App\SyncAdapters\Osctrl\OsctrlAdapter;
use App\SyncAdapters\Unifi\UnifiAdapter;
use App\SyncAdapters\WorkspaceOne\WorkspaceOneAdapter;
use App\SyncAdapters\Zentral\ZentralAdapter;

/**
 * Registry of adapter TYPES Snipe-IT ships. Instances are stored in the
 * sync_adapter_instances table and multiple instances of the same type
 * are allowed (e.g., two Fleet installs). This registry only tells the
 * app which class handles which adapter_type slug.
 *
 * Kept as a static map for now. Migrating to service-container tagged
 * bindings later would let third-party adapter packages register their
 * own types via a service provider without touching this file.
 */
class AdapterRegistry
{
    /**
     * @var array<string, class-string<HostInventoryAdapter>>
     */
    private const TYPES = [
        'fleet' => FleetAdapter::class,
        'addigy' => AddigyAdapter::class,
        'intune' => IntuneAdapter::class,
        'jamf' => JamfAdapter::class,
        'jamf_school' => JamfSchoolAdapter::class,
        'kandji' => KandjiAdapter::class,
        'meraki_sm' => MerakiSystemsManagerAdapter::class,
        'mosyle' => MosyleAdapter::class,
        'ninjaone' => NinjaOneAdapter::class,
        'osctrl' => OsctrlAdapter::class,
        'unifi' => UnifiAdapter::class,
        'workspace_one' => WorkspaceOneAdapter::class,
        'zentral' => ZentralAdapter::class,
        'jumpcloud' => JumpCloudAdapter::class,
    ];

    /**
     * Build an instance-aware adapter from a SyncAdapterInstance row.
     * Returns null when the instance's adapter_type is not registered
     * (which can happen if an adapter class was removed but instances
     * still reference it).
     */
    public static function hydrate(SyncAdapterInstance $instance): ?HostInventoryAdapter
    {
        $class = self::TYPES[$instance->adapter_type] ?? null;
        if ($class === null) {
            return null;
        }

        return new $class($instance);
    }

    /**
     * Load every configured instance and hydrate it as a live adapter.
     * Instances whose adapter_type isn't registered are skipped.
     *
     * @return array<int, HostInventoryAdapter>
     */
    public static function allInstances(): array
    {
        return SyncAdapterInstance::query()
            ->orderBy('label')
            ->get()
            ->map(fn (SyncAdapterInstance $i) => self::hydrate($i))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function typeNames(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * Human-readable labels for each registered adapter type. Used by
     * the "Add adapter" flow's type picker. Falls back to the type slug
     * when the class doesn't declare a static typeLabel().
     *
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        $out = [];
        foreach (self::TYPES as $type => $class) {
            $out[$type] = method_exists($class, 'typeLabel') ? $class::typeLabel() : ucfirst($type);
        }

        return $out;
    }
}
