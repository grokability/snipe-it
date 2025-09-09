<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Watson\Validating\ValidatingTrait;

class Department extends SnipeModel
{
    use CompanyableTrait;
    use HasFactory;

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    use ValidatingTrait, UniqueUndeletedTrait;

    protected $casts = [
        'manager_id'   => 'integer',
        'location_id'  => 'integer',
        'company_id'   => 'integer',
    ];

    protected $rules = [
        'name'                  => 'required|max:255|is_unique_department',
        'location_id'           => 'numeric|nullable',
        'company_id'            => 'numeric|nullable',
        'manager_id'            => 'numeric|nullable',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by',
        'name',
        'phone',
        'fax',
        'location_id',
        'company_id',
        'manager_id',
        'notes',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'notes', 'phone', 'fax'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    /**
     * Establishes the department -> company relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Establishes the department -> users relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'department_id');
    }

    /**
     * Establishes the department -> manager relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    /**
     * Establishes the department -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    /**
     * Query builder scope to order on location name
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations as department_location', 'departments.location_id', '=', 'department_location.id')->orderBy('department_location.name', $order);
    }

    /**
     * Query builder scope to order on manager name
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManager($query, $order)
    {
        return $query->leftJoin('users as department_user', 'departments.manager_id', '=', 'department_user.id')->orderBy('department_user.first_name', $order)->orderBy('department_user.last_name', $order);
    }

    /**
     * Query builder scope to order on company
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCompany($query, $order)
    {
        return $query->leftJoin('companies as company_sort', 'departments.company_id', '=', 'company_sort.id')->orderBy('company_sort.name', $order);
    }
}
