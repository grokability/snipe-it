<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Builders\MaintenanceQueryBuilder;
use App\Models\Contracts\CompanyableChild;
use App\Models\Traits\CompanyableChildTrait;
use App\Models\Traits\HasCalendarEvents;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\MaintenancesPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Asset Maintenances.
 *
 * @version v1.0
 *
 * @property-read \App\Models\Asset|null $asset
 * @property-read \App\Models\Actionlog|null $assetlog
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \App\Models\MaintenanceType|null $maintenanceType
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\User|null $responsibleParty
 * @property-read \App\Models\User|null $completedByUser
 * @property-read \Illuminate\Database\Eloquent\Model|null $item Polymorphic parent (usually Asset). See item() below.
 *
 * @mixin IdeHelperMaintenance
 */
class Maintenance extends SnipeModel implements CompanyableChild
{
    use CompanyableChildTrait;
    use HasCalendarEvents;
    use HasFactory;
    use HasUploads;
    use Loggable, Presentable;
    use SoftDeletes;
    use ValidatingTrait;

    protected $presenter = MaintenancesPresenter::class;

    protected $with = ['asset', 'asset.company'];

    protected $table = 'maintenances';

    protected $rules = [
        // item_id is the polymorphic FK. Legacy asset_id keeps working
        // via the accessor / mutator pair below. Both mutators set
        // item_id so the validator's item_id rule catches either.
        'item_id' => 'required|integer',
        'item_type' => 'required|string',
        'supplier_id' => 'nullable|integer',
        'maintenance_type_id' => 'required|integer|exists:maintenance_types,id',
        'name' => 'required|max:100',
        'is_warranty' => 'boolean',
        'start_date' => 'required|date',
        'expected_completion_date' => 'date|nullable|after_or_equal:start_date',
        'notes' => 'string|nullable',
        'cost' => 'numeric|nullable|gte:0|max:99999999999999999.99',
        'url' => 'nullable|url|max:255',
        'responsible_party_id' => 'nullable|integer|exists:users,id',
        'completed_by' => 'nullable|integer|exists:users,id',
        'completed_at' => 'nullable|date|after_or_equal:start_date|before_or_equal:now',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'expected_completion_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        // Polymorphic item FK. Legacy asset_id is kept fillable so
        // Maintenance::create(['asset_id' => ...]) still works via the
        // setAssetIdAttribute mutator that forwards to item_id +
        // item_type=Asset. New callers should pass item_id + item_type
        // directly.
        'item_id',
        'item_type',
        'asset_id',
        'supplier_id',
        'asset_maintenance_type',
        'maintenance_type_id',
        'is_warranty',
        'start_date',
        'expected_completion_date',
        // Legacy alias for expected_completion_date. Kept fillable so an
        // API v1 caller doing Maintenance::create(['completion_date' =>
        // ...]) or ->update(...) still routes through the mutator that
        // forwards to the renamed column.
        'completion_date',
        'asset_maintenance_time',
        'notes',
        'cost',
        'url',
        'checked_out_to_id',
        'checked_out_to_type',
        'responsible_party_id',
        'completed_at',
        'completed_by',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes =
        [
            'name',
            'notes',
            'cost',
            'start_date',
            'expected_completion_date',
        ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'asset' => ['name', 'asset_tag', 'serial'],
        'asset.model' => ['name', 'model_number'],
        'asset.supplier' => ['name'],
        'asset.status' => ['name'],
        'asset.company' => ['name'],
        'asset.location' => ['name'],
        'asset.defaultLoc' => ['name'],
        'supplier' => ['name'],
        'adminuser' => ['first_name', 'last_name', 'display_name'],
        'maintenanceType' => ['name'],
    ];

    public function getCompanyableParents()
    {
        return ['asset'];
    }

    /**
     * Advanced-search filterable fields. The asset_tag entry's field
     * key matches the bs-table column.field so the modal widget-swap
     * replaces the auto-generated text input with the select2
     * checkboxes picker.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filterableFields(): array
    {
        return [
            [
                'field' => 'asset_tag',
                'type' => 'fk',
                'column' => 'item_id',
                'endpoint' => 'hardware',
                'label' => trans('general.assets'),
            ],
            [
                // Field name matches the bs-table column.field in the
                // maintenances presenter so the modal's widget-swap
                // finds the auto-generated carrier input and replaces
                // the plain-text picker with a select2 checkboxes
                // against the maintenance-types selectlist.
                'field' => 'maintenance_type_details',
                'type' => 'fk',
                'column' => 'maintenance_type_id',
                'endpoint' => 'maintenance-types',
                'label' => trans('admin/maintenances/form.asset_maintenance_type'),
            ],
            [
                // status_label is a derived display column that reads
                // from the related asset's status_id. applyCustomFilter
                // walks that relation via whereHas so the picker still
                // writes a familiar statuslabels id CSV.
                'field' => 'status_label',
                'type' => 'fk',
                'column' => null,
                'endpoint' => 'statuslabels',
                'label' => trans('admin/hardware/table.status'),
            ],
            [
                'field' => 'supplier',
                'type' => 'fk',
                'column' => 'supplier_id',
                'endpoint' => 'suppliers',
                'label' => trans('general.supplier'),
            ],
            [
                'field' => 'created_by',
                'type' => 'fk',
                'column' => 'created_by',
                'endpoint' => 'users',
                'label' => trans('general.created_by'),
            ],
            [
                'field' => 'completed_by',
                'type' => 'fk',
                'column' => 'completed_by',
                'endpoint' => 'users',
                'label' => trans('admin/maintenances/form.completed_by'),
            ],
            [
                'field' => 'responsible_party',
                'type' => 'fk',
                'column' => 'responsible_party_id',
                'endpoint' => 'users',
                'label' => trans('admin/maintenances/form.responsible_party'),
            ],
            [
                'field' => 'is_warranty',
                'type' => 'boolean',
                'label' => trans('admin/maintenances/form.is_warranty'),
            ],
            [
                'field' => 'cost',
                'type' => 'numeric',
                'label' => trans('admin/maintenances/form.cost'),
            ],
            [
                'field' => 'asset_maintenance_time',
                'type' => 'numeric',
                'label' => trans('admin/maintenances/form.asset_maintenance_time'),
            ],
            [
                'field' => 'start_date',
                'type' => 'date-range',
                'label' => trans('admin/maintenances/form.start_date'),
            ],
            [
                'field' => 'expected_completion_date',
                'type' => 'date-range',
                'label' => trans('admin/maintenances/form.completion_date'),
            ],
            [
                'field' => 'completed_at',
                'type' => 'date-range',
                'label' => trans('admin/maintenances/form.completed_at'),
            ],
            [
                'field' => 'created_at',
                'type' => 'date-range',
                'label' => trans('general.created_at'),
            ],
            [
                'field' => 'updated_at',
                'type' => 'date-range',
                'label' => trans('general.updated_at'),
            ],
        ];
    }

    /**
     * Override hook from Searchable. asset_tag picks against the
     * hardware selectlist, so route the chosen ids to item_id AND
     * pin item_type = Asset so the whereIn does not collide with a
     * future non-Asset maintenance that happens to share one of
     * those numeric ids. status_label walks the related asset's
     * status_id since the maintenances table has no status column.
     */
    protected function applyCustomFilter(\Illuminate\Database\Eloquent\Builder $query, string $filterKey, string $filterValue, string $boolean = 'and'): ?\Illuminate\Database\Eloquent\Builder
    {
        if ($filterKey === 'asset_tag') {
            $parsed = $this->parseIdsAndNullSentinel($filterValue);
            if ($parsed === null) {
                return null;
            }
            [$ids, $includeNull] = $parsed;

            $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
            $query->{$outerBoolean}(function ($nested) use ($ids, $includeNull) {
                $nested->where('maintenances.item_type', Asset::class);
                if (!empty($ids)) {
                    $nested->whereIn('maintenances.item_id', $ids);
                }
                if ($includeNull) {
                    if (!empty($ids)) {
                        $nested->orWhereNull('maintenances.item_id');
                    } else {
                        $nested->whereNull('maintenances.item_id');
                    }
                }
            });

            return $query;
        }

        if ($filterKey === 'status_label') {
            $parsed = $this->parseIdsAndNullSentinel($filterValue);
            if ($parsed === null) {
                return null;
            }
            [$ids, $includeNull] = $parsed;
            if (empty($ids) && !$includeNull) {
                return null;
            }

            $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
            $query->{$outerBoolean}(function ($nested) use ($ids, $includeNull) {
                $nested->whereHas('asset', function ($assetQuery) use ($ids, $includeNull) {
                    if (!empty($ids) && $includeNull) {
                        $assetQuery->where(function ($q) use ($ids) {
                            $q->whereIn('assets.status_id', $ids)
                                ->orWhereNull('assets.status_id');
                        });
                    } elseif (!empty($ids)) {
                        $assetQuery->whereIn('assets.status_id', $ids);
                    } else {
                        $assetQuery->whereNull('assets.status_id');
                    }
                });
            });

            return $query;
        }

        return null;
    }

    /**
     * getImprovementOptions
     *
     * @return array
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     */
    public static function getImprovementOptions()
    {
        return [
            trans('admin/maintenances/general.maintenance') => trans('admin/maintenances/general.maintenance'),
            trans('admin/maintenances/general.repair') => trans('admin/maintenances/general.repair'),
            trans('admin/maintenances/general.upgrade') => trans('admin/maintenances/general.upgrade'),
            trans('admin/maintenances/general.pat_test') => trans('admin/maintenances/general.pat_test'),
            trans('admin/maintenances/general.calibration') => trans('admin/maintenances/general.calibration'),
            trans('admin/maintenances/general.software_support') => trans('admin/maintenances/general.software_support'),
            trans('admin/maintenances/general.hardware_support') => trans('admin/maintenances/general.hardware_support'),
            trans('admin/maintenances/general.configuration_change') => trans('admin/maintenances/general.configuration_change'),
        ];
    }

    public function isDeletable()
    {
        return Gate::allows('delete', $this);
    }

    public function setIsWarrantyAttribute($value)
    {
        if ($value == '') {
            $value = 0;
        }
        $this->attributes['is_warranty'] = $value;
    }

    public function setCostAttribute($value)
    {
        $value = Helper::ParseCurrency($value);
        if ($value == 0) {
            $value = null;
        }
        $this->attributes['cost'] = $value;
    }

    public function setNotesAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['notes'] = $value;
    }

    public function setExpectedCompletionDateAttribute($value)
    {
        if ($value == '' || $value == '0000-00-00' || $value == '0000-00-00 00:00:00') {
            $value = null;
        }
        $this->attributes['expected_completion_date'] = $value;
    }

    /**
     * Legacy alias for the old `completion_date` field name (renamed to
     * `expected_completion_date` to match the "Expected Completion" UI
     * label). Kept so API v1 consumers that still write to the old key
     * — either as a mass-assigned Eloquent create/update or as raw HTTP
     * form field — keep working. The API transformer emits both keys
     * on read (see MaintenancesTransformer) for the same reason.
     */
    public function setCompletionDateAttribute($value)
    {
        $this->setExpectedCompletionDateAttribute($value);
    }

    public function getCompletionDateAttribute()
    {
        return $this->expected_completion_date;
    }

    /**
     * asset
     * Get asset for this improvement
     *
     * @return mixed
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     */
    /**
     * Polymorphic parent - an Asset (historically), and potentially
     * an Accessory or other checkoutable model going forward. Every
     * new caller should use ->item; ->asset() below stays as a
     * backward-compat alias that returns the parent only when it IS
     * an Asset (returns null for other item_types).
     */
    public function item()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Legacy alias for ->item kept so existing blades, transformers,
     * and controllers using $maintenance->asset or ->with('asset')
     * keep working during the transition to polymorphic items.
     * No item_type WHERE guard here because it would attach to the
     * assets-table subquery during eager loading (Eloquent scopes the
     * belongsTo child query at the parent side) and reference a
     * non-existent maintenances.item_type column from the assets
     * table context. Every existing maintenance has item_type = Asset
     * per the rename migration's backfill, so the plain belongsTo
     * still returns the right rows today. Once accessories actually
     * carry maintenances, callers should switch to ->item.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'item_id')
            ->withTrashed();
    }

    /**
     * Legacy accessor for the old asset_id column. Returns item_id
     * only when the polymorphic parent is an Asset so it doesn't
     * misrepresent a maintenance attached to an accessory as an
     * asset-scoped one.
     */
    public function getAssetIdAttribute()
    {
        return $this->item_type === Asset::class ? $this->attributes['item_id'] ?? null : null;
    }

    /**
     * Legacy mutator - Maintenance::create(['asset_id' => X]) still
     * works. Sets item_id + pins item_type to Asset since callers
     * using asset_id are, by definition, targeting an Asset.
     */
    public function setAssetIdAttribute($value): void
    {
        $this->attributes['item_id'] = $value;
        $this->attributes['item_type'] = Asset::class;
    }

    /**
     * Get the maintenance logs
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v8.2.2]
     *
     * @return Relation
     */
    public function assetlog()
    {
        return $this->hasMany(Actionlog::class, 'item_id')
            ->where('item_type', '=', self::class)
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')
            ->withTrashed();
    }

    public function maintenanceType()
    {
        return $this->belongsTo(MaintenanceType::class, 'maintenance_type_id');
    }

    public function responsibleParty()
    {
        return $this->belongsTo(User::class, 'responsible_party_id')
            ->withTrashed();
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by')
            ->withTrashed();
    }

    public function checkedOutTo()
    {
        return $this->morphTo('checked_out_to');
    }

    public function calendarEventCompanyId(): ?int
    {
        // loadMissing covers the observer path where the created hook
        // fires before the $with eager-load has run. Without it the
        // observer writes company_id=null and the FMCS scope filter
        // on the read path hides the row from legitimate viewers.
        return $this->loadMissing('asset')->asset?->company_id;
    }

    /**
     * Fields published to the calendar_events index table via the
     * HasCalendarEvents trait. A single range event per maintenance
     * anchored on start_date -> expected_completion_date. The
     * completed_at column is intentionally NOT its own event today -
     * "completed" is a state, not a scheduled item, and the read
     * path can flip a completed marker via the source model. Add
     * more entries here if a maintenance ever needs multiple event
     * markers on the calendar.
     */
    public function calendarEventDefinitions(): array
    {
        return [
            [
                'field' => 'start_date',
                'event_type' => 'maintenance.start',
                'end_field' => 'expected_completion_date',
                'all_day' => true,
            ],
        ];
    }

    public function journal()
    {
        return $this->assetlog()->where('action_type', '=', 'note added');
    }

    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public function newEloquentBuilder($query): MaintenanceQueryBuilder
    {
        return new MaintenanceQueryBuilder($query);
    }
}
