<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Models\Builders\ComponentQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\Acceptable;
use App\Models\Traits\AdjustsQuantity;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasOrders;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Requestable;
use App\Models\Traits\Searchable;
use App\Presenters\ComponentPresenter;
use App\Presenters\Presentable;
use App\Rules\RequestableArchivedMutex;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Components.
 *
 * @version v1.0
 *
 * @mixin IdeHelperComponent
 */
class Component extends SnipeModel implements Importable
{
    use HasFactory;

    protected $presenter = ComponentPresenter::class;

    use Acceptable;
    use AdjustsQuantity;
    use CompanyableTrait;
    use HasOrders;
    use HasUploads;
    use Loggable, Presentable;
    use Requestable;
    use SoftDeletes;

    protected $casts = [
        'purchase_date' => 'datetime',
        'requestable' => 'boolean',
        'archived' => 'boolean',
    ];

    protected $table = 'components';

    /**
     * Category validation rules
     */
    public $rules = [
        'name' => 'required|max:191',
        'qty' => 'required|integer|min:1',
        'category_id' => 'required|integer|exists:categories,id',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'company_id' => 'integer|nullable|exists:companies,id|fmcs_company',
        'location_id' => 'exists:locations,id|nullable|fmcs_location',
        'min_amt' => 'integer|min:0|nullable',
        'purchase_date' => 'date_format:Y-m-d|nullable',
        'purchase_cost' => 'numeric|nullable|gte:0|max:99999999999999999.99',
        'manufacturer_id' => 'integer|exists:manufacturers,id|nullable',
        'default_supplier_id' => 'nullable|integer|exists:suppliers,id',
        'default_purchase_cost' => 'numeric|nullable|gte:0|max:99999999999999999.99',
        'requestable' => ['nullable', 'boolean'],
        'archived' => ['nullable', 'boolean'],
    ];

    /**
     * PHP 8.2 blocks `new X` in property defaults, so the rule object
     * gets spliced in here rather than in $rules above.
     */
    public function getRules(): array
    {
        $rules = $this->rules ?? [];
        $mutex = new RequestableArchivedMutex;

        $noArchivingWithCheckouts = function (string $attribute, mixed $value, \Closure $fail): void {
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN) && $this->exists && $this->numCheckedOut() > 0) {
                $fail(trans('general.archived_blocked_by_checkouts', ['item_type' => trans('general.component')]));
            }
        };

        $rules['requestable'] = array_merge((array) ($rules['requestable'] ?? []), [$mutex]);
        $rules['archived'] = array_merge((array) ($rules['archived'] ?? []), [$mutex, $noArchivingWithCheckouts]);

        return $rules;
    }

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
    // supplier_id / purchase_date / purchase_cost are intentionally
    // absent. See Accessory::$fillable for the full rationale.
    // default_supplier_id / default_purchase_cost are parent-level
    // "template" values that seed the adjust-quantity modal.
    protected $fillable = [
        'category_id',
        'company_id',
        'location_id',
        'manufacturer_id',
        'model_number',
        'name',
        'min_amt',
        'qty',
        'serial',
        'notes',
        'default_supplier_id',
        'default_purchase_cost',
        'requestable',
        'archived',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'serial',
        'notes',
        'model_number',
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
        // Search by the parent's "typical supplier" template — see the
        // Accessory model for the rationale.
        'defaultSupplier' => ['name'],
        'manufacturer' => ['name'],
        'adminuser' => ['first_name', 'last_name', 'display_name'],
        // See Accessory::$searchableRelations. Search hits order_number
        // through the HasOrders trait's orders() HasManyThrough into
        // the Orders table so historical order references still match.
        'orders' => ['order_number'],
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

        // Archive-time request cleanup. SaveQuietly so the component update doesn't trigger a cascade of
        // request updates and emails. The component is already archived, so the requests are now invalid.
        static::updated(function (self $component): void {
            if (! $component->wasChanged('archived') || ! $component->archived) {
                return;
            }

            $component->openRequests()->get()->each(fn (CheckoutRequest $request) => $request->cancelAndSync());
        });

        // On hard-delete, wipe the image file and Files-tab attachments.
        // Soft-delete leaves everything alone so a restore comes back
        // with the image + files intact. The attachment action_log rows
        // get soft-deleted (not hard-deleted) so the audit trail of what
        // was attached-and-when survives even after the parent is gone.
        static::forceDeleted(function (self $component) {
            if ($component->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Components->publicPath().$component->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($component->uploads as $upload) {
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
        return Gate::allows('delete', $this)
            && ($this->numCheckedOut() === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Normalize the requestable form input so an empty string from an
     * unchecked checkbox lands as false rather than a truthy "0" cast
     * (matches Accessory / Consumable).
     *
     * Kept as the legacy magic-method form rather than the Attribute
     * accessor used by `archived()` below: Requestable() is also a
     * query scope on the ComponentQueryBuilder, and callers like
     * `Component::Requestable()->count()` resolve to this method via
     * Model::__callStatic. An `Attribute`-returning method shadows
     * that forwarder and crashes with "Call to undefined method
     * Attribute::count()".
     */
    public function setRequestableAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['requestable'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return Attribute<bool, mixed>
     */
    protected function archived(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => filter_var($value === '' ? null : $value, FILTER_VALIDATE_BOOLEAN),
        );
    }

    /**
     * Establishes the component -> location relationship
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
     * Establishes the component -> assets relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     */
    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'components_assets')->withPivot('id', 'assigned_qty', 'created_at', 'created_by', 'note');
    }

    /**
     * Per-pivot line cost for components-assets. Pulls the per-unit
     * price from the last acquisition (with the same default_* fallback
     * that lastOrderDefaults() applies) and multiplies by pivot qty.
     *
     * @return Attribute<float|null, never>
     */
    protected function calculatedPurchaseCost(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $unitPurchaseCost = $this->lastOrderDefaults()['unit_cost'] ?? null;
                $assignedQty = $this->pivot?->assigned_qty;

                if ($unitPurchaseCost === null) {
                    return $assignedQty !== null ? 0.0 : null;
                }

                if ($assignedQty !== null) {
                    return (float) $unitPurchaseCost * (int) $assignedQty;
                }

                return (float) $unitPurchaseCost;
            }
        );
    }

    /**
     * Establishes the component -> company relationship
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
     * Establishes the component -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Establishes the item -> supplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v6.1.1]
     *
     * @return Relation
     */
    // No `supplier()` relation, no `supplier_id` / `purchase_date` /
    // `purchase_cost` accessors — see Accessory model for rationale.
    // Callers use `$component->orders` or `$component->lastOrderDefaults()`.

    /**
     * Parent-level "typical supplier" template — see Accessory model.
     */
    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    /**
     * Establishes the item -> manufacturer relationship
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
     * Determine whether this asset requires acceptance by the assigned user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @return bool
     */
    public function requireAcceptance()
    {
        return $this->category?->require_acceptance ?? false;
    }

    /**
     * Establishes the component -> action logs relationship
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
     * Check how many items within a component are checked out
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v5.0]
     *
     * @return int
     */
    public function numCheckedOut(bool $recalculate = false)
    {
        /**
         * WARNING: This method caches the result, so if you're doing something
         * that is going to change the number of checked-out items, make sure to pass
         * 'true' as the first parameter to force this to recalculate the number of checked-out
         * items!!!!!
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
        if (! array_key_exists('sum_unconstrained_assets', $raw_attributes) || $recalculate) {
            // This part should *only* run if the component was fetched *without* withCount() (or you've asked to recalculate)
            // NOTE: doing this will add a 'pseudo-attribute' to the component in question, so we need to _remove_ this
            // before we save - so that gets handled in the 'saving' callback defined in the 'booted' method, above.
            $this->sum_unconstrained_assets = $this->unconstrainedAssets()->sum('assigned_qty') ?? 0;
        }

        return $this->sum_unconstrained_assets ?? 0;
    }

    /**
     * AdjustsQuantity trait hook: units currently assigned to assets.
     * Passes true to numCheckedOut to force a fresh count instead of
     * trusting the cached sum_unconstrained_assets attribute, since the
     * adjust-quantity flow can be entered without withCount() priming
     * that value.
     */
    public function currentlyInUseCount(): int
    {
        return (int) $this->numCheckedOut(true);
    }

    /**
     * @return BelongsToMany
     *
     * This allows us to get the assets with assigned components without the company restriction
     */
    public function unconstrainedAssets()
    {

        return $this->belongsToMany(Asset::class, 'components_assets')
            ->withPivot('id', 'assigned_qty', 'created_at', 'created_by', 'note')
            ->withoutGlobalScope(new CompanyableScope);

    }

    public function percentRemaining()
    {
        $totalQuantity = (int) $this->qty;

        if ($totalQuantity <= 0) {
            return 0;
        }

        $availableQuantity = max(0, min($this->numRemaining(), $totalQuantity));

        return ($availableQuantity / $totalQuantity) * 100;
    }

    /**
     * Determine whether to send a checkin/checkout email based on
     * asset model category
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
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
     *
     * @since  [v2.0]
     *
     * @return Relation
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
     *
     * @since  [v3.0]
     *
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
     *
     * @since  v6.3.4
     *
     * @return void
     */
    public function setQtyAttribute($value)
    {
        $this->attributes['qty'] = (! $value) ? 0 : intval($value);
    }

    public function newEloquentBuilder($query): ComponentQueryBuilder
    {
        return new ComponentQueryBuilder($query);
    }
}
