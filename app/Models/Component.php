<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Components.
 *
 * @version v1.0
 */
class Component extends SnipeModel
{
    use HasFactory;

    protected $presenter = \App\Presenters\ComponentPresenter::class;
    use CompanyableTrait;
    use HasUploads;
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
        'name'           => 'required|max:191',
        'qty'            => 'required|integer|min:1',
        'category_id'    => 'required|integer|exists:categories,id',
        'supplier_id'    => 'nullable|integer|exists:suppliers,id',
        'company_id'     => 'integer|nullable|exists:companies,id',
        'location_id'    => 'exists:locations,id|nullable|fmcs_location',
        'min_amt'        => 'integer|min:0|nullable',
        'purchase_date'   => 'date_format:Y-m-d|nullable',
        'purchase_cost'     =>  'numeric|nullable|gte:0|max:99999999999999999.99',
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

    public static function booted()
    {
        static::saving(function ($model) {
            // We use 'sum_unconstrained_assets' as a 'cache' of the count of the sum of unconstrained assets, but
            // Eloquent will gladly try to save the value of that attribute in the case where we populate it ourselves.
            // But when it gets populated by 'withSum()' - it seems to work fine due to some Eloquent magic I am not
            // aware of. During a save, the quantity may have changed or other aspects may have changed, so
            // "invalidating the 'cache'" seems like a fair choice here.
            unset($model->sum_unconstrained_assets);
        });
    }


    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && ($this->numCheckedOut() === 0)
            && ($this->deleted_at == '');
    }



    /**
     * Establishes the component -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
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
     * @since  [v3.0]
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
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by')->withTrashed();
    }

    /**
     * Establishes the component -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
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
     * @since  [v3.0]
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
     * @since  [v6.1.1]
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
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\Manufacturer::class, 'manufacturer_id');
    }
    /**
     * Determine whether this asset requires acceptance by the assigned user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function requireAcceptance()
    {
        return $this->category->require_acceptance;
    }


    /**
     * Establishes the component -> action logs relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
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
     * @since  [v5.0]
     * @return int
     */
    public function numCheckedOut(bool $recalculate = false)
    {
        /**
         *
         * WARNING: This method caches the result, so if you're doing something
         * that is going to change the number of checked-out items, make sure to pass
         * 'true' as the first parameter to force this to recalculate the number of checked-out
         * items!!!!!
         *
         */

        // In case there are elements checked out to assets that belong to a different company
        // than this asset and full multiple company support is on we'll remove the global scope,
        // so they are included in the count.

        // the 'sum' query returns NULL when there are zero checkouts - which can inadvertently re-trigger the following query
        // for un-checked-out components. So we have to do this very careful process of fetching the 'attributes'
        // of the component, then see if sum_unconstrained_assets exists as an attribute. If it doesn't, we run the
        // query. But if it *does* exist as an attribute - even a null - we skip the query, because that means that this
        // component was fetched using withCount() - and that count *is* accurate, even if null. We just do a quick
        // null-coalesce at the end to zero for the null case.
        $raw_attributes = $this->getAttributes();
        if (!array_key_exists('sum_unconstrained_assets', $raw_attributes) || $recalculate) {
            // This part should *only* run if the component was fetched *without* withCount() (or you've asked to recalculate)
            // NOTE: doing this will add a 'pseudo-attribute' to the component in question, so we need to _remove_ this
            // before we save - so that gets handled in the 'saving' callback defined in the 'booted' method, above.
            $this->sum_unconstrained_assets = $this->unconstrainedAssets()->sum('assigned_qty') ?? 0;
        }
        return $this->sum_unconstrained_assets ?? 0;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *
     * This allows us to get the assets with assigned components without the company restriction
     */
    public function unconstrainedAssets()
    {

        return $this->belongsToMany(\App\Models\Asset::class, 'components_assets')
            ->withPivot('id', 'assigned_qty', 'created_at', 'created_by', 'note')
            ->withoutGlobalScope(new CompanyableScope);

    }

    /**
     * Determine whether to send a checkin/checkout email based on
     * asset model category
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function checkin_email()
    {
        return $this->category?->checkin_email;
    }
    /**
     * Get the list of checkouts for this License
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function checkouts()
    {
        return $this->assetlog()->where('action_type', '=', 'checkout')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Check how many items within a component are remaining
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return int
     */
    public function numRemaining()
    {
        return $this->qty - $this->numCheckedOut();
    }

    public function totalCostSum() {

        return $this->purchase_cost !== null ? $this->qty * $this->purchase_cost : null;
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
     * @since  v6.3.4
     * @param  $value
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
     * Query builder scope to search on text filters for complex Bootstrap Tables API
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $filter JSON array of search keys and terms
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByFilter($query, $filter)
    {
        return $query->where(
            function ($query) use ($filter) {
                foreach ($filter as $fieldname => $search_val) {

                    if ($fieldname == 'name') {
                        $query->where('components.name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'notes') {
                        $query->where('components.notes', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'model_number') {
                        $query->where('components.model_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'order_number') {
                        $query->where('components.order_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'serial') {
                        $query->where('components.serial', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'serial') {
                        $query->where('components.serial', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'purchase_cost') {
                        $query->where('components.purchase_cost', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'location') {
                        $query->whereHas(
                            'location', function ($query) use ($search_val) {
                            $query->where('locations.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }

                    if ($fieldname == 'manufacturer') {
                        $query->whereHas(
                            'manufacturer', function ($query) use ($search_val) {
                            $query->where('manufacturers.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }


                    if ($fieldname == 'supplier') {
                        $query->whereHas(
                            'supplier', function ($query) use ($search_val) {
                            $query->where('suppliers.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }


                    if ($fieldname == 'category') {
                        $query->whereHas(
                            'category', function ($query) use ($search_val) {
                            $query->where('categories.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }

                    if ($fieldname == 'company') {
                        $query->whereHas(
                            'company', function ($query) use ($search_val) {
                            $query->where('companies.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }


                }


            }
        );
    }

    /**
     * Query builder scope to order on company
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
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
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
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
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
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
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
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
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
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
