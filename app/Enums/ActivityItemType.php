<?php

namespace App\Enums;

use App\Helpers\IconHelper;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

/**
 * Canonical set of entity types that can appear in action_logs
 * item_type and target_type columns, and that the activity report
 * accepts as filter values.
 *
 * The string value is the short name used in URLs and the activity
 * report's API query params. ->class() returns the fully qualified
 * model class, as stored in action_logs.
 *
 * Serves as the single source of truth for:
 *   - the activity-report allowlist on the base Controller
 *   - resolveActivityReportType on Api\ReportsController
 *   - the type dropdowns in the activity report toolbar
 *
 * LicenseSeat is intentionally absent. Loggable::determineLogItemType
 * rewrites every seat-level log call to item_type = License + item_id
 * = license_id, so no action_logs row ever carries LicenseSeat as its
 * item_type or target_type.
 */
enum ActivityItemType: string
{
    case Accessory = 'accessory';
    case Asset = 'asset';
    case AssetModel = 'assetmodel';
    case Category = 'category';
    case Company = 'company';
    case Component = 'component';
    case Consumable = 'consumable';
    case Department = 'department';
    case License = 'license';
    case Location = 'location';
    case Maintenance = 'maintenance';
    case Manufacturer = 'manufacturer';
    case Supplier = 'supplier';
    case User = 'user';

    /**
     * Fully qualified model class, as stored in action_logs.item_type
     * and action_logs.target_type.
     *
     * @return class-string
     */
    public function class(): string
    {
        return match ($this) {
            self::Accessory => Accessory::class,
            self::Asset => Asset::class,
            self::AssetModel => AssetModel::class,
            self::Category => Category::class,
            self::Company => Company::class,
            self::Component => Component::class,
            self::Consumable => Consumable::class,
            self::Department => Department::class,
            self::License => License::class,
            self::Location => Location::class,
            self::Maintenance => Maintenance::class,
            self::Manufacturer => Manufacturer::class,
            self::Supplier => Supplier::class,
            self::User => User::class,
        };
    }

    /**
     * Translated human-readable label for UI dropdowns.
     */
    public function label(): string
    {
        return match ($this) {
            self::AssetModel => trans('general.asset_model'),
            self::Maintenance => trans('admin/maintenances/general.maintenance'),
            default => trans('general.'.$this->value),
        };
    }

    /**
     * CSS class string for the type's icon, via IconHelper.
     */
    public function icon(): string
    {
        return IconHelper::icon(match ($this) {
            self::AssetModel => 'model',
            self::Maintenance => 'maintenances',
            default => $this->value,
        });
    }

    /**
     * Resolve a caller-supplied type input (short name OR fully
     * qualified class string, case insensitive) to an enum case.
     * Returns null on no match so callers can translate to a 400.
     *
     * Case insensitivity is deliberate. The pre-enum resolver ran
     * input through Helper::normalizeFullModelName whose ucwords()
     * step mangled CamelCase class names ('licenseseat' became the
     * nonexistent App\Models\Licenseseat), and the fallback was a
     * strcasecmp against the allowlist. The enum keeps that recovery.
     */
    public static function tryFromAny(string $input): ?self
    {
        $normalized = strtolower(ltrim($input, '\\'));

        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $normalized) {
                return $case;
            }
            if (strtolower($case->class()) === $normalized) {
                return $case;
            }
        }

        return null;
    }

    /**
     * All fully qualified class strings, in enum declaration order.
     * Historical allowlist shape, kept for Controller::$activity_report_class_allowlist.
     *
     * @return list<class-string>
     */
    public static function classes(): array
    {
        return array_map(fn (self $case) => $case->class(), self::cases());
    }
}
