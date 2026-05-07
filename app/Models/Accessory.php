<?php

namespace App\Models;

use App\Models\Traits\Acceptable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\AccessoryPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Accessories.
 *
 * @version v1.0
 */
class Accessory extends SnipeModel
{
    use Acceptable;
    use CompanyableTrait;
    use HasFactory;
    use HasUploads;
    use Loggable;
    use Presentable;
    use Searchable;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'accessories';

    protected $casts = [
        'purchase_date' => 'datetime',
        'requestable' => 'boolean',    ];

    protected $presenter = AccessoryPresenter::class;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'created_at',
        'model_number',
        'name',
        'notes',
        'order_number',
        'purchase_cost',
        'purchase_date',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'category' => ['name'],
        'company' => ['name'],
        'location' => ['name'],
        'manufacturer' => ['name'],
        'supplier' => ['name'],
    ];

    protected $searchableCounts = [
        'checkouts_count',
    ];

    /**
     * Accessory validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'qty' => 'nullable|integer|min:0',
        'category_id' => 'required|integer|exists:categories,id',
        'company_id' => 'integer|nullable',
        'location_id' => 'exists:locations,id|nullable|fmcs_location',
        'min_amt' => 'integer|min:0|nullable',
        'purchase_cost' => 'numeric|nullable|gte:0|max:99999999999999999.99',
        'purchase_date' => 'date_format:Y-m-d|nullable',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

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
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function isDeletable()
    {
        return $this->checkouts_count === 0;
    }

    /**
     * Sets the requestable attribute on the accessory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Establishes the accessory -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Establishes the accessory -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->where('category_type', '=', 'accessory');
    }

    /**
     * Returns the action logs associated with the accessory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assetlog()
    {
        return $this->hasMany(Actionlog::class, 'item_id')->where('item_type', self::class)->orderBy('created_at', 'desc')->withTrashed();
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
     *
     * @since  v5.0.0
     * @see checkedout()
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
     *
     * @since  [v3.0]
     *
     * @return string
     */
    public function getImageUrl($path = null)
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
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function checkouts()
    {
        return $this->hasMany(AccessoryCheckout::class, 'accessory_id')
            ->with('assignedTo');
    }

    public function percentRemaining()
    {
        if (($this->qty == '' || $this->qty == 0)) {
            return 0;
        }
        if ($this->checkouts_count == 0) {
            return 100;
        }

        return ($this->qty - $this->checkouts_count) / $this->qty * 100;
    }

    /**
     * Establishes the accessory -> users relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function users()
    {
        return $this->belongsToMany(AccessoryCheckout::class, 'accessories_checkout')
            ->with('assignedTo');
    }

    /**
     * Checks whether or not the accessory has users
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return int
     */
    public function hasUsers()
    {
        return $this->hasMany(AccessoryCheckout::class, 'accessory_id')
            ->where('assigned_type', User::class)
            ->count();
    }

    /**
     * Establishes the accessory -> manufacturer relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Determins whether or not an email should be sent for checkin/checkout of this
     * accessory based on the category it belongs to.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
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
     *
     * @since  [v3.0]
     *
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
     *
     * @since  [v5.0]
     *
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
     *
     * @since  [v3.0]
     *
     * @return int
     */
    public function numRemaining()
    {
        $checkedout = $this->numCheckedOut();
        $total = $this->qty;
        $remaining = $total - $checkedout;

        return $remaining;
    }

    /**
     * Run after the checkout acceptance was declined by the user
     *
     * @param  User  $acceptedBy
     * @param  string  $signature
     */
    public function declinedCheckout(User $declinedBy, $signature)
    {
        if (is_null($accessory_checkout = AccessoryCheckout::userAssigned()->where('assigned_to', $declinedBy->id)->where('accessory_id', $this->id)->latest('created_at'))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory_checkout->limit(1)->delete();
    }

    public function totalCostSum()
    {

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
     *
     * @since  v6.3.4
     *
     * @return void
     */
    public function setQtyAttribute($value)
    {
        $this->attributes['qty'] = (! $value) ? 0 : intval($value);
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

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
     * @param  Builder  $query  Query builder instance
     * @param  text  $order  Order
     * @return Builder Modified query builder
     */
    public function scopeOrderCompany($query, $order)
    {
        return $query->leftJoin('companies', 'accessories.company_id', '=', 'companies.id')
            ->orderBy('companies.name', $order);
    }

    /**
     * Query builder scope to order on category
     *
     * @param  Builder  $query  Query builder instance
     * @param  text  $order  Order
     * @return Builder Modified query builder
     */
    public function scopeOrderCategory($query, $order)
    {
        return $query->leftJoin('categories', 'accessories.category_id', '=', 'categories.id')
            ->orderBy('categories.name', $order);
    }

    /**
     * Query builder scope to order on location
     *
     * @param  Builder  $query  Query builder instance
     * @param  text  $order  Order
     * @return Builder Modified query builder
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations', 'accessories.location_id', '=', 'locations.id')
            ->orderBy('locations.name', $order);
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param  Builder  $query  Query builder instance
     * @param  text  $order  Order
     * @return Builder Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->leftJoin('manufacturers', 'accessories.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
    }

    /**
     * Query builder scope to order on supplier
     *
     * @param  Builder  $query  Query builder instance
     * @param  text  $order  Order
     * @return Builder Modified query builder
     */
    public function scopeOrderSupplier($query, $order)
    {
        return $query->leftJoin('suppliers', 'accessories.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.name', $order);
    }
}
