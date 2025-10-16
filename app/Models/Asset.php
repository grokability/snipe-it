<?php

namespace App\Models;

use DB;
use App\Events\CheckoutableCheckedOut;
use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Acceptable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Searchable;
use App\Presenters\AssetPresenter;
use App\Presenters\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use UnexpectedValueException;
use Watson\Validating\ValidatingTrait;
use Arr;
use Illuminate\Support\Str;

/**
 * Model for Assets.
 *
 * @version v1.0
 */
class Asset extends Depreciable
{

    protected $presenter = AssetPresenter::class;
    protected $with = ['model', 'adminuser'];

    use CompanyableTrait;
    use HasUploads;
    use HasFactory, Loggable, Requestable, Presentable, SoftDeletes, ValidatingTrait, UniqueUndeletedTrait;

    public const LOCATION = 'location';
    public const ASSET = 'asset';
    public const USER = 'user';

    use Acceptable;

    /**
     * Run after the checkout acceptance was declined by the user
     *
     * @param User   $acceptedBy
     * @param string $signature
     */
    public function declinedCheckout(User $declinedBy, $signature)
    {
        $this->assigned_to = null;
        $this->assigned_type = null;
        $this->accepted = null;
        $this->save();
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'assets';

    /**
     * Leaving this commented out, since we need to test further, but this would eager load the model relationship every single
     * time the asset model is loaded.
     */
    // protected $with = ['model'];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    protected $casts = [
        'purchase_date' => 'date',
        'eol_explicit' => 'boolean',
        'last_checkout' => 'datetime',
        'last_checkin' => 'datetime',
        'expected_checkin' => 'datetime:m-d-Y',
        'last_audit_date' => 'datetime',
        'next_audit_date' => 'datetime:m-d-Y',
        'model_id' => 'integer',
        'status_id' => 'integer',
        'company_id' => 'integer',
        'location_id' => 'integer',
        'rtd_company_id' => 'integer',
        'supplier_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $rules = [
        'model_id' => ['required', 'integer', 'exists:models,id,deleted_at,NULL', 'not_array'],
        'status_id' => ['required', 'integer', 'exists:status_labels,id'],
        'asset_tag' => ['required', 'min:1', 'max:255', 'unique_undeleted:assets,asset_tag', 'not_array'],
        'name' => ['nullable', 'max:255'],
        'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        'warranty_months' => ['nullable', 'numeric', 'digits_between:0,240'],
        'last_checkout' => ['nullable', 'date_format:Y-m-d H:i:s'],
        'last_checkin' => ['nullable', 'date_format:Y-m-d H:i:s'],
        'expected_checkin' => ['nullable', 'date'],
        'last_audit_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
        'next_audit_date' => ['nullable', 'date'],
        'location_id' => ['nullable', 'exists:locations,id', 'fmcs_location'],
        'rtd_location_id' => ['nullable', 'exists:locations,id', 'fmcs_location'],
        'purchase_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        'serial' => ['nullable', 'string', 'unique_undeleted:assets,serial'],
        'purchase_cost' => ['nullable', 'numeric', 'gte:0', 'max:9999999999999'],
        'supplier_id' => ['nullable', 'exists:suppliers,id'],
        'asset_eol_date' => ['nullable', 'date'],
        'eol_explicit' => ['nullable', 'boolean'],
        'byod' => ['nullable', 'boolean'],
        'order_number' => ['nullable', 'string', 'max:191'],
        'notes' => ['nullable', 'string', 'max:65535'],
        'assigned_to' => ['nullable', 'integer', 'required_with:assigned_type'],
        'assigned_type' => ['nullable', 'required_with:assigned_to', 'in:' . User::class . "," . Location::class . "," . Asset::class],
        'requestable' => ['nullable', 'boolean'],
        'assigned_user' => ['integer', 'nullable', 'exists:users,id,deleted_at,NULL'],
        'assigned_location' => ['integer', 'nullable', 'exists:locations,id,deleted_at,NULL', 'fmcs_location'],
        'assigned_asset' => ['integer', 'nullable', 'exists:assets,id,deleted_at,NULL']
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asset_tag',
        'assigned_to',
        'assigned_type',
        'company_id',
        'image',
        'location_id',
        'model_id',
        'name',
        'notes',
        'order_number',
        'purchase_cost',
        'purchase_date',
        'rtd_location_id',
        'serial',
        'status_id',
        'supplier_id',
        'warranty_months',
        'requestable',
        'last_checkout',
        'expected_checkin',
        'byod',
        'asset_eol_date',
        'eol_explicit',
        'last_audit_date',
        'next_audit_date',
        'last_checkin',
        'last_checkout',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'asset_tag',
        'serial',
        'order_number',
        'purchase_cost',
        'notes',
        'created_at',
        'updated_at',
        'purchase_date',
        'expected_checkin',
        'next_audit_date',
        'last_audit_date',
        'last_checkin',
        'last_checkout',
        'asset_eol_date',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'assetstatus' => ['name'],
        'supplier' => ['name'],
        'company' => ['name'],
        'defaultLoc' => ['name'],
        'location' => ['name'],
        'model' => ['name', 'model_number', 'eol'],
        'model.category' => ['name'],
        'model.manufacturer' => ['name'],
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (Asset $asset) {
            $asset->requests()->forceDelete();
        });

        static::softDeleted(function (Asset $asset) {
            $asset->requests()->delete();
        });
    }

    // To properly set the expected checkin as Y-m-d
    public function setExpectedCheckinAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['expected_checkin'] = $value;
    }


    public function customFieldValidationRules()
    {

        $customFieldValidationRules = [];

        if (($this->model) && ($this->model->fieldset)) {

            foreach ($this->model->fieldset->fields as $field) {

                // this just casts booleans that may come through as strings to an actual boolean type
                // adding !$field->field_encrypted because when the encrypted value comes through it
                // screws things up for the encrypted validation rules (and the encrypted string
                // is not a valid boolean type)
                if ($field->format == 'BOOLEAN' && !$field->field_encrypted) {
                    $this->{$field->db_column} = filter_var($this->{$field->db_column}, FILTER_VALIDATE_BOOLEAN);
                }
            }

            $customFieldValidationRules += $this->model->fieldset->validation_rules();
        }

        return $customFieldValidationRules;

    }



    /**
     * This handles the custom field validation for assets
     *
     * @var array
     */
    public function save(array $params = [])
    {
        $this->rules += $this->customFieldValidationRules();
        return parent::save($params);
    }

    public function getDisplayNameAttribute()
    {
        return $this->present()->name();
    }

    /**
     * Returns the warranty expiration date as Carbon object
     *
     * @return \Carbon\Carbon|null
     */


    protected function warrantyExpires(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => ($attributes['warranty_months'] && $attributes['purchase_date']) ? Carbon::parse($attributes['purchase_date'])->addMonths($attributes['warranty_months']) : null,
        );
    }

    protected function warrantyExpiresFormattedDate(): Attribute
    {

        return Attribute:: make(
             get: fn(mixed $value, array $attributes) => Helper::getFormattedDateObject($this->warrantyExpires, 'date', false)
        );
    }

    protected function warrantyExpiresDiff(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $this->warrantyExpires ? round((Carbon::now()->diffInDays($this->warrantyExpires))) : null,
        );

    }

    protected function warrantyExpiresDiffForHumans(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $this->warrantyExpires ? Carbon::parse($this->warrantyExpires)->diffForHumans() : null,
        );

    }


    protected function lastAuditFormattedDate(): Attribute
    {

        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => Helper::getFormattedDateObject($this->last_audit_date, 'datetime', false)
        );
    }

    protected function lastAuditDiff(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $this->warrantyExpires ? round((Carbon::now()->diffInDays($this->warrantyExpires))) : null,
        );

    }

    protected function lastAuditDiffForHumans(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) =>  $attributes['last_audit_date'] ? Carbon::parse($attributes['last_audit_date'])->diffForHumans() : null,
        );

    }

    protected function nextAuditFormattedDate(): Attribute
    {

        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => Helper::getFormattedDateObject($this->next_audit_date, 'date', false)
        );
    }

    protected function nextAuditDiffInDays(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $attributes['next_audit_date'] ? Carbon::now()->diffInDays($attributes['next_audit_date']) : null,
        );
    }

    protected function nextAuditDiffForHumans(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $attributes['next_audit_date'] ? Carbon::parse($attributes['next_audit_date'])->diffForHumans() : null,
        );

    }

    protected function eolDate(): Attribute
    {

        return Attribute:: make(
            get: function(mixed $value, array $attributes) {
                if ($attributes['asset_eol_date'] && $attributes['eol_explicit'] == '1') {
                    return Carbon::parse($attributes['asset_eol_date']);
                } elseif ($attributes['purchase_date'] && $this->model && ((int) $this->model->eol > 0)) {
                    return Carbon::parse($attributes['purchase_date'])->addMonths((int) $this->model->eol);
                } else {
                    return null;
                }
            }
        );

    }



    protected function eolFormattedDate(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $this->eolDate ? Helper::getFormattedDateObject($this->eolDate, 'date', false) : null,
        );
    }

    protected function eolDiffInDays(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $this->eolDate ? round((Carbon::now()->diffInDays(Carbon::parse($this->eolDate), false,  1))) : null,
        );

    }

    protected function eolDiffForHumans(): Attribute
    {

        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $this->eolDate  ? Carbon::parse($this->eolDate)->diffForHumans() : null,
        );

    }


    /**
     * Establishes the asset -> company relationship
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
     * Determines if an asset is available for checkout.
     * This checks to see if it's checked out to an invalid (deleted) user
     * OR if the assigned_to and deleted_at fields on the asset are empty AND
     * that the status is deployable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return bool
     */
    public function availableForCheckout()
    {

        // This asset is not currently assigned to anyone and is not deleted...
        if ((!$this->assigned_to) && (!$this->deleted_at)) {

            // The asset status is not archived and is deployable
            if (
                ($this->assetstatus) && ($this->assetstatus->archived == '0')
                && ($this->assetstatus->deployable == '1')
            ) {
                return true;

            }
        }
        return false;
    }


    /**
     * Checks the asset out to the target
     *
     * @todo The admin parameter is never used. Can probably be removed.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  User   $user
     * @param  User   $admin
     * @param  Carbon $checkout_at
     * @param  Carbon $expected_checkin
     * @param  string $note
     * @param  null   $name
     * @return bool
     * @since  [v3.0]
     * @return bool
     */
    public function checkOut($target, $admin = null, $checkout_at = null, $expected_checkin = null, $note = null, $name = null, $location = null)
    {
        if (!$target) {
            return false;
        }
        if ($this->is($target)) {
            throw new CheckoutNotAllowed('You cannot check an asset out to itself.');
        }

        if ($expected_checkin) {
            $this->expected_checkin = $expected_checkin;
        }

        $this->last_checkout = $checkout_at;
        $this->name = $name;

        $this->assignedTo()->associate($target);

        if ($location != null) {
            $this->location_id = $location;
        } else {
            if (isset($target->location)) {
                $this->location_id = $target->location->id;
            }
            if ($target instanceof Location) {
                $this->location_id = $target->id;
            }
        }

        $originalValues = $this->getRawOriginal();

        // attempt to detect change in value if different from today's date
        if ($checkout_at && strpos($checkout_at, date('Y-m-d')) === false) {
            $originalValues['action_date'] = date('Y-m-d H:i:s');
        }

        if ($this->save()) {
            if (is_int($admin)) {
                $checkedOutBy = User::findOrFail($admin);
            } elseif ($admin && get_class($admin) === \App\Models\User::class) {
                $checkedOutBy = $admin;
            } else {
                $checkedOutBy = auth()->user();
            }
            event(new CheckoutableCheckedOut($this, $target, $checkedOutBy, $note, $originalValues));

            $this->increment('checkout_counter', 1);

            return true;
        }

        return false;
    }

    /**
     * Sets the detailedNameAttribute
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return string
     */
    public function getDetailedNameAttribute()
    {
        if ($this->assignedto) {
            $user_name = $this->assignedto->present()->name();
        } else {
            $user_name = 'Unassigned';
        }

        return $this->asset_tag . ' - ' . $this->name . ' (' . $user_name . ') ' . ($this->model) ? $this->model->name : '';
    }

    /**
     * Pulls in the validation rules
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return array
     */
    public function validationRules()
    {
        return $this->rules;
    }

    public function customFieldsForCheckinCheckout($checkin_checkout)
    {
        // Check to see if any of the custom fields were included on the form and if they have any values
        if (($this->model) && ($this->model->fieldset) && ($this->model->fieldset->fields)) {

            foreach ($this->model->fieldset->fields as $field) {

                if (($field->{$checkin_checkout} == 1) && (request()->has($field->db_column))) {

                    if ($field->field_encrypted == '1') {

                        if (Gate::allows('assets.view.encrypted_custom_fields')) {
                            if (is_array(request()->input($field->db_column))) {
                                $this->{$field->db_column} = Crypt::encrypt(implode(', ', request()->input($field->db_column)));
                            } else {
                                $this->{$field->db_column} = Crypt::encrypt(request()->get($field->db_column));
                            }
                        }

                    } else {

                        if (is_array(request()->input($field->db_column))) {
                            $this->{$field->db_column} = implode(', ', request()->input($field->db_column));
                        } else {
                            $this->{$field->db_column} = request()->input($field->db_column);
                        }

                    }
                }
            }
        }

    }


    /**
     * Establishes the asset -> depreciation relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function depreciation()
    {
        return $this->hasOneThrough(\App\Models\Depreciation::class, \App\Models\AssetModel::class, 'id', 'id', 'model_id', 'depreciation_id');
    }


    /**
     * Get components assigned to this asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function components()
    {
        return $this->belongsToMany('\App\Models\Component', 'components_assets', 'asset_id', 'component_id')->withPivot('id', 'assigned_qty', 'created_at');
    }


    /**
     * Get depreciation attribute from associated asset model
     *
     * @todo Is this still needed?
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function get_depreciation()
    {
        if (($this->model) && ($this->model->depreciation)) {
            return $this->model->depreciation;
        }
    }


    /**
     * Determines whether the asset is checked out to a user
     *
     * Even though we allow for checkout to things beyond users
     * this method is an easy way of seeing if we are checked out to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     */
    public function checkedOutToUser(): bool
    {
        return $this->assignedType() === self::USER;
    }

    public function checkedOutToLocation(): bool
    {
        return $this->assignedType() === self::LOCATION;
    }

    public function checkedOutToAsset(): bool
    {
        return $this->assignedType() === self::ASSET;
    }

    /**
     * Get the target this asset is checked out to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Gets assets assigned to this asset
     *
     * Sigh.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedAssets()
    {
        return $this->morphMany(self::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Establishes the accessory -> asset assignment relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedAccessories()
    {
        return $this->morphMany(\App\Models\AccessoryCheckout::class, 'assigned', 'assigned_type', 'assigned_to');
    }


    /**
     * Get the asset's location based on the assigned user
     *
     * @todo Refactor this if possible. It's awful.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \ArrayObject
     */
    public function assetLoc($iterations = 1, $first_asset = null)
    {
        if (!empty($this->assignedType())) {
            if ($this->assignedType() == self::ASSET) {
                if (!$first_asset) {
                    $first_asset = $this;
                }
                if ($iterations > 10) {
                    throw new \Exception('Asset assignment Loop for Asset ID: ' . $first_asset->id);
                }
                $assigned_to = self::find($this->assigned_to); //have to do this this way because otherwise it errors
                if ($assigned_to) {
                    return $assigned_to->assetLoc($iterations + 1, $first_asset);
                } // Recurse until we have a final location
            }
            if ($this->assignedType() == self::LOCATION) {
                if ($this->assignedTo) {
                    return $this->assignedTo;
                }

            }
            if ($this->assignedType() == self::USER) {
                if (($this->assignedTo) && $this->assignedTo->userLoc) {
                    return $this->assignedTo->userLoc;
                }
                //this makes no sense
                return $this->defaultLoc;

            }

        }
        return $this->defaultLoc;
    }

    /**
     * Gets the lowercased name of the type of target the asset is assigned to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return string
     */
    public function assignedType()
    {
        return $this->assigned_type ? strtolower(class_basename($this->assigned_type)) : null;
    }



    /**
     * This is annoying, but because we don't say "assets" in our route names, we have to make an exception here
     *
     * @todo - normalize the route names - API endpoint URLS can stay the same
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v6.1.0]
     * @return string
     */
    public function targetShowRoute()
    {
        $route = str_plural($this->assignedType());
        if ($route == 'assets') {
            return 'hardware';
        }

        return $route;

    }


    /**
     * Get the asset's location based on default RTD location
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function defaultLoc()
    {
        return $this->belongsTo(\App\Models\Location::class, 'rtd_location_id');
    }

    /**
     * Get the image URL of the asset.
     *
     * Check first to see if there is a specific image uploaded to the asset,
     * and if not, check for an image uploaded to the asset model.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return string | false
     */
    public function getImageUrl()
    {
        if ($this->image && !empty($this->image)) {
            return Storage::disk('public')->url(app('assets_upload_path') . e($this->image));
        } elseif ($this->model && !empty($this->model->image)) {
            return Storage::disk('public')->url(app('models_upload_path') . e($this->model->image));
        } elseif ($this->model?->category && !empty($this->model->category->image)) {
            return Storage::disk('public')->url(app('categories_upload_path') . e($this->model->category->image));
        }

        return false;
    }


    /**
     * Get the asset's logs
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assetlog()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')
            ->where('item_type', '=', self::class)
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Get the list of checkouts for this asset
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
     * Get the list of audits for this asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function audits()
    {
        return $this->assetlog()->where('action_type', '=', 'audit')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Get the list of checkins for this asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function checkins()
    {
        return $this->assetlog()
            ->where('action_type', '=', 'checkin from')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Get the asset's user requests
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userRequests()
    {
        return $this->assetlog()
            ->where('action_type', '=', 'requested')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }


    /**
     * Get maintenances for this asset
     *
     * @author Vincent Sposato <vincent.sposato@gmail.com>
     * @since  1.0
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function maintenances()
    {
        return $this->hasMany(\App\Models\Maintenance::class, 'asset_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get user who created the item
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }



    /**
     * Establishes the asset -> status relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assetstatus()
    {
        return $this->belongsTo(\App\Models\Statuslabel::class, 'status_id');
    }

    /**
     * Establishes the asset -> model relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function model()
    {
        return $this->belongsTo(\App\Models\AssetModel::class, 'model_id')->withTrashed();
    }

    /**
     * Return the assets with a warranty expiring within x days
     *
     * @param  $days
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return mixed
     */
    public static function getExpiringWarrantyOrEol($days = 30)
    {
        
        return self::where('archived', '=', '0')
            ->NotArchived()
            ->whereNull('deleted_at')
            ->where(function ($query) use ($days) {
                // Check for manual asset EOL first
                $query->where(function ($query) use ($days) {
                    $query->whereNotNull('asset_eol_date')
                        ->whereBetween('asset_eol_date', [Carbon::now(), Carbon::now()->addDays($days)]);
                // Otherwise use the warranty months + purchase date + threshold
                })->orWhere(function ($query) use ($days) {
                    $query->whereNotNull('purchase_date')
                        ->whereNotNull('warranty_months')
                        ->whereBetween('purchase_date', [Carbon::now(), Carbon::now()->addMonths('assets.warranty_months')->addDays($days)]);
                });
            })
            ->orderBy('asset_eol_date', 'ASC')
            ->orderBy('purchase_date', 'ASC')
            ->get();
    }


    /**
     * Establishes the asset -> assigned licenses relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenses()
    {
        return $this->belongsToMany(\App\Models\License::class, 'license_seats', 'asset_id', 'license_id');
    }

    /**
     * Establishes the asset -> license seats relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenseseats()
    {
        return $this->hasMany(\App\Models\LicenseSeat::class, 'asset_id');
    }

    /**
     * Establishes the asset -> aupplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    /**
     * Establishes the asset -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }


    /**
     * Get the next autoincremented asset tag
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return string | false
     */
    public static function autoincrement_asset(int $additional_increment = 0)
    {
        $settings = \App\Models\Setting::getSettings();


        if ($settings->auto_increment_assets == '1') {
            if ($settings->zerofill_count > 0) {
                return $settings->auto_increment_prefix . self::zerofill($settings->next_auto_tag_base + $additional_increment, $settings->zerofill_count);
            }

            return $settings->auto_increment_prefix . ($settings->next_auto_tag_base + $additional_increment);
        } else {
            return false;
        }
    }


    /**
     * Get the next base number for the auto-incrementer.
     *
     * We'll add the zerofill and prefixes on the fly as we generate the number.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return int
     */
    public static function nextAutoIncrement($assets)
    {

        $max = 1;

        foreach ($assets as $asset) {
            $results = preg_match("/\d+$/", $asset['asset_tag'], $matches);

            if ($results) {
                $number = $matches[0];

                if ($number > $max) {
                    $max = $number;
                }
            }
        }


    }



    /**
     * Add zerofilling based on Settings
     *
     * We'll add the zerofill and prefixes on the fly as we generate the number.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return string
     */
    public static function zerofill($num, $zerofill = 3)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
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
        if (($this->model) && ($this->model->category)) {
            return $this->model->category->checkin_email;
        }
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
        if (($this->model) && ($this->model->category)) {
            return $this->model->category->require_acceptance;
        }

        return false;
    }


    /**
     * Determine whether this asset's next audit date is before the last audit date
     *
     * @return bool
     * @since  [v6.4.1]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * */
    public function checkInvalidNextAuditDate()
    {

        // Deliberately parse the dates as Y-m-d (without H:i:s) to compare them
        if ($this->last_audit_date) {
            $last = Carbon::parse($this->last_audit_date)->format('Y-m-d');
        }

        if ($this->next_audit_date) {
            $next = Carbon::parse($this->next_audit_date)->format('Y-m-d');
        }

        if ((isset($last) && (isset($next))) && ($last > $next)) {
            return true;
        }

        return false;
    }

    public function getComponentCost()
    {
        $cost = 0;
        foreach ($this->components as $component) {
            $cost += $component->pivot->assigned_qty * $component->purchase_cost;
        }
        return $cost;
    }

    /**
     * -----------------------------------------------
     * BEGIN MUTATORS
     * -----------------------------------------------
     **/

    /**
     * Make sure the next_audit_date is formatted as Y-m-d.
     *
     * This is kind of dumb and confusing, since we already cast it that way AND it's a date field
     * in the database, but here we are.
     *
     * @param  $value
     * @return void
     */

    protected function nextAuditDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function lastAuditDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    protected function lastCheckout(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    protected function lastCheckin(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    protected function assetEolDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * This sets the requestable to a boolean 0 or 1. This accounts for forms or API calls that
     * explicitly pass the requestable field but it has a null or empty value.
     *
     * This will also correctly parse a 1/0 if "true"/"false" is passed.
     *
     * @param  $value
     * @return void
     */

    protected function requestable(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) filter_var($value, FILTER_VALIDATE_BOOLEAN),
            set: fn($value) => (int) filter_var($value, FILTER_VALIDATE_BOOLEAN),
        );
    }


    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

    /**
     * Run additional, advanced searches.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $terms The search terms
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function advancedTextSearch(Builder $query, array $terms)
    {

        /**
         * Assigned user
         */
        $query = $query->leftJoin(
            'users as assets_users',
            function ($leftJoin) {
                $leftJoin->on('assets_users.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', User::class);
            }
        );

        foreach ($terms as $term) {

            $query = $query
                ->orWhere('assets_users.first_name', 'LIKE', '%' . $term . '%')
                ->orWhere('assets_users.last_name', 'LIKE', '%' . $term . '%')
                ->orWhere('assets_users.jobtitle', 'LIKE', '%' . $term . '%')
                ->orWhere('assets_users.username', 'LIKE', '%' . $term . '%')
                ->orWhere('assets_users.employee_num', 'LIKE', '%' . $term . '%')
                ->orWhereMultipleColumns(
                    [
                        'assets_users.first_name',
                        'assets_users.last_name',
                    ],
                    $term
                );
        }

        /**
         * Assigned location
         */
        $query = $query->leftJoin(
            'locations as assets_locations',
            function ($leftJoin) {
                $leftJoin->on('assets_locations.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', Location::class);
            }
        );

        foreach ($terms as $term) {

            $query = $query->orWhere('assets_locations.name', 'LIKE', '%' . $term . '%');
        }

        /**
         * Assigned assets
         */
        $query = $query->leftJoin(
            'assets as assigned_assets',
            function ($leftJoin) {
                $leftJoin->on('assigned_assets.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', self::class);
            }
        );

        foreach ($terms as $term) {
            $query = $query->orWhere('assigned_assets.name', 'LIKE', '%' . $term . '%');

        }

        return $query;
    }


    /**
     * Query builder scope for hardware
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeHardware($query)
    {
        return $query->where('physical', '=', '1');
    }

    /**
     * Query builder scope for pending assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopePending($query)
    {
        return $query->whereHas(
            'assetstatus',
            function ($query) {
                $query->where('deployable', '=', 0)
                    ->where('pending', '=', 1)
                    ->where('archived', '=', 0);
            }
        );
    }


    /**
     * Query builder scope for searching location
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeAssetsByLocation($query, $location)
    {
        return $query->where(
            function ($query) use ($location) {
                $query->whereHas(
                    'assignedTo',
                    function ($query) use ($location) {
                        $query->where(
                            [
                                ['users.location_id', '=', $location->id],
                                ['assets.assigned_type', '=', User::class],
                            ]
                        )->orWhere(
                                [
                                    ['locations.id', '=', $location->id],
                                    ['assets.assigned_type', '=', Location::class],
                                ]
                            )->orWhere(
                                [
                                    ['assets.rtd_location_id', '=', $location->id],
                                    ['assets.assigned_type', '=', self::class],
                                ]
                            );
                    }
                )->orWhere(
                        function ($query) use ($location) {
                            $query->where('assets.rtd_location_id', '=', $location->id);
                            $query->whereNull('assets.assigned_to');
                        }
                    );
            }
        );
    }


    /**
     * Query builder scope for RTD assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeRTD($query)
    {
        return $query->whereNull('assets.assigned_to')
            ->whereHas(
                'assetstatus',
                function ($query) {
                    $query->where('deployable', '=', 1)
                        ->where('pending', '=', 0)
                        ->where('archived', '=', 0);
                }
            );
    }

    /**
     * Query builder scope for Undeployable assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeUndeployable($query)
    {
        return $query->whereHas(
            'assetstatus',
            function ($query) {
                $query->where('deployable', '=', 0)
                    ->where('pending', '=', 0)
                    ->where('archived', '=', 0);
            }
        );
    }

    /**
     * Query builder scope for non-Archived assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeNotArchived($query)
    {
        return $query->whereHas(
            'assetstatus',
            function ($query) {
                $query->where('archived', '=', 0);
            }
        );
    }

    /**
     * Query builder scope for Assets that are due for auditing, based on the assets.next_audit_date
     * and settings.audit_warning_days.
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on assets that will need to be audited.
     *
     * Due for audit soon:
     * next_audit_date greater than or equal to now (must be in the future)
     * and (next_audit_date - threshold days) <= now ()
     *
     * Example:
     * next_audit_date = May 4, 2025
     * threshold for alerts = 30 days
     * now = May 4, 2019
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v4.6.16
     * @param  Setting $settings
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeDueForAudit($query, $settings)
    {
        $interval = (int) $settings->audit_warning_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval)->format('Y-m-d');

        return $query->whereNotNull('assets.next_audit_date')
            ->whereBetween('assets.next_audit_date', [$today->format('Y-m-d'), $interval_date])
            ->where('assets.archived', '=', 0)
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are OVERDUE for auditing, based on the assets.next_audit_date
     * and settings.audit_warning_days. It checks to see if assets.next audit_date is before now
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on overdue assets.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v4.6.16
     * @param  Setting $settings
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeOverdueForAudit($query)
    {
        return $query->whereNotNull('assets.next_audit_date')
            ->where('assets.next_audit_date', '<', Carbon::now()->format('Y-m-d'))
            ->where('assets.archived', '=', 0)
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are due for auditing OR overdue, based on the assets.next_audit_date
     * and settings.audit_warning_days.
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on assets that will need to be audited.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v4.6.16
     * @param  Setting $settings
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeDueOrOverdueForAudit($query, $settings)
    {

        return $query->where(
            function ($query) {
                $query->OverdueForAudit();
            }
        )->orWhere(
                function ($query) use ($settings) {
                    $query->DueForAudit($settings);
                }
            );
    }


    /**
     * Query builder scope for Assets that are DUE for checkin, based on the assets.expected_checkin
     * and settings.audit_warning_days. It checks to see if assets.expected_checkin is now
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v6.4.0
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeDueForCheckin($query, $settings)
    {
        $interval = (int) $settings->due_checkin_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval)->format('Y-m-d');

        return $query->whereNotNull('assets.expected_checkin')
            ->whereBetween('assets.expected_checkin', [$today->format('Y-m-d'), $interval_date])
            ->where('assets.archived', '=', 0)
            ->whereNotNull('assets.assigned_to')
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are overdue for checkin OR overdue
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v6.4.0
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOverdueForCheckin($query)
    {
        return $query->whereNotNull('assets.expected_checkin')
            ->where('assets.expected_checkin', '<', Carbon::now()->format('Y-m-d'))
            ->where('assets.archived', '=', 0)
            ->whereNotNull('assets.assigned_to')
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are due for checkin OR overdue
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v6.4.0
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeDueOrOverdueForCheckin($query, $settings)
    {
        return $query->where(
            function ($query) {
                $query->OverdueForCheckin();
            }
        )->orWhere(
                function ($query) use ($settings) {
                    $query->DueForCheckin($settings);
                }
            );
    }


    /**
     * Query builder scope for Archived assets counting
     *
     * This is primarily used for the tab counters so that IF the admin
     * has chosen to not display archived assets in their regular lists
     * and views, it will return the correct number.
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeAssetsForShow($query)
    {

        if (Setting::getSettings()->show_archived_in_list != 1) {
            return $query->whereHas(
                'assetstatus',
                function ($query) {
                    $query->where('archived', '=', 0);
                }
            );
        } else {
            return $query;
        }

    }

    /**
     * Query builder scope for Archived assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeArchived($query)
    {
        return $query->whereHas(
            'assetstatus',
            function ($query) {
                $query->where('deployable', '=', 0)
                    ->where('pending', '=', 0)
                    ->where('archived', '=', 1);
            }
        );
    }

    /**
     * Query builder scope for Deployed assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeDeployed($query)
    {
        return $query->where('assigned_to', '>', '0');
    }

    /**
     * Query builder scope for Requestable assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeRequestableAssets($query): Builder
    {
        $table = $query->getModel()->getTable();

        return Company::scopeCompanyables($query->where($table . '.requestable', '=', 1))
            ->whereHas(
                'assetstatus',
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query->where('deployable', '=', 1)
                                ->where('archived', '=', 0); // you definitely can't request something that's archived
                        }
                    )->orWhere('pending', '=', 1); // we've decided that even though an asset may be 'pending', you can still request it
                }
            );
    }


    /**
     * scopeInModelList
     * Get all assets in the provided listing of model ids
     *
     * @param $query
     * @param array $modelIdListing
     *
     * @return  mixed
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function scopeInModelList($query, array $modelIdListing)
    {
        return $query->whereIn('assets.model_id', $modelIdListing);
    }

    /**
     * Query builder scope to get not-yet-accepted assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeNotYetAccepted($query)
    {
        return $query->where('accepted', '=', 'pending');
    }

    /**
     * Query builder scope to get rejected assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeRejected($query)
    {
        return $query->where('accepted', '=', 'rejected');
    }


    /**
     * Query builder scope to get accepted assets
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeAccepted($query)
    {
        return $query->where('accepted', '=', 'accepted');
    }

    /**
     * Query builder scope to search on text for complex Bootstrap Tables API.
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $search Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeAssignedSearch($query, $search)
    {
        $search = explode(' OR ', $search);

        return $query->leftJoin(
            'users as assets_users',
            function ($leftJoin) {
                $leftJoin->on('assets_users.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', User::class);
            }
        )->leftJoin(
                'locations as assets_locations',
                function ($leftJoin) {
                    $leftJoin->on('assets_locations.id', '=', 'assets.assigned_to')
                        ->where('assets.assigned_type', '=', Location::class);
                }
            )->leftJoin(
                'assets as assigned_assets',
                function ($leftJoin) {
                    $leftJoin->on('assigned_assets.id', '=', 'assets.assigned_to')
                        ->where('assets.assigned_type', '=', self::class);
                }
            )->where(
                function ($query) use ($search) {
                    foreach ($search as $search) {
                        $query->whereHas(
                            'model',
                            function ($query) use ($search) {
                                $query->whereHas(
                                    'category',
                                    function ($query) use ($search) {
                                        $query->where(
                                            function ($query) use ($search) {
                                                $query->where('categories.name', 'LIKE', '%' . $search . '%')
                                                    ->orWhere('models.name', 'LIKE', '%' . $search . '%')
                                                    ->orWhere('models.model_number', 'LIKE', '%' . $search . '%');
                                            }
                                        );
                                    }
                                );
                            }
                        )->orWhereHas(
                                'model',
                                function ($query) use ($search) {
                                    $query->whereHas(
                                        'manufacturer',
                                        function ($query) use ($search) {
                                            $query->where(
                                                function ($query) use ($search) {
                                                    $query->where('manufacturers.name', 'LIKE', '%' . $search . '%');
                                                }
                                            );
                                        }
                                    );
                                }
                            )->orWhere(
                                function ($query) use ($search) {
                                    $query->where('assets_users.first_name', 'LIKE', '%' . $search . '%')
                                        ->orWhere('assets_users.last_name', 'LIKE', '%' . $search . '%')
                                        ->orWhere('assets_users.username', 'LIKE', '%' . $search . '%')
                                        ->orWhere('assets_users.jobtitle', 'LIKE', '%' . $search . '%')
                                        ->orWhereMultipleColumns(
                                            [
                                                'assets_users.first_name',
                                                'assets_users.last_name',
                                                'assets_users.jobtitle',
                                            ],
                                            $search
                                        )
                                        ->orWhere('assets_locations.name', 'LIKE', '%' . $search . '%')
                                        ->orWhere('assigned_assets.name', 'LIKE', '%' . $search . '%');
                                }
                            )->orWhere('assets.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('assets.asset_tag', 'LIKE', '%' . $search . '%')
                            ->orWhere('assets.serial', 'LIKE', '%' . $search . '%')
                            ->orWhere('assets.order_number', 'LIKE', '%' . $search . '%')
                            ->orWhere('assets.notes', 'LIKE', '%' . $search . '%');
                    }

                }
            )->withTrashed()->whereNull('assets.deleted_at'); //workaround for laravel bug
    }

    /**
     * Query builder scope to search the department ID of users assigned to assets
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v5.0]
     * @return string | false
     *
     * @return \Illuminate\Database\Query\Builder Modified query builder
     */
    public function scopeCheckedOutToTargetInDepartment($query, $search)
    {
        return $query->leftJoin(
            'users as assets_dept_users',
            function ($leftJoin) {
                $leftJoin->on('assets_dept_users.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', User::class);
            }
        )->where(
                function ($query) use ($search) {
                    $query->whereIn('assets_dept_users.department_id', $search);

                }
            )->withTrashed()->whereNull('assets.deleted_at'); //workaround for laravel bug
    }



    /**
     * Query builder scope to search on text filters for complex Bootstrap Tables API
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $filters JSON array of search keys and terms
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */


    public function scopeByFilter( $query, $filters){
        return $query->where(
            function(Builder $query) use ($filters){
                $query = Asset::scopeDateRangeFilter($query, 'purchase_date', 'purchase_date_start', 'purchase_date_end', $filters);
                $query = Asset::scopeDateRangeFilter($query, 'asset_eol_date', 'asset_eol_date_start', 'asset_eol_date_end', $filters);
                $query = Asset::scopeDateRangeFilter($query, 'assets.created_at', 'created_at_start', 'created_at_end', $filters);
                $query = Asset::scopeDateRangeFilter($query, 'assets.updated_at', 'updated_at_start', 'updated_at_end', $filters);

                $skipFields = [
                    'purchase_date_start', 'purchase_date_end',
                    'asset_eol_date_start', 'asset_eol_date_end',
                    'created_at_start', 'created_at_end',
                    'updated_at_start', 'updated_at_end',
                ];

                //changed to Array and loop through each item
                foreach($filters as $filterItem) {
                    // legacy
                    if (!is_array($filterItem) || !isset($filterItem['field'])) {
                        foreach ($filters as $key => $value){
                            if (is_string($key) && !is_array($value) && !in_array($key, $skipFields)) {
                                $filterObj = [
                                    'field'     => $key,
                                    'value'     => $value,
                                    'operator'  => 'contains', // default
                                    'logic'     => 'AND' // default
                                ];
                                $this->applySingleFilter($query, $filterObj);
                            }
                        }
                        break;
                    }
                    if (!isset($filterItem['field'], $filterItem['operator'], $filterItem['logic'], $filterItem['value'])) {
                        continue;
                    }
                    if (in_array($filterItem['field'], $skipFields)) {
                        continue;
                    }
                    $this->applySingleFilter($query, $filterItem);
                }
            }
        );
    }

    /**
    * Apply a single filter object into the query builder, using operator & logic.
    *
    * @param Builder $q
    * @param array $filterObj  keys: field, value, operator, logic
    * @return void
    */
    protected function applySingleFilter(Builder &$q, array $filterObj)
    {
        $fieldname = $filterObj['field'];
        $value     = $filterObj['value'];
        $operator  = strtolower($filterObj['operator'] ?? 'equals'); // "equals" or "contains"
        $logic     = strtoupper($filterObj['logic'] ?? 'AND');       // "AND", "OR", "NOT"

        $callback = function (Builder $inner) use ($fieldname, $value, $operator, $filterObj) {
        // === 1. Custom Field Support ===

        if (Str::startsWith($fieldname, ['cf:', 'custom_field:'])) {
            $fieldLabel = Str::after($fieldname, ':');

            // Try to get the custom field from DB
            $cf = DB::table('custom_fields')
                ->where('name', $fieldLabel)
                ->first();

            if (!$cf) {
                return;
            }

            $column = $cf->db_column ?? null;

            if (!$column || !Schema::hasColumn('assets', $column)) {
                return;
            }

            if ($operator === 'equals') {
                $value = is_array($value) ? $value : [$value];
                $inner->whereIn('assets.' . $column, $value);
            } elseif ($operator === 'contains') {
                $value = is_array($value) ? $value : [$value];
                $inner->where(function ($query) use ($column, $value) {
                    foreach ($value as $v) {
                        $query->orWhere('assets.' . $column, 'LIKE', '%' . $v . '%');
                    }
                });
            }
            return;
        }

        // === 2. Field Mapping for Relational Fields ===
        $simpleFields = [
            'asset_tag'     => 'assets.asset_tag',
            'name'          => 'assets.name',
            'serial'        => 'assets.serial',
            'purchase_date' => 'assets.purchase_date',
            'purchase_cost' => 'assets.purchase_cost',
            'notes'         => 'assets.notes',
            'order_number'  => 'assets.order_number',
        ];

        $relationMap = [
            'model' => [
                'relation' => 'model',
                'id' => 'models.id',
                'name' => 'models.name',
            ],
            'category' => [
                'relation' => 'model.category',
                'id' => 'categories.id',
                'name' => 'categories.name',
            ],
            'manufacturer' => [
                'relation' => 'model.manufacturer',
                'id' => 'manufacturers.id',
                'name' => 'manufacturers.name',
            ],
            'company' => [
                'relation' => 'company',
                'id' => 'companies.id',
                'name' => 'companies.name',
            ],
            'supplier' => [
                'relation' => 'supplier',
                'id' => 'suppliers.id',
                'name' => 'suppliers.name',
            ],
            'location' => [
                'relation' => 'location',
                'id' => 'locations.id',
                'name' => 'locations.name',
            ],
            'rtd_location' => [
                'relation' => 'defaultLoc',
                'id' => 'locations.id',
                'name' => 'locations.name',
            ],
            'status_label' => [
                'relation' => 'assetstatus',
                'id' => 'status_labels.id',
                'name' => 'status_labels.name',
            ],
            'model_number' => [
                'relation' => 'model',
                'column' => 'models.model_number',
            ],
            'jobtitle' => [
                'relation' => 'assignedTo',
                'morph' => true,
                'type' => User::class,
                'column' => 'users.jobtitle',
            ],
            'assigned_to' => [
                'relation' => 'assignedTo',
                'morph' => true,
                'types' => [User::class, Asset::class, Location::class],
            ],
        ];

        // === 3. Simple Fields ===
        if (array_key_exists($fieldname, $simpleFields)) {
            $column = $simpleFields[$fieldname];

        $this->applyWhereWithOperator($inner, $column, $value, $operator);
            return;
        }

        // === 4. Relational or Morph ===
        if (isset($relationMap[$fieldname])) {
            $meta = $relationMap[$fieldname];

            // --- Morph Relation ---
            if (!empty($meta['morph'])) {
                if (is_array($value) && isset($value[0]['assignedType'])){
                    $grouped = collect($value)->groupBy('assignedType');

                    $inner->where(function ($q2) use ($grouped, $meta){
                        foreach ($grouped as $type => $items) {
                            $q2->orWhereHasMorph($meta['relation'],[$type], function ($morphQ) use ($items, $type) {
                                $ids = collect($items)->pluck('assigned_to')->filter((fn($v)=>is_numeric($v)));
                                $names = collect($items)->pluck('assigned_to')->filter(fn($v) => is_string($v));
                                
                                if ($ids->isNotEmpty()){
                                    $morphQ->whereIn('id', $ids);
                                }

                                if ($names->isNotEmpty()){
                                    $morphQ->where(function($query) use ($names, $type) {

                                        foreach ($names as $name) {
                                            if ($type === \App\Models\User::class) {
                                                
                                                $query->orWhere(function($sq) use ($name) {
                                                    $sq->where('first_name', 'LIKE', '%' . $name . '%')
                                                    ->orWhere('last_name', 'LIKE', '%' . $name . '%');
                                                });
                                                
                                            }else{
                                                $query->orWhere('name', 'LIKE', '%' . $name . '%');
                                            }
                                        }
                                    });
                                    }
                                });
                            }
                        });

                    return;
                }

                $types = $meta['types'] ?? [$meta['type']];

                $inner->where(function ($q2) use ($types, $value, $operator, $meta) {
                    foreach ($types as $type) {
                        $q2->orWhereHasMorph($meta['relation'], [$type], function ($morphQ) use ($type, $value, $operator, $meta) {
                            if ($meta['column'] ?? false) {
                                $field = $meta['column'];
                                if (is_array($value)) {
                                    $morphQ->whereIn($field, $value);
                                } else {
                                    $morphQ->where($field, $operator === 'equals' ? '=' : 'LIKE', $operator === 'equals' ? $value : '%' . $value . '%');
                                }
                            } else {
                                if ($type === User::class) {
                                    $morphQ->where(function ($sq) use ($value) {
                                        $sq->where('first_name', 'LIKE', '%' . $value . '%')
                                           ->orWhere('last_name', 'LIKE', '%' . $value . '%');
                                    });
                                } else {
                                    $morphQ->where('name', 'LIKE', '%' . $value . '%');
                                }
                            }
                        });
                    }
                });
                return;
            }

            // --- Normal Relation ---
            $relationPath = explode('.', $meta['relation']);
            $first = array_shift($relationPath);

            $inner->whereHas($first, function ($subQ) use ($relationPath, $value, $operator, $meta) {
                foreach ($relationPath as $relation) {
                    $subQ->whereHas($relation, function ($q) use ($value, $operator, $meta) {
                        $this->applyRelationalValue($q, $value, $operator, $meta);
                    });
                }

                if (empty($relationPath)) {
                    $this->applyRelationalValue($subQ, $value, $operator, $meta);
                }
            });

            return;
        }

        // === 5. Fallback: Direct column ===
        $column = 'assets.' . $fieldname;

        if (!Schema::hasColumn('assets', $fieldname)) {
            return;
        }

        $this->applyWhereWithOperator($inner, $column, $value, $operator);
    };

    // === Apply logic ===
    switch ($logic) {
        case 'NOT':
            $q->whereNot($callback);
            break;
        case 'AND':
        default:
            $q->where($callback);
            break;
    }
}

protected function applyRelationalValue(Builder $q, $value, string $operator, array $meta): void
{
    $idField = $meta['id'] ?? null;
    $nameField = $meta['name'] ?? null;
    $column = $meta['column'] ?? null;

    if ($column) {
        $this->applyWhereWithOperator($q, $column, $value, $operator);
        return;
    }

    // Fallback safety check
    if (!$idField && !$nameField) {
        return;
    }
    $values = is_array($value) ? $value : [$value];

    $ids = array_filter($values, 'is_int');
    $names = array_filter($values, 'is_string');

    $q->where(function ($subQ) use ($ids, $names, $idField, $nameField, $operator) {
        $first = true;

        // IDs only
        if (!empty($ids)) {
            if ($first) {
                $subQ->whereIn($idField, $ids);
                $first = false;
            } else {
                $subQ->orWhereIn($idField, $ids);
            }
        }

        // Names only
        foreach ($names as $name) {
            if ($first) {
                $this->applyWhereWithOperator($subQ, $nameField, $name, $operator);
                $first = false;
            } else {
                $subQ->orWhere(function ($q) use ($nameField, $name, $operator) {
                    $this->applyWhereWithOperator($q, $nameField, $name, $operator);
                });
            }
        }
    });
}

protected function applyWhereWithOperator(Builder $query, string $column, $value, string $operator)
{
    $value = is_array($value) ? $value : [$value];

    if ($operator === 'equals') {
        $query->whereIn($column, $value);
    } else { // contains
        $query->where(function ($q) use ($column, $value) {
            foreach ($value as $v) {
                $q->orWhere($column, 'LIKE', '%' . $v . '%');
            }
        });
    }
}

    private function whereHasMatchSingleItem($query, $relation, $searchValue, $idColumn = 'id', $nameColumn = 'name')
    {
        if (is_int($searchValue)) {
            $query->whereHas($relation, function ($query) use ($idColumn, $searchValue) {
                $query->where($idColumn, $searchValue); // Filter by status label ID
            });
        } else if (is_string($searchValue)) {
            $query->whereHas($relation, function ($query) use ($nameColumn, $searchValue) {
                $query->where($nameColumn, 'LIKE', '%' . $searchValue . '%'); // Filter by status label name
            });
        }
        return $query;
    }

    private function whereHasMatchItemArray($query, $relation, $ids, $names, $idColumn = 'id', $nameColumn = 'name')
    {
        return $query->whereHas($relation, function ($q) use ($ids, $names, $idColumn, $nameColumn) {
            $q->where(function ($sub) use ($ids, $names, $idColumn, $nameColumn) {
                if (!empty($ids)) {
                    $sub->whereIn($idColumn, $ids);
                }
                if (!empty($names)) {
                    $sub->orWhereIn($nameColumn, $names);
                }
            });
        });
    }

    /**
     * Query builder scope to order on model
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderModels($query, $order)
    {
        return $query->join('models as asset_models', 'assets.model_id', '=', 'asset_models.id')->orderBy('asset_models.name', $order);
    }

    /**
     * Query builder scope to order on model number
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderModelNumber($query, $order)
    {
        return $query->leftJoin('models as model_number_sort', 'assets.model_id', '=', 'model_number_sort.id')->orderBy('model_number_sort.model_number', $order);
    }


    /**
     * Query builder scope to order on created_by name
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderByCreatedByName($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'assets.created_by', '=', 'admin_sort.id')->select('assets.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }


    /**
     * Query builder scope to order on assigned user
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderAssigned($query, $order)
    {
        return $query->leftJoin('users as users_sort', 'assets.assigned_to', '=', 'users_sort.id')->select('assets.*')->orderBy('users_sort.first_name', $order)->orderBy('users_sort.last_name', $order);
    }

    /**
     * Query builder scope to order on status
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderStatus($query, $order)
    {
        return $query->join('status_labels as status_sort', 'assets.status_id', '=', 'status_sort.id')->orderBy('status_sort.name', $order);
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
        return $query->leftJoin('companies as company_sort', 'assets.company_id', '=', 'company_sort.id')->orderBy('company_sort.name', $order);
    }


    /**
     * Query builder scope to return results of a category
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeInCategory($query, $category_id)
    {
        return $query->join('models as category_models', 'assets.model_id', '=', 'category_models.id')
            ->join('categories', 'category_models.category_id', '=', 'categories.id')
            ->whereIn('category_models.category_id', (!is_array($category_id) ? explode(',', $category_id) : $category_id));
        //->whereIn('category_models.category_id', $category_id);
    }

    /**
     * Query builder scope to return results of a manufacturer
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByManufacturer($query, $manufacturer_id)
    {
        return $query->join('models', 'assets.model_id', '=', 'models.id')
            ->join('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')->whereIn('models.manufacturer_id', (!is_array($manufacturer_id) ? explode(',', $manufacturer_id) : $manufacturer_id));
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
        return $query->join('models as order_model_category', 'assets.model_id', '=', 'order_model_category.id')
            ->join('categories as category_order', 'order_model_category.category_id', '=', 'category_order.id')
            ->orderBy('category_order.name', $order);
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
        return $query->join('models as order_asset_model', 'assets.model_id', '=', 'order_asset_model.id')
            ->leftjoin('manufacturers as manufacturer_order', 'order_asset_model.manufacturer_id', '=', 'manufacturer_order.id')
            ->orderBy('manufacturer_order.name', $order);
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
        return $query->leftJoin('locations as asset_locations', 'asset_locations.id', '=', 'assets.location_id')->orderBy('asset_locations.name', $order);
    }

    /**
     * Query builder scope to order on default
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderRtdLocation($query, $order)
    {
        return $query->leftJoin('locations as rtd_asset_locations', 'rtd_asset_locations.id', '=', 'assets.rtd_location_id')->orderBy('rtd_asset_locations.name', $order);
    }


    /**
     * Query builder scope to order on supplier name
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderSupplier($query, $order)
    {
        return $query->leftJoin('suppliers as suppliers_assets', 'assets.supplier_id', '=', 'suppliers_assets.id')->orderBy('suppliers_assets.name', $order);
    }

    /**
     * Query builder scope to order on supplier name
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderByJobTitle($query, $order)
    {
        return $query->leftJoin('users as users_sort', 'assets.assigned_to', '=', 'users_sort.id')->select('assets.*')->orderBy('users_sort.jobtitle', $order);
    }

    /**
     * Query builder scope to search on location ID
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $search Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByLocationId($query, $search)
    {
        return $query->where(
            function ($query) use ($search) {
                $query->whereHas(
                    'location',
                    function ($query) use ($search) {
                        $query->where('locations.id', '=', $search);
                    }
                );
            }
        );

    }


    /**
     * Query builder scope to search on depreciation name
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $search Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByDepreciationId($query, $search)
    {
        return $query->join('models', 'assets.model_id', '=', 'models.id')
            ->join('depreciations', 'models.depreciation_id', '=', 'depreciations.id')->where('models.depreciation_id', '=', $search);

    }

    /**
     * Query builder scope to filter by a date range on a given field
     *
     * @param \Illuminate\Database\Eloquent\Builder $query      Query builder instance
     * @param string                                $field      Database column name
     * @param string                                $startKey   Filter array key for start date
     * @param string                                $endKey     Filter array key for end date
     * @param array                                 $filter     Filter array
     *
     * @return \Illuminate\Database\Eloquent\Builder            Modified query builder
     */
    public function scopeDateRangeFilter($query, $field, $startKey, $endKey, $filters)
    {
        $start = null;
        $end = null;

        foreach ($filters as $filter) {
            if (!isset($filter['field'], $filter['value'])) {
                continue;
            }

            if ($filter['field'] === $startKey) {
                $start = $filter['value'];
            }

            if ($filter['field'] === $endKey) {
                $end = $filter['value'];
            }
        }

        if (!empty($start)) {
            $query->whereDate($field, '>=', $start);
        }

        if (!empty($end)) {
            $query->whereDate($field, '<=', $end);
        }

        return $query;
    }

}