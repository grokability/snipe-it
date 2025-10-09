<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Traits\Acceptable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Searchable;
use App\Presenters\ConsumablePresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

class Consumable extends SnipeModel
{
    use HasFactory;

    protected $presenter = ConsumablePresenter::class;
    use CompanyableTrait;
    use Loggable, Presentable;
    use SoftDeletes;
    use Acceptable;
    use HasUploads;

    protected $table = 'consumables';
    protected $casts = [
        'purchase_date' => 'datetime',
        'requestable'    => 'boolean',
        'category_id'    => 'integer',
        'company_id'     => 'integer',
        'supplier_id',
        'qty'            => 'integer',
        'min_amt'        => 'integer',    
     ];

    /**
     * Category validation rules
     */
    public $rules = [
        'name'        => 'required|min:3|max:255',
        'qty'         => 'required|integer|min:0|max:99999',
        'category_id' => 'required|integer',
        'company_id'  => 'integer|nullable',
        'location_id' => 'exists:locations,id|nullable|fmcs_location',
        'min_amt'     => 'integer|min:0|max:99999|nullable',
        'purchase_cost'     =>  'numeric|nullable|gte:0|max:99999999999999999.99',
        'purchase_date'   => 'date_format:Y-m-d|nullable',
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
        'item_no',
        'location_id',
        'manufacturer_id',
        'supplier_id',
        'name',
        'order_number',
        'model_number',
        'purchase_cost',
        'purchase_date',
        'qty',
        'min_amt',
        'requestable',
        'notes',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'order_number', 'purchase_cost', 'purchase_date', 'item_no', 'model_number', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'category'     => ['name'],
        'company'      => ['name'],
        'location'     => ['name'],
        'manufacturer' => ['name'],
        'supplier'     => ['name'],
    ];




    /**
     * Sets the attribute of whether or not the consumable is requestable
     *
     * This isn't really implemented yet, as you can't currently request a consumable
     * however it will be implemented in the future, and we needed to include
     * this method here so all of our polymorphic methods don't break.
     *
     * @todo Update this comment once it's been implemented
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function setRequestableAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['requestable'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Establishes the consumable -> admin user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Establishes the component -> assignments relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function consumableAssignments()
    {
        return $this->hasMany(ConsumableAssignment::class);
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
     * Establishes the component -> manufacturer relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
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
        return $this->belongsTo(Location::class, 'location_id');
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
        return $this->belongsTo(Category::class, 'category_id');
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
        return $this->hasMany(Actionlog::class, 'item_id')->where('item_type', self::class)->orderBy('created_at', 'desc')->withTrashed();
    }

    /**
     * Gets the full image url for the consumable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return string | false
     */
    public function getImageUrl()
    {
        // If there is a consumable image, use that
        if ($this->image) {
            return Storage::disk('public')->url(app('consumables_upload_path').$this->image);

        // Otherwise check for a category image
        }   elseif (($this->category) && ($this->category->image)) {
            return Storage::disk('public')->url(app('categories_upload_path').e($this->category->image));
        }

        return false;
    }

    /**
     * Establishes the component -> users relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     */
    public function users() : Relation
    {
        return $this->belongsToMany(User::class, 'consumables_users', 'consumable_id', 'assigned_to')->withPivot('created_by')->withTrashed()->withTimestamps();
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
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }


    /**
     * Determine whether to send a checkin/checkout email based on
     * asset model category
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return bool
     */
    public function checkin_email()
    {
        return $this->category?->checkin_email;
    }

    /**
     * Determine whether this asset requires acceptance by the assigned user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return bool
     */
    public function requireAcceptance()
    {
        return $this->category->require_acceptance;
    }

    /**
     * Check how many items within a consumable are checked out
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v5.0]
     * @return int
     */
    public function numCheckedOut()
    {
        return $this->consumables_users_count ?? $this->users()->count();
    }

    /**
     * Checks the number of available consumables
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return int
     */
    public function numRemaining()
    {
        $checkedout = $this->numCheckedOut();
        $total = $this->qty;
        $remaining = $total - $checkedout;

        return $remaining;
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
                        $query->where('consumables.name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'notes') {
                        $query->where('consumables.notes', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'model_number') {
                        $query->where('consumables.model_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'order_number') {
                        $query->where('consumables.order_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'item_no') {
                        $query->where('consumables.item_no', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'serial') {
                        $query->where('consumables.serial', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'purchase_cost') {
                        $query->where('consumables.purchase_cost', 'LIKE', '%' . $search_val . '%');
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
        return $query->join('categories', 'consumables.category_id', '=', 'categories.id')->orderBy('categories.name', $order);
    }

    /**
     * Query builder scope to order on location
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations', 'consumables.location_id', '=', 'locations.id')->orderBy('locations.name', $order);
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->leftJoin('manufacturers', 'consumables.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
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
        return $query->leftJoin('companies', 'consumables.company_id', '=', 'companies.id')->orderBy('companies.name', $order);
    }

    /**
     * Query builder scope to order on remaining
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderRemaining($query, $order)
    {
        $order_by = 'consumables.qty - consumables_users_count ' . $order;
        return $query->orderByRaw($order_by);
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
        return $query->leftJoin('suppliers', 'consumables.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.name', $order);
    }

    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as users_sort', 'consumables.created_by', '=', 'users_sort.id')->select('consumables.*')->orderBy('users_sort.first_name', $order)->orderBy('users_sort.last_name', $order);
    }
}
