<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Watson\Validating\ValidatingTrait;


class License extends Depreciable
{
    use HasFactory;

    protected $presenter = \App\Presenters\LicensePresenter::class;

    use SoftDeletes;
    use CompanyableTrait;
    use HasUploads;
    use Loggable, Presentable;
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;

    // We set these as protected dates so that they will be easily accessible via Carbon

    public $timestamps = true;

    protected $guarded = 'id';
    protected $table = 'licenses';


    protected $casts = [
        'purchase_date' => 'date',
        'expiration_date' => 'date',
        'termination_date' => 'date',
        'category_id'  => 'integer',
        'company_id'   => 'integer',
    ];

    protected $rules = [
        'name'   => 'required|string|min:3|max:255',
        'seats' => 'required|min:1|integer|limit_change:10000', // limit_change is a "pseudo-rule" that translates into 'between', see prepareLimitChangeRule() below
        'license_email'   => 'email|nullable|max:120',
        'license_name'   => 'string|nullable|max:100',
        'notes'   => 'string|nullable',
        'category_id' => 'required|exists:categories,id',
        'company_id' => 'integer|nullable',
        'purchase_cost'     =>  'numeric|nullable|gte:0|max:99999999999999999.99',
        'purchase_date'   => 'date_format:Y-m-d|nullable|max:10|required_with:depreciation_id',
        'expiration_date'   => 'date_format:Y-m-d|nullable|max:10',
        'termination_date'   => 'date_format:Y-m-d|nullable|max:10',
        'min_amt'   => 'numeric|nullable|gte:0',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'depreciation_id',
        'expiration_date',
        'license_email',
        'license_name', //actually licensed_to
        'maintained',
        'manufacturer_id',
        'category_id',
        'name',
        'notes',
        'order_number',
        'purchase_cost',
        'purchase_date',
        'purchase_order',
        'reassignable',
        'seats',
        'serial',
        'supplier_id',
        'termination_date',
        'created_by',
        'min_amt',
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
        'order_number',
        'purchase_order',
        'purchase_cost',
        'purchase_date',
        'expiration_date',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'manufacturer' => ['name'],
        'company'      => ['name'],
        'category'     => ['name'],
        'depreciation' => ['name'],
        'supplier'     => ['name'],
    ];
    protected $appends = ['free_seat_count'];

    /**
     * Update seat counts when the license is updated
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     */
    public static function boot()
    {
        parent::boot();
        // We need to listen for created for the initial setup so that we have a license ID.
        static::created(
            function ($license) {
                $newSeatCount = $license->getAttributes()['seats'];

                return static::adjustSeatCount($license, 0, $newSeatCount);
            }
        );
        // However, we listen for updating to be able to prevent the edit if we cannot delete enough seats.
        static::updating(
            function ($license) {
                $newSeatCount = $license->getAttributes()['seats'];
                //$oldSeatCount = isset($license->getOriginal()['seats']) ? $license->getOriginal()['seats'] : 0;
                /*
                That previous method *did* mostly work, but if you ever managed to get your $license->seats value out of whack
                with your actual count of license_seats *records*, you would never manage to get back 'into whack'.
                The below method actually grabs a count of existing license_seats records, so it will be more accurate.
                This means that if your license_seats are out of whack, you can change the quantity and hit 'save' and it
                will manage to 'true up' and make your counts line up correctly.
                */
                $oldSeatCount = $license->license_seats_count;

                return static::adjustSeatCount($license, $oldSeatCount, $newSeatCount);
            }
        );
    }


    protected function terminatesFormattedDate(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $attributes['termination_date'] ? Helper::getFormattedDateObject($attributes['termination_date'], 'date', false) : null,
        );
    }

    protected function terminatesDiffInDays(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $attributes['termination_date'] ? Carbon::now()->diffInDays($attributes['termination_date']) : null,
        );
    }

    protected function terminatesDiffForHumans(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $attributes['termination_date'] ? Carbon::parse($attributes['termination_date'])->diffForHumans() : null,
        );
    }


    public function prepareLimitChangeRule($parameters, $field)
    {
        $actual_seat_count = $this->licenseseats()->count(); //we use the *actual* seat count here, in case your license has gone wonky
        $lower_bound = $actual_seat_count - $parameters[0];
        $upper_bound = $actual_seat_count + $parameters[0];
        return ["between", ($lower_bound <= 0 ? 1 : $lower_bound), $upper_bound];
    }

    /**
     * Balance seat counts
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public static function adjustSeatCount($license, $oldSeats, $newSeats)
    {
        // If the seats haven't changed, continue on happily.
        if ($oldSeats == $newSeats) {
            return true;
        }
        // On Create, we just make one for each of the seats.
        $change = abs($oldSeats - $newSeats);
        if ($oldSeats > $newSeats) {

            // Need to delete seats... lets see if if we have enough.
            $seatsAvailableForDelete = $license->licenseseats()->whereNull('assigned_to')->whereNull('asset_id')->limit($change);

            if ($change > $seatsAvailableForDelete->count()) {
                Session::flash('error', trans('admin/licenses/message.assoc_users'));

                return false;
            }
            $seatsAvailableForDelete->delete();

            // Log Deletion of seats.
            $logAction = new Actionlog;
            $logAction->item_type = self::class;
            $logAction->item_id = $license->id;
            $logAction->created_by = auth()->id() ?: 1; // We don't have an id while running the importer from CLI.
            $logAction->note = "deleted {$change} seats";
            $logAction->target_id = null;
            $logAction->logaction('delete seats');

            return true;
        }
        // Else we're adding seats.
        //Create enough seats for the change.
        $licenseInsert = [];
        for ($i = $oldSeats; $i < $newSeats; $i++) {
            $licenseInsert[] = [
                'created_by' => auth()->id(),
                'license_id' => $license->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        //Chunk and use DB transactions to prevent timeouts.

        collect($licenseInsert)->chunk(1000)->each(
            function ($chunk) {
                DB::transaction(
                    function () use ($chunk) {
                        LicenseSeat::insert($chunk->toArray());
                    }
                );
            }
        );

        // On initial create, we shouldn't log the addition of seats.
        if ($license->id) {
            //Log the addition of license to the log.
            $logAction = new Actionlog();
            $logAction->item_type = self::class;
            $logAction->item_id = $license->id;
            $logAction->created_by = auth()->id() ?: 1; // Importer.
            $logAction->note = "added {$change} seats";
            $logAction->target_id = null;
            $logAction->logaction('add seats');
        }

        return true;
    }

    /**
     * Sets the attribute for whether or not the license is maintained
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return mixed
     */
    public function setMaintainedAttribute($value)
    {
        $this->attributes['maintained'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sets the reassignable attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return mixed
     */
    public function setReassignableAttribute($value)
    {
        $this->attributes['reassignable'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sets expiration date attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return mixed
     */
    public function setExpirationDateAttribute($value)
    {
        if ($value == '' || $value == '0000-00-00') {
            $value = null;
        } else {
            $value = (new Carbon($value))->toDateString();
        }
        $this->attributes['expiration_date'] = $value;
    }

    /**
     * Sets termination date attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return mixed
     */
    public function setTerminationDateAttribute($value)
    {
        if ($value == '' || $value == '0000-00-00') {
            $value = null;
        } else {
            $value = (new Carbon($value))->toDateString();
        }
        $this->attributes['termination_date'] = $value;
    }

    public function isInactive(): bool
{
    $day = now()->startOfDay();

    $expired = $this->expiration_date && $this->asDateTime($this->expiration_date)->startofDay()->lessThanOrEqualTo($day);

    $terminated = $this->termination_date && $this->asDateTime($this->termination_date)->startofDay()->lessThanOrEqualTo($day);


        return $this->isExpired() || $this->isTerminated();
}

    public function isExpired(): bool
    {
        $day = now()->startOfDay();

        $expired = $this->expiration_date && $this->asDateTime($this->expiration_date)->startofDay()->lessThanOrEqualTo($day);

        return $expired;
    }

    public function isTerminated(): bool
    {
        $day = now()->startOfDay();

        $terminated = $this->termination_date && $this->asDateTime($this->termination_date)->startofDay()->lessThanOrEqualTo($day);


        return $terminated;
    }

    /**
     * Sets free_seat_count attribute
     *
     * @author G. Martinez
     * @since  [v6.3]
     * @return mixed
     */
    public function getFreeSeatCountAttribute()
    {
        return $this->attributes['free_seat_count'] = $this->remaincount();
    }

    /**
     * Establishes the license -> company relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Establishes the license -> category relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    /**
     * Establishes the license -> manufacturer relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Determine whether the user should be emailed on checkin/checkout
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return bool
     */
    public function checkin_email()
    {
        if ($this->category) {
            return $this->category->checkin_email;
        }
        return false;
    }

    /**
     * Determine whether the user should be required to accept the license
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return bool
     */
    public function requireAcceptance()
    {
        if ($this->category) {
            return $this->category->require_acceptance;
        }

        return false;
    }


    /**
     * Establishes the license -> assigned user relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedusers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'license_seats', 'license_id', 'assigned_to');
    }

    /**
     * Establishes the license -> action logs relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assetlog()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')
            ->where('item_type', '=', self::class)
            ->orderBy('created_at', 'desc');
    }



    /**
     * Establishes the license -> admin user relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Returns the total number of all license seats
     *
     * @todo this can probably be refactored at some point. We don't need counting methods.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return int
     */
    public static function assetcount()
    {
        return LicenseSeat::whereNull('deleted_at')
            ->count();
    }


    /**
     * Return the number of seats for this asset
     *
     * @todo this can also probably be refactored at some point.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function totalSeatsByLicenseID()
    {
        return LicenseSeat::where('license_id', '=', $this->id)
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Establishes the license -> seat relationship
     *
     * We do this to eager load the "count" of seats from the controller.
     * Otherwise calling "count()" on each model results in n+1 sadness.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenseSeatsRelation()
    {
        return $this->hasMany(LicenseSeat::class)->whereNull('deleted_at')->selectRaw('license_id, count(*) as count')->groupBy('license_id');
    }

    /**
     * Sets the license seat count attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return int
     */
    public function getLicenseSeatsCountAttribute()
    {
        if ($this->licenseSeatsRelation->first()) {
            return $this->licenseSeatsRelation->first()->count;
        }

        return 0;
    }

    /**
     * Returns the number of total available seats across all licenses
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return int
     */
    public static function availassetcount()
    {
        return LicenseSeat::whereNull('assigned_to')
            ->whereNull('asset_id')
            ->whereNull('deleted_at')
            ->count();
    }
    /**
     * Returns the available seats remaining
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return int
     */

    /**
     * Returns the number of total available seats for this license
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function availCount()
    {
        return $this->licenseSeatsRelation()
            ->whereNull('asset_id')
            ->whereNull('assigned_to')
            ->where('unreassignable_seat', '=', false)
            ->whereNull('deleted_at');
    }

    /**
     * Sets the available seats attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return mixed
     */
    public function getAvailSeatsCountAttribute()
    {
        if ($this->availCount->first()) {
            return $this->availCount->first()->count;
        }

        return 0;
    }

    /**
     * Retuns the number of assigned seats for this asset
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedCount()
    {
        return $this->licenseSeatsRelation()->where(
            function ($query) {
                $query->whereNotNull('assigned_to')
                    ->orWhereNotNull('asset_id');
            }
        );
    }

    /**
     * Sets the assigned seats attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return int
     */
    public function getAssignedSeatsCountAttribute()
    {
        if ($this->assignedCount->first()) {
            return $this->assignedCount->first()->count;
        }

        return 0;
    }
    /**
     * Calculates the number of unreassignable seats
     *
     * @author G. Martinez
     * @since [v7.1.15]
     */
    public static function unReassignableCount($license) : int
    {
        $count = 0;
        if (!$license->reassignable) {
            $count = LicenseSeat::query()->where('unreassignable_seat', '=', true)
                ->where('license_id', '=', $license->id)
                ->count();
        }
        return $count;
    }
    /**
     * Calculates the number of remaining seats
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return int
     */
    public function remaincount() : int
    {
        $total = $this->licenseSeatsCount;
        $taken = $this->assigned_seats_count;
        $unreassignable = self::unReassignableCount($this);
        $diff = ($total - $taken - $unreassignable);

        return (int) $diff;
    }

    /**
     * Returns the total number of seats for this license
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return int
     */
    public function totalcount()
    {
        $avail = $this->availSeatsCount;
        $taken = $this->assignedcount();
        $diff = ($avail + $taken);

        return $diff;
    }

    /**
     * Establishes the license -> seats relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenseseats()
    {
        return $this->hasMany(\App\Models\LicenseSeat::class);
    }

    /**
     * Establishes the license -> supplier relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }


    /**
     * Gets the next available free seat - used by
     * the API to populate next_seat
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return mixed
     */
    public function freeSeat()
    {
        return  $this->licenseseats()
            ->whereNull('deleted_at')
            ->where('unreassignable_seat', '=', false)
            ->where(function ($query) {
                $query->whereNull('assigned_to')
                    ->whereNull('asset_id');
            })
            ->orderBy('id', 'asc')
            ->first();
    }


    /**
     * Establishes the license -> free seats relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function freeSeats()
    {
        return $this->hasMany(\App\Models\LicenseSeat::class)->whereNull('assigned_to')->whereNull('deleted_at')->whereNull('asset_id');
    }

    public function scopeActiveLicenses($query)
    {

        return $query->whereNull('licenses.deleted_at')

            // The termination date is null or within range
            ->where(function ($query)  {
                $query->whereNull('termination_date')
                    ->orWhereDate('termination_date', '>', [Carbon::now()]);
            })
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>', [Carbon::now()]);
            });
    }

    /**
     * Expiried/terminated licenses scope
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     * @see \App\Console\Commands\SendExpiringLicenseNotifications
     */
    public function scopeExpiredLicenses($query)
    {
        return $query->whereDate('termination_date', '<=', Carbon::now())// The termination date is null or within range
        ->orWhere(function ($query) {
                    $query->whereDate('expiration_date', '<=', Carbon::now());
        })
        ->whereNull('licenses.deleted_at');
    }

    /**
     * Expiring/terminating licenses scope
     *
     * This checks if:
     *
     * 1) The license has not been deleted
     * 2) The expiration date is between now and the number of days specified
     * 3) There is an expiration date set and the termination date has not passed
     * 4) The license termination date is null or has not passed
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     * @see \App\Console\Commands\SendExpiringLicenseNotifications
     */
    public function scopeExpiringLicenses($query, $days = 60)
    {
        return $query// The termination date is null or within range
        ->where(function ($query) use ($days) {
            $query->whereNull('termination_date')
                ->orWhereBetween('termination_date', [Carbon::now(), Carbon::now()->addDays($days)]);
        })
            ->where(function ($query) use ($days) {
                $query->whereNotNull('expiration_date')
                    // Handle expiring licenses without termination dates
                    ->where(function ($query) use ($days) {
                        $query->whereNull('termination_date')
                            ->whereBetween('expiration_date', [Carbon::now(), Carbon::now()->addDays($days)]);
                    })

                    // Handle expiring licenses with termination dates in the future
                    ->orWhere(function ($query) use ($days) {
                        $query->whereBetween('termination_date', [Carbon::now(), Carbon::now()->addDays($days)]);
                    });
            });
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
        return $query->leftJoin('manufacturers', 'licenses.manufacturer_id', '=', 'manufacturers.id')->select('licenses.*')
            ->orderBy('manufacturers.name', $order);
    }

    /**
     * Query builder scope to order on supplier
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderSupplier($query, $order)
    {
        return $query->leftJoin('suppliers', 'licenses.supplier_id', '=', 'suppliers.id')->select('licenses.*')
            ->orderBy('suppliers.name', $order);
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
        return $query->leftJoin('companies as companies', 'licenses.company_id', '=', 'companies.id')->select('licenses.*')
            ->orderBy('companies.name', $order);
    }

    /**
     * Query builder scope to order on the user that created it
     */
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'licenses.created_by', '=', 'admin_sort.id')->select('licenses.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
