<?php

namespace App\Models\Builders;

use App\Models\Accessory;
use App\Models\Actionlog;
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
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @template TModel of Actionlog
 *
 * @extends Builder<TModel>
 */
class ActionlogQueryBuilder extends Builder
{
    /**
     * Eager load history relations used by the API transformer to avoid
     * N+1 queries.
     */
    public function forApiHistory(): self
    {
        return $this->with([
            'adminuser',
            'location',
            'item' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Asset::class => ['model'],
                ]);
            },
            'target' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Asset::class => ['model'],
                ]);
            },
        ]);
    }

    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'action_logs.created_by', '=', 'admin_sort.id')
            ->addSelect('action_logs.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    public function orderByLocation(string $order): self
    {
        return $this
            ->leftJoin('locations as location_sort', 'action_logs.location_id', '=', 'location_sort.id')
            ->addSelect('action_logs.*')
            ->orderBy('location_sort.name', $order);
    }

    public function orderByAssetNextAuditDate(string $order): self
    {
        return $this
            ->leftJoin('assets as asset_sort', function ($join) {
                $join->on('action_logs.item_id', '=', 'asset_sort.id')
                    ->where('action_logs.item_type', '=', Asset::class);
            })
            ->addSelect('action_logs.*')
            ->orderBy('asset_sort.next_audit_date', $order);
    }

    /**
     * Item name sort for the audit report (#9430). Only Assets get
     * audited, so the action_type=audit filter upstream guarantees
     * item_type=Asset. For the polymorphic activity report, use
     * orderByItemTypeAndId instead.
     */
    public function orderByItemName(string $order): self
    {
        return $this
            ->leftJoin('assets as asset_item_sort', function ($join) {
                $join->on('action_logs.item_id', '=', 'asset_item_sort.id')
                    ->where('action_logs.item_type', '=', Asset::class);
            })
            ->addSelect('action_logs.*')
            ->orderBy('asset_item_sort.name', $order);
    }

    /**
     * This is genuinely awful, but...
     *
     * Alphabetical polymorphic item-name sort for the activity report
     * (#17288). action_logs.item is a polymorphic morphTo across every
     * entity on the install, so there is no cross-type "name" column
     * to ORDER BY. This LEFT JOINs each possible item_type table on
     * (item_id, item_type) and COALESCEs the per-type name expression,
     * so each row contributes exactly one non-null name value and the
     * query orders by that merged value.
     *
     * Per-type expressions handle the entities whose display name is
     * not just `name`: Asset falls back to asset_tag when name is
     * null, User concatenates first_name + last_name when display_name
     * is empty, LicenseSeat has no name column of its own so a second
     * JOIN to the parent license surfaces the license name.
     *
     * The joins are selective (each ON predicate includes the item_type
     * match, so a given action_logs row only joins to one child table)
     * and non-multiplying (one match per row at most), so count() and
     * pagination behavior are unaffected.
     */
    public function orderByPolymorphicItemName(string $order): self
    {
        $order = strtolower($order) === 'desc' ? 'desc' : 'asc';

        $query = $this;
        $nameExprs = [];
        foreach (self::itemNameJoinSpecs() as $spec) {
            $query = self::applyItemNameJoin($query, $spec);
            $nameExprs[] = $spec['nameExpr'];
        }

        return $query
            ->addSelect('action_logs.*')
            ->orderByRaw('COALESCE('.implode(', ', $nameExprs).') '.$order);
    }

    /**
     * Optimized item-name sort for the toolbar type-filter case
     * (#17288). When the controller has pinned a single item_type,
     * only that one child table needs to be joined, so skip the 15-way
     * polymorphic join and ORDER BY the one applicable name expression
     * directly. Unknown class strings fall back to the polymorphic sort
     * so a future addition to the allowlist does not silently NO-OP.
     */
    public function orderByFilteredItemName(string $classString, string $order): self
    {
        $order = strtolower($order) === 'desc' ? 'desc' : 'asc';

        foreach (self::itemNameJoinSpecs() as $spec) {
            if ($spec['class'] !== $classString) {
                continue;
            }

            return self::applyItemNameJoin($this, $spec)
                ->addSelect('action_logs.*')
                ->orderByRaw($spec['nameExpr'].' '.$order);
        }

        return $this->orderByPolymorphicItemName($order);
    }

    /**
     * Shared per-type join specs for item-name sorts. Each entry
     * carries the class string the action_logs.item_type match gates
     * on, the physical table, an alias unique to this scope, and the
     * raw-SQL name expression to COALESCE / ORDER BY.
     *
     * Per-type expressions handle entities whose display name is not
     * just `name`: Asset falls back to asset_tag when name is null or
     * empty, User concatenates first_name and last_name when
     * display_name is empty (honoring name_display_format for last-
     * first locales, #19626), LicenseSeat has no name of its own so a
     * secondary join to its parent license surfaces the license name.
     *
     * NULLIF around display_name and asset.name mirrors the model
     * accessors, which fall back to the full name / tag when the
     * stored value is empty. ConvertEmptyStringsToNull turns '' into
     * null on HTTP writes, but factory, seeder, and import paths can
     * land an actual '' in the column.
     *
     * @return list<array{class: class-string, table: string, alias: string, nameExpr: string, secondary?: array{table: string, alias: string, localKey: string, foreignKey: string}}>
     */
    private static function itemNameJoinSpecs(): array
    {
        $userNameExpr = Setting::getSettings()?->name_display_format === 'last_first'
            ? "COALESCE(NULLIF(poly_user.display_name, ''), CONCAT(poly_user.last_name, ' ', poly_user.first_name))"
            : "COALESCE(NULLIF(poly_user.display_name, ''), CONCAT(poly_user.first_name, ' ', poly_user.last_name))";

        return [
            ['class' => Accessory::class, 'table' => 'accessories', 'alias' => 'poly_accessory', 'nameExpr' => 'poly_accessory.name'],
            ['class' => Asset::class, 'table' => 'assets', 'alias' => 'poly_asset', 'nameExpr' => "COALESCE(NULLIF(poly_asset.name, ''), poly_asset.asset_tag)"],
            ['class' => AssetModel::class, 'table' => 'models', 'alias' => 'poly_assetmodel', 'nameExpr' => 'poly_assetmodel.name'],
            ['class' => Category::class, 'table' => 'categories', 'alias' => 'poly_category', 'nameExpr' => 'poly_category.name'],
            ['class' => Company::class, 'table' => 'companies', 'alias' => 'poly_company', 'nameExpr' => 'poly_company.name'],
            ['class' => Component::class, 'table' => 'components', 'alias' => 'poly_component', 'nameExpr' => 'poly_component.name'],
            ['class' => Consumable::class, 'table' => 'consumables', 'alias' => 'poly_consumable', 'nameExpr' => 'poly_consumable.name'],
            ['class' => Department::class, 'table' => 'departments', 'alias' => 'poly_department', 'nameExpr' => 'poly_department.name'],
            ['class' => License::class, 'table' => 'licenses', 'alias' => 'poly_license', 'nameExpr' => 'poly_license.name'],
            ['class' => Location::class, 'table' => 'locations', 'alias' => 'poly_location', 'nameExpr' => 'poly_location.name'],
            ['class' => Maintenance::class, 'table' => 'maintenances', 'alias' => 'poly_maintenance', 'nameExpr' => 'poly_maintenance.name'],
            ['class' => Manufacturer::class, 'table' => 'manufacturers', 'alias' => 'poly_manufacturer', 'nameExpr' => 'poly_manufacturer.name'],
            ['class' => Supplier::class, 'table' => 'suppliers', 'alias' => 'poly_supplier', 'nameExpr' => 'poly_supplier.name'],
            ['class' => User::class, 'table' => 'users', 'alias' => 'poly_user', 'nameExpr' => $userNameExpr],
        ];
    }

    /**
     * @param  array{class: class-string, table: string, alias: string, nameExpr: string}  $spec
     */
    private static function applyItemNameJoin(self $query, array $spec): self
    {
        $query->leftJoin($spec['table'].' as '.$spec['alias'], function ($join) use ($spec) {
            $join->on('action_logs.item_id', '=', $spec['alias'].'.id')
                ->where('action_logs.item_type', '=', $spec['class']);
        });

        return $query;
    }
}
