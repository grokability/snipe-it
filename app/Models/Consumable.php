<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Models\Builders\ConsumableQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\Acceptable;
use App\Models\Traits\AdjustsQuantity;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasOrders;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Requestable;
use App\Models\Traits\Searchable;
use App\Presenters\ConsumablePresenter;
use App\Presenters\Presentable;
use App\Rules\RequestableArchivedMutex;
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
 * @mixin IdeHelperConsumable
 */
class Consumable extends SnipeModel implements Importable
{
    use HasFactory;

    protected $presenter = ConsumablePresenter::class;

    use Acceptable;
    use AdjustsQuantity;
    use CompanyableTrait;
    use HasOrders;
    use HasUploads;
    use Loggable, Presentable;
    use Requestable;
    use SoftDeletes;

    protected $table = 'consumables';

    protected $casts = [
        'requestable' => 'boolean',
        'archived' => 'boolean',
        'category_id' => 'integer',
        'company_id' => 'integer',
        'qty' => 'integer',
        'min_amt' => 'integer',
    ];

    /**
     * validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'qty' => 'required|integer|min:0|max:99999',
        'category_id' => 'required|integer',
        'company_id' => 'integer|nullable|exists:companies,id|fmcs_company',
        'location_id' => 'exists:locations,id|nullable|fmcs_location',
        'min_amt' => 'integer|min:0|max:99999|nullable',
        'purchase_cost' => 'numeric|nullable|gte:0|max:99999999999999999.99',
        'purchase_date' => 'date_format:Y-m-d|nullable',
        'default_supplier_id' => 'nullable|integer|exists:suppliers,id',
        'default_purchase_cost' => 'numeric|nullable|gte:0|max:99999999999999999.99',
        'requestable' => ['nullable', 'boolean'],
        'archived' => ['nullable', 'boolean'],
    ];

    /**
     * PHP 8.2 blocks `new X` in property defaults, so the rule object
     * gets spliced in here rather than in $rules above. Mirrors
     * Accessory::getRules() so the mutex and the no-archive-with-
     * checkouts guard behave identically across checkoutable items.
     */
    public function getRules(): array
    {
        $rules = $this->rules ?? [];
        $mutex = new RequestableArchivedMutex;

        $noArchivingWithOutstanding = function (string $attribute, mixed $value, \Closure $fail): void {
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN) && $this->exists && $this->numOutstanding() > 0) {
                // Consumables close their lifecycle via mark-consumed,
                // not check-in, so the standard archived_blocked_by_checkouts
                // string points admins at the wrong action. Use the
                // outstanding-specific variant that names the right path.
                $fail(trans('general.archived_blocked_by_outstanding', [
                    'item_type' => trans('general.consumable'),
                    'attribute' => $attribute,
                ]));
            }
        };

        $rules['requestable'] = array_merge((array) ($rules['requestable'] ?? []), [$mutex]);
        $rules['archived'] = array_merge((array) ($rules['archived'] ?? []), [$mutex, $noArchivingWithOutstanding]);

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
        'item_no',
        'location_id',
        'manufacturer_id',
        'name',
        'model_number',
        'qty',
        'min_amt',
        'requestable',
        'archived',
        'notes',
        'default_supplier_id',
        'default_purchase_cost',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'item_no',
        'model_number',
        'notes',
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
        // Search by the parent's "typical supplier" template. See the
        // Accessory model for the rationale.
        'defaultSupplier' => ['name'],
        'adminuser' => ['first_name', 'last_name', 'display_name'],
        // See Accessory::$searchableRelations. Search hits order_number
        // through the HasOrders trait's orders() HasManyThrough into
        // the Orders table so historical order references still match.
        'orders' => ['order_number'],
    ];

    /**
     * Advanced-search filterable fields. The purchase_date, remaining, and
     * purchase_cost fields are derived from the Orders / Users
     * pivot and routed through applyCustomFilter below rather than
     * a direct column on consumables.
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
                'field' => 'category',
                'type' => 'fk',
                'column' => 'category_id',
                'endpoint' => 'categories/consumable',
                'label' => trans('general.category'),
            ],
            [
                'field' => 'manufacturer',
                'type' => 'fk',
                'column' => 'manufacturer_id',
                'endpoint' => 'manufacturers',
                'label' => trans('general.manufacturer'),
            ],
            [
                'field' => 'supplier',
                'type' => 'fk',
                'column' => 'default_supplier_id',
                'endpoint' => 'suppliers',
                'label' => trans('general.supplier'),
            ],
            [
                'field' => 'location',
                'type' => 'fk',
                'column' => 'location_id',
                'endpoint' => 'locations',
                'label' => trans('general.location'),
            ],
            [
                'field' => 'created_by',
                'type' => 'fk',
                'column' => 'created_by',
                'endpoint' => 'users',
                'label' => trans('general.created_by'),
            ],
            [
                'field' => 'requestable',
                'type' => 'boolean',
                'label' => trans('admin/hardware/general.requestable'),
            ],
            [
                'field' => 'qty',
                'type' => 'numeric',
                'label' => trans('admin/accessories/general.total'),
            ],
            [
                'field' => 'min_amt',
                'type' => 'numeric',
                'label' => trans('general.min_amt'),
            ],
            [
                'field' => 'remaining',
                'type' => 'numeric',
                'label' => trans('admin/accessories/general.remaining'),
            ],
            [
                'field' => 'purchase_cost',
                'type' => 'numeric',
                'label' => trans('general.last_unit_cost'),
            ],
            [
                'field' => 'purchase_date',
                'type' => 'date-range',
                'label' => trans('general.last_purchase_date'),
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
     * Override hook from Searchable. Same shape as
     * Accessory::applyCustomFilter. purchase_date / purchase_cost
     * route through correlated subqueries on OrderItem (via the
     * HasOrders polymorphic pivot). remaining routes through a
     * subquery on the consumables_users pivot. Default list queries
     * never pay the subquery cost. Only runs when a matching filter
     * key is on the request.
     */
    protected function applyCustomFilter(\Illuminate\Database\Eloquent\Builder $query, string $filterKey, string $filterValue, string $boolean = 'and'): ?\Illuminate\Database\Eloquent\Builder
    {
        if ($filterKey === 'purchase_date') {
            if (!preg_match('/^(\d{4}-\d{2}-\d{2})?\.\.(\d{4}-\d{2}-\d{2})?$/', trim($filterValue), $m)) {
                return null;
            }
            $start = $m[1] !== '' ? $m[1] : null;
            $end = $m[2] !== '' ? $m[2] : null;
            if ($start === null && $end === null) {
                return null;
            }

            $lastDateSub = \App\Models\OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereColumn('order_items.item_id', 'consumables.id')
                ->where('order_items.item_type', static::class)
                ->orderByDesc('order_items.id')
                ->limit(1)
                ->select('orders.purchase_date');

            $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
            $query->{$outerBoolean}(function ($nested) use ($lastDateSub, $start, $end) {
                if ($start !== null && $end !== null) {
                    $nested->whereBetween($lastDateSub, [$start, $end]);
                } elseif ($start !== null) {
                    $nested->where($lastDateSub, '>=', $start);
                } elseif ($end !== null) {
                    $nested->where($lastDateSub, '<=', $end);
                }
            });

            return $query;
        }

        if ($filterKey === 'remaining') {
            $bounds = $this->parseGtLtBounds($filterValue);
            if ($bounds === null) {
                return null;
            }
            [$gt, $lt] = $bounds;

            // numCheckedOut on Consumable walks consumables_users
            // (one pivot row per handout). Filter mirrors that exact
            // shape so advanced-search semantics match the displayed
            // "remaining" column.
            $checkoutCountSub = \Illuminate\Support\Facades\DB::table('consumables_users')
                ->whereColumn('consumables_users.consumable_id', 'consumables.id')
                ->selectRaw('COUNT(*)');

            $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
            $query->{$outerBoolean}(function ($nested) use ($checkoutCountSub, $gt, $lt) {
                [$countSql, $countBindings] = [$checkoutCountSub->toSql(), $checkoutCountSub->getBindings()];
                if ($gt !== null) {
                    $nested->whereRaw(
                        "(consumables.qty - ({$countSql})) > ?",
                        array_merge($countBindings, [$gt])
                    );
                }
                if ($lt !== null) {
                    $nested->whereRaw(
                        "(consumables.qty - ({$countSql})) < ?",
                        array_merge($countBindings, [$lt])
                    );
                }
            });

            return $query;
        }

        if ($filterKey === 'purchase_cost') {
            $bounds = $this->parseGtLtBounds($filterValue);
            if ($bounds === null) {
                return null;
            }
            [$gt, $lt] = $bounds;

            $lastCostSub = \App\Models\OrderItem::query()
                ->whereColumn('order_items.item_id', 'consumables.id')
                ->where('order_items.item_type', static::class)
                ->orderByDesc('order_items.id')
                ->limit(1)
                ->select('order_items.price');

            $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
            $query->{$outerBoolean}(function ($nested) use ($lastCostSub, $gt, $lt) {
                if ($gt !== null) {
                    $nested->where($lastCostSub, '>', $gt);
                }
                if ($lt !== null) {
                    $nested->where($lastCostSub, '<', $lt);
                }
            });

            return $query;
        }

        return null;
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
        static::forceDeleted(function (self $consumable) {
            // Pivot rows + uploads + image hold the item's lifecycle
            // state. Keep them intact across soft-delete + restore,
            // and only tear them down when the parent is actually
            // being destroyed. Direct DB purge (snipeit:purge)
            // bypasses Eloquent, so the Purge command carries the
            // same cleanup for consumables_users in its childTables
            // map.
            $consumable->users()->detach();

            if ($consumable->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Consumables->publicPath().$consumable->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($consumable->uploads as $upload) {
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
     * Normalize the requestable form input so an empty string from an
     * unchecked checkbox lands as false rather than a truthy "0" cast
     * (matches Accessory::setRequestableAttribute).
     */
    public function setRequestableAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['requestable'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Mirror for the archived flag. Normalizes empty-string / null /
     * missing-column CSV cells to false so the NOT NULL `archived`
     * column never receives a literal null.
     */
    public function setArchivedAttribute($value): void
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['archived'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && ($this->numOutstanding() === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Establishes the component -> assignments relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function consumableAssignments()
    {
        return $this->hasMany(ConsumableAssignment::class);
    }

    /**
     * Pivot rows the recipient has not yet reported as used up.
     * `consumed_at` is one-way: once set, the row never re-enters the
     * outstanding bucket. Backs numOutstanding() and the archive +
     * delete gates.
     */
    public function outstandingAssignments()
    {
        return $this->consumableAssignments()->outstanding();
    }

    public function percentRemaining()
    {
        if ($this->consumables_users_count == 0) {
            return 100;
        }

        if (($this->qty == '') || ($this->qty == 0)) {
            return 0;
        }

        return ($this->qty - $this->consumables_users_count) / $this->qty * 100;
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
     * Establishes the component -> manufacturer relationship
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
     * Gets the full image url for the consumable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return string | false
     */
    public function getImageUrl($path = null)
    {
        // If there is a consumable image, use that
        if ($this->image) {
            return Storage::disk('public')->url(FileStorage::Consumables->publicPath().$this->image);

            // Otherwise check for a category image
        } elseif (($this->category) && ($this->category->image)) {
            return Storage::disk('public')->url(FileStorage::Categories->publicPath().e($this->category->image));
        }

        return false;
    }

    /**
     * Establishes the component -> users relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'consumables_users', 'consumable_id', 'assigned_to')->withPivot('created_by')->withTrashed()->withTimestamps();
    }

    /**
     * Same as users() but without FMCS scoping applied so counts
     * reflect every unit checked out regardless of the viewer's company.
     *
     * For withCount in the list controllers prefer consumableAssignments:
     * this relation INNER JOINs users for a count that never reads any
     * user field, which is dead weight on fleets with large
     * consumables_users pivots. Kept intact for any caller that needs
     * the actual User objects (e.g. iterating recipients).
     */
    public function unconstrainedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'consumables_users', 'consumable_id', 'assigned_to')
            ->withTrashed()
            ->withoutGlobalScope(new CompanyableScope);
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
    // Callers use `$consumable->orders` or `$consumable->lastOrderDefaults()`.

    /**
     * Parent-level "typical supplier" template — see Accessory model.
     */
    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    /**
     * Determine whether to send a checkin/checkout email based on
     * asset model category
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v4.0]
     *
     * @return bool
     */
    public function requireAcceptance()
    {
        return $this->category?->require_acceptance ?? false;
    }

    /**
     * Check how many items within a consumable are checked out
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v5.0]
     *
     * @return int
     */
    public function numCheckedOut()
    {
        // Fallback walks the consumableAssignments hasMany so this
        // path matches the shape the list controllers use via withCount
        // (single-table COUNT on consumables_users, no users JOIN).
        return $this->consumables_users_count ?? $this->consumableAssignments()->count();
    }

    /**
     * Units that have been handed out but the recipient has not yet
     * reported as used up. Feeds the archive + delete gates (the
     * historical stockroom-remaining math still runs off
     * numCheckedOut).
     */
    public function numOutstanding(): int
    {
        return (int) ($this->outstanding_assignments_count
            ?? $this->outstandingAssignments()->count());
    }

    /**
     * Units that have been distributed and reported used up. Permanent
     * state. A consumed row never re-enters the outstanding bucket.
     * Admins see this as the "Consumed" column so they can read how
     * much of lifetime stock has actually been destroyed.
     */
    public function numConsumed(): int
    {
        return (int) $this->numCheckedOut() - $this->numOutstanding();
    }

    /**
     * Units that are either still on the shelf OR distributed-but-not-
     * yet-consumed. The low-inventory alert threshold runs off this
     * count so a consumable only trips the "below minimum" alarm when
     * stock is actually destroyed, not merely handed out to someone
     * who may still return it or who the admin can chase up. Shelf
     * remaining = qty - distributed stays the headline "available
     * right now" number and drives the per-checkout quantity cap.
     */
    public function numInPlay(): int
    {
        return (int) $this->qty - $this->numConsumed();
    }

    /**
     * AdjustsQuantity trait hook: units currently distributed to users.
     * The adjust-quantity modal uses this to reject decrements that
     * would leave the on-hand qty below what's already handed out.
     */
    public function currentlyInUseCount(): int
    {
        return (int) $this->numCheckedOut();
    }

    /**
     * Checks the number of available consumables
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
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
     * Get the list of checkouts for this consumable
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

    public function newEloquentBuilder($query): ConsumableQueryBuilder
    {
        return new ConsumableQueryBuilder($query);
    }
}
