<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Builders\DepartmentQueryBuilder;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\DepartmentPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperDepartment
 */
class Department extends SnipeModel
{
    use CompanyableTrait;
    use HasFactory;
    use HasUploads;
    use Loggable;
    use SoftDeletes;

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    protected $presenter = DepartmentPresenter::class;

    use Presentable, UniqueUndeletedTrait, ValidatingTrait;

    protected $casts = [
        'manager_id' => 'integer',
        'location_id' => 'integer',
        'company_id' => 'integer',
    ];

    protected $rules = [
        'name' => 'required|string|max:255|is_unique_across_company_and_location:departments,name',
        'location_id' => 'numeric|nullable|exists:locations,id|fmcs_location',
        'company_id' => 'numeric|nullable|exists:companies,id|fmcs_company',
        'manager_id' => 'numeric|nullable|exists:users,id|fmcs_manager',
        'phone' => 'string|max:255|nullable',
        'fax' => 'string|max:255|nullable',
        'notes' => 'string|max:255|nullable',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'name',
        'phone',
        'fax',
        'location_id',
        'company_id',
        'manager_id',
        'tag_color',
        'notes',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'notes',
        'phone',
        'fax',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'adminuser' => ['first_name', 'last_name', 'display_name'],
        'company' => ['name'],
        'location' => ['name'],
        'manager' => ['first_name', 'last_name', 'display_name'],
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
                'field' => 'company',
                'type' => 'fk',
                'column' => 'company_id',
                'endpoint' => 'companies',
                'label' => trans('general.company'),
            ],
            [
                'field' => 'location',
                'type' => 'fk',
                'column' => 'location_id',
                'endpoint' => 'locations',
                'label' => trans('general.location'),
            ],
            [
                'field' => 'manager',
                'type' => 'fk',
                'column' => 'manager_id',
                'endpoint' => 'users',
                'label' => trans('admin/departments/table.manager'),
            ],
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

    /**
     * On hard-delete, wipe the image file and Files-tab attachments.
     * Soft-delete leaves everything alone so a restore comes back with
     * the image + files intact. The attachment action_log rows get
     * soft-deleted (not hard-deleted) so the audit trail of what was
     * attached-and-when survives even after the parent is gone.
     */
    protected static function booted(): void
    {
        static::forceDeleted(function (self $department) {
            if ($department->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Departments->publicPath().$department->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($department->uploads as $upload) {
                if (($path = $upload->uploads_file_path()) !== null) {
                    try {
                        Storage::delete($path);
                    } catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }
                }
                $upload->delete();
            }
        });
    }

    public function isDeletable()
    {
        return Gate::allows('delete', $this) && (($this->users_count ?? $this->users()->count()) === 0);
    }

    /**
     * Establishes the department -> company relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Establishes the department -> users relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Establishes the department -> manager relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Establishes the department -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function newEloquentBuilder($query): DepartmentQueryBuilder
    {
        return new DepartmentQueryBuilder($query);
    }
}
