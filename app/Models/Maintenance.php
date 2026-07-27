<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Builders\MaintenanceQueryBuilder;
use App\Models\Traits\CompanyableChildTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\MaintenancesPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Asset Maintenances.
 *
 * @version v1.0
 */
class Maintenance extends SnipeModel implements ICompanyableChild
{
    use CompanyableChildTrait;
    use HasFactory;
    use HasUploads;
    use Loggable, Presentable;
    use SoftDeletes;
    use ValidatingTrait;

    protected $presenter = MaintenancesPresenter::class;

    protected $with = ['asset', 'asset.company'];

    protected $table = 'maintenances';

    protected $rules = [
        'asset_id' => 'required|integer',
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
     * @var array
     */
    protected $fillable = [
        'name',
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
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id')
            ->withTrashed();
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
