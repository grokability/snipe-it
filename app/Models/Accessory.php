<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Traits\Acceptable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Accessories.
 *
 * @version v1.0
 */
class Accessory extends SnipeModel
{
    use HasFactory;

    protected $presenter = \App\Presenters\AccessoryPresenter::class;
    use CompanyableTrait;
    use HasUploads;
    use Loggable, Presentable;
    use SoftDeletes;

    protected $table = 'accessories';
    protected $casts = [
        'purchase_date' => 'datetime',
        'requestable' => 'boolean',    ];

    use Searchable;
    use Acceptable;
    
    /**
     * The attributes that should be included when searching the model.
     * 
     * @var array
     */
    protected $searchableAttributes = ['name', 'model_number', 'order_number', 'purchase_date', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     * 
     * @var array
     */
    protected $searchableRelations = [
        'category'     => ['name'],
        'company'      => ['name'],
        'manufacturer' => ['name'],
        'supplier'     => ['name'],
        'location'     => ['name'],
    ];

    /**
     * Accessory validation rules
     */
    public $rules = [
        'name'              => 'required|min:3|max:255',
        'qty'               => 'required|integer|min:1',
        'category_id'       => 'required|integer|exists:categories,id',
        'company_id'        => 'integer|nullable',
        'location_id'       => 'exists:locations,id|nullable|fmcs_location',
        'min_amt'           => 'integer|min:0|nullable',
        'purchase_cost'     =>  'numeric|nullable|gte:0|max:99999999999999999.99',
        'purchase_date'     => 'date_format:Y-m-d|nullable',
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
        'location_id',
        'name',
        'order_number',
        'purchase_cost',
        'purchase_date',
        'model_number',
        'manufacturer_id',
        'supplier_id',
        'image',
        'qty',
        'min_amt',
        'requestable',
        'notes',
    ];


    /**
     * Establishes the accessory -> supplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }


    /**
     * Sets the requestable attribute on the accessory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return void
     */
    public function setRequestableAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['requestable'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Establishes the accessory -> company relationship
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
     * Establishes the accessory -> location relationship
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
     * Establishes the accessory -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id')->where('category_type', '=', 'accessory');
    }

    /**
     * Returns the action logs associated with the accessory
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
     * Get the LAST checkout for this accessory.
     * 
     * This is kinda gross, but is necessary for how the accessory
     * pivot stuff works for now.
     *
     * It looks like we should be able to use ->first() here and
     * return an object instead of a collection, but we actually
     * cannot.
     *
     * In short, you cannot execute the query defined when you're eager loading.
     * and in order to avoid 1001 query problems when displaying the most
     * recent checkout note, we have to eager load this.
     *
     * This means we technically return a collection of one here, and then
     * in the controller, we convert that collection to an array, so we can
     * use it in the transformer to display only the notes of the LAST
     * checkout.
     *
     * It's super-mega-assy, but it's the best I could do for now.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v5.0.0
     *
     * @see \App\Http\Controllers\Api\AccessoriesController\checkedout()
     */
    public function lastCheckout()
    {
        return $this->assetlog()->where('action_type', '=', 'checkout')->take(1);
    }


    /**
     * Sets the full image url
     *
     * @todo this should probably be moved out of the model and into a
     * presenter or service provider
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return string
     */
    public function getImageUrl()
    {
        if ($this->image) {
            return Storage::disk('public')->url(app('accessories_upload_path').$this->image);
        }
        return false;

    }

    /**
     * Establishes the accessory -> users relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function checkouts()
    {
        return $this->hasMany(\App\Models\AccessoryCheckout::class, 'accessory_id')
            ->with('assignedTo');
    }

    /**
     * Establishes the accessory -> admin user relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v7.0.13]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Checks whether or not the accessory has users
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return int
     */
    public function hasUsers()
    {
        return $this->hasMany(\App\Models\AccessoryCheckout::class, 'accessory_id')
            ->where('assigned_type', User::class)
            ->count();
    }

    /**
     * Establishes the accessory -> manufacturer relationship
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
     * Determins whether or not an email should be sent for checkin/checkout of this
     * accessory based on the category it belongs to.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return bool
     */
    public function checkin_email()
    {
        return $this->category?->checkin_email;
    }

    /**
     * Determines whether or not the accessory should require the user to
     * accept it via email.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return bool
     */
    public function requireAcceptance()
    {
        return $this->category->require_acceptance ?? false;
    }

    /**
     * Check how many items within an accessory are checked out
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v5.0]
     * @return int
     */
    public function numCheckedOut()
    {
        return $this->checkouts_count ?? $this->checkouts()->count();
    }


    /**
     * Check how many items of an accessory remain.
     *
     * In order to use this model method, you MUST call withCount('checkouts as checkouts_count')
     * on the eloquent query in the controller, otherwise $this->checkouts_count will be null and
     * bad things happen.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return int
     */
    public function numRemaining()
    {
        $checkedout = $this->numCheckedOut();
        $total = $this->qty;
        $remaining = $total - $checkedout;

        return  $remaining;
    }

    /**
     * Run after the checkout acceptance was declined by the user
     * 
     * @param User   $acceptedBy
     * @param string $signature
     */
    public function declinedCheckout(User $declinedBy, $signature)
    {
        if (is_null($accessory_checkout = AccessoryCheckout::userAssigned()->where('assigned_to', $declinedBy->id)->where('accessory_id', $this->id)->latest('created_at'))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory_checkout->limit(1)->delete();
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
                        $query->where('accessories.name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'notes') {
                        $query->where('accessories.notes', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'model_number') {
                        $query->where('accessories.model_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'order_number') {
                        $query->where('accessories.order_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'purchase_cost') {
                        $query->where('accessories.purchase_cost', 'LIKE', '%' . $search_val . '%');
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
     * Query builder scope to order on created_by name
     */
    public function scopeOrderByCreatedByName($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'accessories.created_by', '=', 'admin_sort.id')->select('accessories.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
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
        return $query->leftJoin('companies', 'accessories.company_id', '=', 'companies.id')
            ->orderBy('companies.name', $order);
    }

    /**
     * Query builder scope to order on category
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCategory($query, $order)
    {
        return $query->leftJoin('categories', 'accessories.category_id', '=', 'categories.id')
            ->orderBy('categories.name', $order);
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
        return $query->leftJoin('locations', 'accessories.location_id', '=', 'locations.id')
            ->orderBy('locations.name', $order);
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
        return $query->leftJoin('manufacturers', 'accessories.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
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
        return $query->leftJoin('suppliers', 'accessories.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.name', $order);
    }
}
