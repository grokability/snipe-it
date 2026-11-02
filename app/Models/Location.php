<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Builders\LocationQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\LocationPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperLocation
 */
class Location extends SnipeModel implements Importable
{
    use CompanyableTrait;
    use HasFactory;
    use HasUploads;
    use Loggable;
    use Presentable;
    use Searchable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    protected $presenter = LocationPresenter::class;

    protected $table = 'locations';

    protected $rules = [
        'name' => 'required|max:255|unique_undeleted_in_scope:parent_id,company_id',
        'address' => 'max:191|nullable',
        'address2' => 'max:191|nullable',
        'city' => 'max:191|nullable',
        'state' => 'min:2|max:191|nullable',
        'country' => 'min:2|max:191|nullable',
        'zip' => 'max:10|nullable',
        'manager_id' => 'exists:users,id|nullable',
        'parent_id' => 'nullable|exists:locations,id|non_circular:locations,id|parent_matches_location_company',
        'company_id' => 'integer|nullable|exists:companies,id|fmcs_company',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'manager_id' => 'integer',
        'company_id' => 'integer',
    ];

    /**
     * Whether the model should inject its identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'parent_id',
        'address',
        'address2',
        'city',
        'state',
        'country',
        'zip',
        'phone',
        'fax',
        'ldap_ou',
        'currency',
        'manager_id',
        'image',
        'company_id',
        'tag_color',
        'notes',
    ];

    protected $hidden = ['user_id'];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes =
        [
            'name',
            'address',
            'city',
            'state',
            'zip',
            'created_at',
            'ldap_ou',
            'phone',
            'fax',
            'notes',
        ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'parent' => ['name'],
        'company' => ['name'],
        'manager' => ['first_name', 'last_name', 'display_name'],
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
                'field' => 'company',
                'type' => 'fk',
                'column' => 'company_id',
                'endpoint' => 'companies',
                'label' => trans('admin/companies/table.title'),
            ],
            [
                'field' => 'parent',
                'type' => 'fk',
                'column' => 'parent_id',
                'endpoint' => 'locations',
                'label' => trans('admin/locations/table.parent'),
            ],
            [
                'field' => 'manager',
                'type' => 'fk',
                'column' => 'manager_id',
                'endpoint' => 'users',
                'label' => trans('admin/users/table.manager'),
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
        static::forceDeleted(function (self $location) {
            if ($location->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Locations->publicPath().$location->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($location->uploads as $upload) {
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

    /**
     * Determine whether or not this location can be deleted.
     *
     * This method requires the eager loading of the relationships in order to determine whether
     * it can be deleted. It's tempting to load those here, but that increases the query load considerably.
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return bool
     */
    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && ($this->deleted_at == '')
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->assigned_assets_count ?? $this->assignedAssets()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->assigned_accessories_count ?? $this->assignedAccessories()->count()) === 0)
            && (($this->children_count ?? $this->children()->count()) === 0)
            && (($this->components_count ?? $this->components()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->rtd_assets_count ?? $this->rtd_assets()->count()) === 0)
            && (($this->users_count ?? $this->users()->count()) === 0);
    }

    /**
     * Establishes the user -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function users()
    {
        return $this->hasMany(User::class, 'location_id');
    }

    /**
     * Find assets with this location as their location_id
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assets()
    {
        // Pluck IDs then whereIn — do NOT replace with whereHas. whereHas generates a correlated EXISTS per row and causes severe slowdowns in withCount contexts.
        $ids = Statuslabel::where(function ($q) {
            $q->where('deployable', 1)->orWhere('pending', 1)->orWhere('archived', 0);
        })->whereNull('deleted_at')->pluck('id');

        return $this->hasMany(Asset::class, 'location_id')
            ->whereIn('assets.status_id', $ids->isEmpty() ? [0] : $ids);
    }

    public function countAllTheThings()
    {
        return $this->assets()->count() + $this->consumables()->count() + $this->components()->count() + $this->users()->count() + $this->assignedAccessories()->count() + $this->assignedAssets()->count() + $this->accessories()->count();
    }

    /**
     * Establishes the  asset -> rtd_location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function rtd_assets()
    {
        /* This used to have an ...->orHas() clause that referred to
           assignedAssets, and that was probably incorrect, as well as
           definitely was setting fire to the query-planner. So don't do that.

           It is arguable that we should have a '...->whereNull('assigned_to')
           bit in there, but that isn't always correct either (in the case
           where a user has no location, for example).
        */
        return $this->hasMany(Asset::class, 'rtd_location_id');
    }

    /**
     * Establishes the consumable -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'location_id');
    }

    /**
     * Establishes the component -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function components()
    {
        return $this->hasMany(Component::class, 'location_id');
    }

    /**
     * Establishes the component -> accessory relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'location_id');
    }

    /**
     * Find the parent of a location
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id')
            ->with('parent');
    }

    /**
     * Walk up the parent chain to find the nearest ancestor with a company_id.
     * Used by FMCS checkout validation so that assets can be checked out to
     * child locations whose company is only set on a parent location.
     */
    public function effectiveFmcsCompanyId(): ?int
    {
        if ($this->company_id) {
            return (int) $this->company_id;
        }

        $ancestor = $this->parent()->withoutGlobalScopes()->first();
        while ($ancestor) {
            if ($ancestor->company_id) {
                return (int) $ancestor->company_id;
            }
            $ancestor = $ancestor->parent()->withoutGlobalScopes()->first();
        }

        return null;
    }

    /**
     * Establishes the locations -> company relationship
     *
     * @author [T. Regnery] [<tobias.regnery@gmail.com>]
     *
     * @since  [v7.0]
     *
     * @return Relation
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Find the manager of a location
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Find children of a location
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with('children');
    }

    /**
     * Establishes the asset -> location assignment relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assignedAssets()
    {
        return $this->morphMany(Asset::class, 'assigned', 'assigned_type', 'assigned_to')->AssetsForShow()->withTrashed();
    }

    /**
     * Establishes the accessory -> location assignment relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assignedAccessories()
    {
        return $this->morphMany(AccessoryCheckout::class, 'assigned', 'assigned_type', 'assigned_to');
    }

    public function setLdapOuAttribute($ldap_ou)
    {
        return $this->attributes['ldap_ou'] = empty($ldap_ou) ? null : $ldap_ou;
    }

    /**
     * The map's keys are parent_id values, with `0` used for "no parent / top-
     * level". Using 0 (not null) avoids PHP 8.4's deprecation of null array
     * offsets when callers build the map from `$location->parent_id`.
     *
     * `$prefix` is the accumulated indent marker for the current recursion
     * depth (two dashes per level), so `use_text` reads e.g.
     * `-- Rack 1` or `---- Rack 1a` in the dropdown. Locations can nest
     * arbitrarily deep and the previous `A › B › C` breadcrumb form produced
     * unreadable long strings for even modestly nested hierarchies
     * (see #19398). Company::indenter keeps the breadcrumb form because its
     * hierarchy is capped at one parent level and reads cleanly there.
     */
    public static function indenter($locations_with_children, int $parent_id = 0, $prefix = '')
    {
        $results = [];

        if (! array_key_exists($parent_id, $locations_with_children)) {
            return [];
        }

        foreach ($locations_with_children[$parent_id] as $location) {
            $location->use_text = $prefix === ''
                ? $location->name
                : $prefix.' '.$location->name;
            $location->use_image = ($location->image) ? Storage::disk('public')->url(FileStorage::Locations->publicPath().$location->image) : null;
            $results[] = $location;
            if (array_key_exists($location->id, $locations_with_children)) {
                $results = array_merge($results, self::indenter($locations_with_children, $location->id, $prefix.'--'));
            }
        }

        return $results;
    }

    public function newEloquentBuilder($query): LocationQueryBuilder
    {
        return new LocationQueryBuilder($query);
    }
}
