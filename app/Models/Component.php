<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Components.
 *
 * @version    v1.0
 */
class Component extends SnipeModel
{
    use HasFactory;

    protected $presenter = \App\Presenters\ComponentPresenter::class;
    use CompanyableTrait;
    use Loggable, Presentable;
    use SoftDeletes;
    protected $casts = [
        'purchase_date' => 'datetime',
    ];
    protected $table = 'components';

    /**
     * Category validation rules
     */
    public $rules = [
        'name'           => 'required|min:3|max:191',
        'qty'            => 'required|integer|min:1',
        'category_id'    => 'required|integer|exists:categories,id',
        'supplier_id'    => 'nullable|integer|exists:suppliers,id',
        'company_id'     => 'integer|nullable|exists:companies,id',
        'location_id'    => 'exists:locations,id|nullable|fmcs_location',
        'min_amt'        => 'integer|min:0|nullable',
        'purchase_date'   => 'date_format:Y-m-d|nullable',
        'purchase_cost'  => 'numeric|nullable|gte:0|max:9999999999999',
        'manufacturer_id'   => 'integer|exists:manufacturers,id|nullable',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'company_id',
        'supplier_id',
        'location_id',
        'manufacturer_id',
        'model_number',
        'name',
        'purchase_cost',
        'purchase_date',
        'min_amt',
        'order_number',
        'qty',
        'serial',
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
        'order_number',
        'serial',
        'purchase_cost',
        'purchase_date',
        'notes',
        'model_number',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'category'     => ['name'],
        'company'      => ['name'],
        'location'     => ['name'],
        'supplier'     => ['name'],
        'manufacturer' => ['name'],
    ];


    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && ($this->numCheckedOut() === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Establishes the components -> action logs -> uploads relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since [v6.1.13]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function uploads()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')
            ->where('item_type', '=', self::class)
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->orderBy('created_at', 'desc');
    }


    /**
     * Establishes the component -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    /**
     * Establishes the component -> assets relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->belongsToMany(\App\Models\Asset::class, 'components_assets')->withPivot('id', 'assigned_qty', 'created_at', 'created_by', 'note');
    }

    /**
     * Establishes the component -> admin user relationship
     *
     * @todo this is probably not needed - refactor
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Establishes the component -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Establishes the component -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    /**
     * Establishes the item -> supplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.1.1]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }


    /**
     * Establishes the item -> manufacturer relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Establishes the component -> action logs relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assetlog()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')->where('item_type', self::class)->orderBy('created_at', 'desc')->withTrashed();
    }

    /**
     * Check how many items within a component are checked out
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.0]
     * @return int
     */
    public function numCheckedOut()
    {
        $checkedout = 0;

        // In case there are elements checked out to assets that belong to a different company
        // than this asset and full multiple company support is on we'll remove the global scope,
        // so they are included in the count.
        return $this->uncontrainedAssets->sum('pivot.assigned_qty');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *
     * This allows us to get the assets with assigned components without the company restriction
     */
    public function uncontrainedAssets() {

        return $this->belongsToMany(\App\Models\Asset::class, 'components_assets')
                ->withPivot('id', 'assigned_qty', 'created_at', 'created_by', 'note')
                ->withoutGlobalScope(new CompanyableScope);

    }


    /**
     * Check how many items within a component are remaining
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return int
     */
    public function numRemaining()
    {
        return $this->qty - $this->numCheckedOut();
    }


    /**
     * -----------------------------------------------
     * BEGIN MUTATORS
     * -----------------------------------------------
     **/

    /**
     * This sets a value for qty if no value is given. The database does not allow this
     * field to be null, and in the other areas of the code, we set a default, but the importer
     * does not.
     *
     * This simply checks that there is a value for quantity, and if there isn't, set it to 0.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since v6.3.4
     * @param $value
     * @return void
     */
    public function setQtyAttribute($value)
    {
        $this->attributes['qty'] = (!$value) ? 0 : intval($value);
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/


    /**
     * Query builder scope to order on company
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCategory($query, $order)
    {
        return $query->join('categories', 'components.category_id', '=', 'categories.id')->orderBy('categories.name', $order);
    }

    /**
     * Query builder scope to order on company
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations', 'components.location_id', '=', 'locations.id')->orderBy('locations.name', $order);
    }

    /**
     * Query builder scope to order on company
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCompany($query, $order)
    {
        return $query->leftJoin('companies', 'components.company_id', '=', 'companies.id')->orderBy('companies.name', $order);
    }

    /**
     * Query builder scope to order on supplier
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderSupplier($query, $order)
    {
        return $query->leftJoin('suppliers', 'components.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.name', $order);
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->leftJoin('manufacturers', 'components.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
    }

    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'components.created_by', '=', 'admin_sort.id')->select('components.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
