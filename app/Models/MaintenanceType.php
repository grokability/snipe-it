<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\MaintenanceTypePresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperMaintenanceType
 */
class MaintenanceType extends SnipeModel
{
    use HasFactory;
    use Presentable;
    use Searchable;
    use SoftDeletes;
    use ValidatingTrait;

    protected $presenter = MaintenanceTypePresenter::class;

    protected $table = 'maintenance_types';

    protected $rules = [
        'name' => 'required|max:100|unique:maintenance_types,name,NULL,id,deleted_at,NULL',
    ];

    protected $injectUniqueIdentifier = true;

    protected $fillable = ['name', 'tag_color'];

    protected $searchableAttributes = ['name', 'tag_color'];

    protected $searchableRelations = [
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    /**
     * Advanced-search filterable fields.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filterableFields(): array
    {
        return [
            [
                'field' => 'created_by',
                'type' => 'fk',
                'column' => 'created_by',
                'endpoint' => 'users',
                'label' => trans('general.created_by'),
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

    public function isDeletable(): bool
    {
        return Gate::allows('delete', $this)
            && ($this->deleted_at == '')
            && $this->maintenances()->doesntExist();
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'maintenance_type_id');
    }

    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }
}
