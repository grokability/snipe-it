<?php

/*! \mainpage Snipe-IT Code Documentation
 *
 * \section intro_sec Introduction
 *
 * This documentation is designed to allow developers to easily understand
 * the backend code of Snipe-IT. Familiarity with the PHP language is assumed,
 * and experience with the Laravel framework (version 5.2) will be very helpful.
 *
 * **THIS DOCUMENTATION DOES NOT COVER INSTALLATION.** If you're here and you're not a
 * developer, you're probably in the wrong place. Please see the
 * [Installation documentation](https://snipe-it.readme.io) for
 * information on how to install Snipe-IT.
 *
 * To learn how to set up a development environment and get started developing for Snipe-IT,
 * please see the [contributing documentation](https://snipe-it.readme.io/docs/contributing-overview).
 *
 * Only the Snipe-IT specific controllers, models, helpers, service providers,
 * etc have been included in this documentation (excluding vendors, Laravel core, etc)
 * for simplicity.
 */

namespace App\Http\Controllers;

use App\Enums\FileStorage;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\DisablesDebugbar;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DisablesDebugbar, DispatchesJobs, ValidatesRequests;

    public static $map_object_type = [
        'accessories' => Accessory::class,
        'companies' => Company::class,
        'departments' => Department::class,
        'maintenances' => Maintenance::class,
        'assets' => Asset::class,
        'audits' => Asset::class,
        'components' => Component::class,
        'consumables' => Consumable::class,
        'hardware' => Asset::class,
        'licenses' => License::class,
        'locations' => Location::class,
        'models' => AssetModel::class,
        'suppliers' => Supplier::class,
        'users' => User::class,
    ];

    public static $map_file_prefix = [
        'accessories' => 'accessory',
        'maintenances' => 'maintenance',
        'assets' => 'asset',
        'audits' => 'audits',
        'companies' => 'company',
        'departments' => 'department',
        'components' => 'component',
        'consumables' => 'consumable',
        'hardware' => 'asset',
        'licenses' => 'license',
        'locations' => 'location',
        'models' => 'model',
        'suppliers' => 'supplier',
        'users' => 'user',
    ];

    /**
     * Reverse of $map_object_type: model class => canonical URL segment,
     * used by generic code (transformers, traits) that has a class
     * string in hand and needs the segment to build a route or key
     * into $map_storage_path / $map_file_prefix. Asset uses 'hardware'
     * because that's the canonical URL segment (the 'assets' and
     * 'audits' entries above are legacy aliases pointing at the same
     * model). Add entries here when a new model needs URL-segment
     * lookup, not by scattering per-model `urlSegment()` methods.
     */
    public static $map_class_url_segment = [
        Accessory::class => 'accessories',
        Asset::class => 'hardware',
        Component::class => 'components',
        Consumable::class => 'consumables',
        License::class => 'licenses',
    ];

    // Activity-report class allowlist lives on App\Enums\ActivityItemType.
    // Access via getActivityReportClassAllowlist() below.

    public function __construct()
    {
        view()->share('signedIn', Auth::check());
        view()->share('user', auth()->user());
    }

    /**
     * Accessor for the object-type map. The public static array above is
     * kept for back-compat with any external callers that reach into it
     * directly, but internal callers should prefer this getter so static
     * analyzers (Codacy) don't misparse `parent::$map_object_type` as a
     * variable-variable dereference.
     */
    public static function getMapObjectType(): array
    {
        return static::$map_object_type;
    }

    /**
     * Accessor for the storage-path map. See getMapObjectType for rationale.
     */
    public static function getMapStoragePath(): array
    {
        return [
            'accessories' => FileStorage::Accessories->privateStorageKey(),
            'maintenances' => FileStorage::Maintenances->privateStorageKey(),
            'assets' => FileStorage::Assets->privateStorageKey(),
            'audits' => FileStorage::Audits->privateStorageKey(),
            'departments' => FileStorage::Departments->privateStorageKey(),
            'companies' => FileStorage::Companies->privateStorageKey(),
            'components' => FileStorage::Components->privateStorageKey(),
            'consumables' => FileStorage::Consumables->privateStorageKey(),
            'hardware' => FileStorage::Assets->privateStorageKey(),
            'licenses' => FileStorage::Licenses->privateStorageKey(),
            'locations' => FileStorage::Locations->privateStorageKey(),
            'models' => FileStorage::Models->privateStorageKey(),
            'suppliers' => FileStorage::Suppliers->privateStorageKey(),
            'users' => FileStorage::Users->privateStorageKey(),
        ];
    }

    /**
     * Accessor for the file-prefix map. See getMapObjectType for rationale.
     */
    public static function getMapFilePrefix(): array
    {
        return static::$map_file_prefix;
    }

    /**
     * Accessor for the class => URL segment map. See getMapObjectType
     * for rationale.
     */
    public static function getMapClassUrlSegment(): array
    {
        return static::$map_class_url_segment;
    }

    /**
     * Fully qualified model classes the activity-report endpoint
     * (Api\ReportsController::index) accepts as item_type / target_type.
     * Sourced from the ActivityItemType enum, which is the single source
     * of truth for every surface that enumerates these types (controller
     * allowlist, resolver, UI dropdowns).
     *
     * This is a security surface: input flows directly into a runtime
     * class lookup and polymorphic where-clauses. Without a gate, a
     * caller can probe arbitrary strings and either trigger a Fatal
     * Error (Helper::normalizeFullModelName + ucwords mangles CamelCase
     * so `licenseseat` yields `App\Models\Licenseseat`) or route the
     * authorization check into an unintended class.
     *
     * @return list<class-string>
     */
    public static function getActivityReportClassAllowlist(): array
    {
        return \App\Enums\ActivityItemType::classes();
    }
}
