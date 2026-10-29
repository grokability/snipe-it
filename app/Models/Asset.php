<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Events\CheckoutableCheckedOut;
use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Builders\AssetQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\Acceptable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasCalendarEvents;
use App\Models\Traits\HasImageUpload;
use App\Models\Traits\HasOrders;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Requestable;
use App\Models\Traits\Searchable;
use App\Presenters\AssetPresenter;
use App\Presenters\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Assets.
 *
 * @version v1.0
 *
 * @property ?int $location_id
 * @property \Carbon\Carbon|string|null $next_audit_date
 * @property \Carbon\Carbon|string|null $last_audit_date
 * @property \Carbon\Carbon|string|null $asset_eol_date
 * @property ?int $company_id
 * @property \Carbon\Carbon|string|null $last_checkin
 * @property bool $requestable
 * @property Asset|Location|User|null $assigned Current checkout target. morphTo relation named `assigned` (declared on method `assignedTo()`). Eloquent resolves the dynamic property by the morphTo name arg, not the method name, so this `@property` is distinct from `@property ... $assignedTo` written by ide-helper.
 * @property Asset|Location|User|null $assignedTo Current checkout target. morphTo relation named `assigned` (declared on method `assignedTo()`). Eloquent resolves the dynamic property by the morphTo name arg, not the method name, so this `@property` is distinct from `@property ... $assigned` written by ide-helper.
 *
 * @mixin IdeHelperAsset
 */
class Asset extends Depreciable implements Importable
{
    protected $presenter = AssetPresenter::class;

    // protected $with = ['model', 'adminuser', 'location', 'company'];

    use CompanyableTrait;
    use HasCalendarEvents;
    use HasFactory;
    use HasImageUpload;
    use HasOrders;
    use HasUploads;
    use Loggable;
    use Presentable;
    use Requestable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    public const LOCATION = 'location';

    public const ASSET = 'asset';

    public const USER = 'user';

    use Acceptable;

    public static function fileStorage(): FileStorage
    {
        return FileStorage::Assets;
    }

    /**
     * Run after the checkout acceptance was declined by the user
     *
     * @param  string  $signature
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
        'expected_checkin' => 'datetime',
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

    /**
     * location_id and company_id should store NULL when there's no
     * assignment, never 0. Old data and previous bugs occasionally
     * left `0` behind (empty select2 → '' → integer-cast → 0), which
     * then breaks `exists:` validation and FMCS queries that treat
     * NULL and 0 as different. `set` normalizes on write, `get`
     * normalizes on read so legacy rows already storing 0 present as
     * null at the model boundary until they're re-saved.
     *
     * @return Attribute<int|null, int|null>
     */
    protected function locationId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value === null || (int) $value === 0) ? null : (int) $value,
            set: fn ($value) => ($value === '' || $value === null || (int) $value === 0) ? null : (int) $value,
        );
    }

    /**
     * @return Attribute<int|null, int|null>
     */
    protected function companyId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value === null || (int) $value === 0) ? null : (int) $value,
            set: fn ($value) => ($value === '' || $value === null || (int) $value === 0) ? null : (int) $value,
        );
    }

    protected $rules = [
        'model_id' => ['required', 'integer', 'exists:models,id,deleted_at,NULL', 'not_array'],
        'status_id' => ['required', 'integer', 'exists:status_labels,id'],
        'asset_tag' => ['required', 'min:1', 'max:255', 'unique_undeleted:assets,asset_tag', 'not_array'],
        'name' => ['nullable', 'max:255'],
        'company_id' => ['nullable', 'integer', 'exists:companies,id', 'fmcs_company'],
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
        'purchase_cost' => ['nullable', 'numeric', 'gte:0', 'max:99999999999999999.99'],
        'supplier_id' => ['nullable', 'exists:suppliers,id'],
        'asset_eol_date' => ['nullable', 'date'],
        'eol_explicit' => ['nullable', 'boolean'],
        'byod' => ['nullable', 'boolean'],
        'order_number' => ['nullable', 'string', 'max:191'],
        'notes' => ['nullable', 'string', 'max:65535'],
        'assigned_to' => ['nullable', 'integer', 'required_with:assigned_type'],
        'assigned_type' => ['nullable', 'required_with:assigned_to', 'in:'.User::class.','.Location::class.','.Asset::class],
        'requestable' => ['nullable', 'boolean'],
        'assigned_user' => ['integer', 'nullable', 'exists:users,id,deleted_at,NULL'],
        'assigned_location' => ['integer', 'nullable', 'exists:locations,id,deleted_at,NULL', 'fmcs_location'],
        'assigned_asset' => ['integer', 'nullable', 'exists:assets,id,deleted_at,NULL'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_tag',
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
        'status' => ['name'],
        'supplier' => ['name'],
        'company' => ['name'],
        'defaultLoc' => ['name'],
        'location' => ['name'],
        'model' => ['name', 'model_number', 'eol'],
        'category' => ['name'],
        'manufacturer' => ['name'],
        'assigned_to' => ['name'],
        'externalSource' => ['primary_mac', 'primary_ip', 'os', 'os_version'],
    ];

    /**
     * Maps the field names exposed by the API / transformers to the actual
     * Eloquent relation names used in $searchableRelations.
     *
     * This lets callers filter using the same key they see in API responses
     * without needing to know the internal relation name.
     *
     * @var array<string, string> [ api_key => relation_name ]
     */
    protected $searchableRelationAliases = [
        'status_label' => 'status',
        'assigned_to' => 'assignedTo',
        'model_number' => 'model',
        'rtd_location' => 'defaultLoc',
    ];

    protected $searchableRelationColumns = [
        'jobtitle' => ['relation' => 'assignedTo', 'column' => 'jobtitle', 'types' => [\App\Models\User::class]],
        'employee_num' => ['relation' => 'assignedTo', 'column' => 'employee_num', 'types' => [\App\Models\User::class]],
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (Asset $asset) {
            $asset->requests()->forceDelete();

            // Image + Files-tab attachments wipe on hard-delete only, so
            // a restored soft-deleted asset keeps its image and files.
            // Attachment action_log rows get soft-deleted (not
            // hard-deleted) so the audit trail of what was attached-
            // and-when survives even after the parent is gone.
            if ($asset->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Assets->publicPath().$asset->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($asset->uploads as $upload) {
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

        static::softDeleted(function (Asset $asset) {
            $asset->requests()->delete();
        });

        // When an asset moves between companies, its own calendar
        // events pick up the new company_id through the HasCalendarEvents
        // trait's updated hook, but child Maintenances resolve their
        // calendar_events company_id from the parent asset and would
        // otherwise keep the stale value until they themselves are
        // touched. Cascade the re-sync here so FMCS filtering stays
        // accurate on both the asset's and the maintenance's rows.
        static::updated(function (Asset $asset) {
            if (array_key_exists('company_id', $asset->getChanges())) {
                // withoutGlobalScopes bypasses CompanyableChildScope on
                // Maintenance so the cascade reaches every related
                // maintenance regardless of the saving context's auth
                // state (CLI, system jobs, cross-tenant admin saves).
                $asset->maintenances()->withoutGlobalScopes()->get()->each(
                    fn (Maintenance $m) => $m->forceSyncCalendarEvents(),
                );
            }
        });
    }

    public function setExpectedCheckinAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        // Normalise to Y-m-d H:i:s so date-only strings ("2026-07-17") end
        // up as valid datetime values in the DB. On MariaDB the DATETIME
        // column would widen automatically, but SQLite (used in tests) is
        // dynamically typed and stores the raw string, which then fails
        // datetime BETWEEN comparisons in the DueForCheckin scope.
        if ($value !== null && $value !== '') {
            $value = Carbon::parse($value)->format('Y-m-d H:i:s');
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
                if ($field->format == 'BOOLEAN' && ! $field->field_encrypted) {
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

        if ($this->model_id && $this->model && (string) $this->model->require_serial === '1') {
            $this->rules['serial'] = array_merge(
                array_filter($this->rules['serial'] ?? [], fn ($r) => $r !== 'nullable'),
                ['required'],
            );
        }

        return parent::save($params);
    }

    public function getDisplayNameAttribute()
    {
        return $this->present()->name();
    }

    /**
     * Returns the warranty expiration date as Carbon object
     *
     * @return Attribute<Carbon|null, never>
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * `$value` is unused because this is a computed accessor - the
     * warranty expiration is derived from purchase_date +
     * warranty_months, not stored as its own column. Laravel's
     * Attribute closure signature is positional though (`$value` must
     * be the first parameter), so we can't drop it. Suppression tells
     * PHPMD / Codacy to stop flagging.
     */
    protected function warrantyExpires(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => (! empty($attributes['warranty_months']) && ! empty($attributes['purchase_date'])) ? Carbon::parse($attributes['purchase_date'])->addMonths((int) $attributes['warranty_months']) : null,
        );
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function warrantyExpiresFormattedDate(): Attribute
    {

        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Helper::getFormattedDateObject($this->warrantyExpires, 'date', false)
        );
    }

    /**
     * @return Attribute<float|null, never>
     */
    protected function warrantyExpiresDiff(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->warrantyExpires ? round((Carbon::now()->diffInDays($this->warrantyExpires))) : null,
        );

    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function warrantyExpiresDiffForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->warrantyExpires ? Carbon::parse($this->warrantyExpires)->diffForHumans() : null,
        );

    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function lastAuditFormattedDate(): Attribute
    {

        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Helper::getFormattedDateObject($this->last_audit_date, 'datetime', false)
        );
    }

    /**
     * @return Attribute<float|null, never>
     */
    protected function lastAuditDiff(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->warrantyExpires ? round((Carbon::now()->diffInDays($this->warrantyExpires))) : null,
        );

    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function lastAuditDiffForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['last_audit_date'] ? Carbon::parse($attributes['last_audit_date'])->diffForHumans() : null,
        );

    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function nextAuditFormattedDate(): Attribute
    {

        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Helper::getFormattedDateObject($this->next_audit_date, 'date', false)
        );
    }

    /**
     * @return Attribute<float|null, never>
     */
    protected function nextAuditDiffInDays(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['next_audit_date'] ? Carbon::now()->diffInDays($attributes['next_audit_date']) : null,
        );
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function nextAuditDiffForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['next_audit_date'] ? Carbon::parse($attributes['next_audit_date'])->diffForHumans() : null,
        );

    }

    /**
     * @return Attribute<Carbon|null, never>
     */
    protected function eolDate(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, array $attributes) {
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

    /**
     * @return Attribute<string|null, never>
     */
    protected function eolFormattedDate(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->eolDate ? Helper::getFormattedDateObject($this->eolDate, 'date', false) : null,
        );
    }

    /**
     * @return Attribute<float|null, never>
     */
    protected function eolDiffInDays(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->eolDate ? round((Carbon::now()->diffInDays(Carbon::parse($this->eolDate), false, 1))) : null,
        );

    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function eolDiffForHumans(): Attribute
    {

        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->eolDate ? Carbon::parse($this->eolDate)->diffForHumans() : null,
        );

    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function expectedCheckinFormattedDate(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => array_key_exists('expected_checkin', $attributes) ? Helper::getFormattedDateObject($attributes['expected_checkin'], 'datetime', false) : null,
        );
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function expectedCheckinDiffForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => array_key_exists('expected_checkin', $attributes) ? Carbon::parse($this->expected_checkin)->diffForHumans() : null,
        );
    }

    public function isDeletable()
    {

        return Gate::allows('delete', $this)
            && ($this->deleted_at == '');
    }

    /**
     * Establishes the asset -> company relationship
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
     * Determines if an asset is available for checkout.
     * This checks to see if it's checked out to an invalid (deleted) user
     * OR if the assigned_to and deleted_at fields on the asset are empty AND
     * that the status is deployable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return bool
     */
    public function availableForCheckout()
    {

        // This asset is not currently assigned to anyone and is not deleted...
        if ((! $this->assigned_to) && (! $this->deleted_at)) {

            // The asset status is not archived and is deployable
            if (($this->status) && ($this->status->archived == '0')
                && ($this->status->deployable == '1')
            ) {
                return true;

            }

            return false;
        }

        return false;
    }

    public function availableForCheckIn()
    {
        if ($this->assigned_to == '') {
            return false;
        }

        // Deleted assets that are still checked out should always allow checkin
        if ($this->deleted_at != '') {
            return true;
        }

        return $this->status
            && ($this->status->archived == '0')
            && ($this->status->deployable == '1');
    }

    /**
     * Checks the asset out to the target
     *
     * @todo The admin parameter is never used. Can probably be removed.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  User  $admin
     * @param  Carbon  $checkout_at
     * @param  Carbon  $expected_checkin
     * @param  string  $note
     * @param  string|null  $name
     * @return bool
     *
     * @since  [v3.0]
     *
     * @return bool
     */
    public function checkOut($target, $admin = null, $checkout_at = null, $expected_checkin = null, $note = null, $name = null, $location = null, bool $signInPlace = false)
    {
        if (! $target) {
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
            } elseif ($admin && get_class($admin) === User::class) {
                $checkedOutBy = $admin;
            } else {
                $checkedOutBy = auth()->user();
            }
            event(new CheckoutableCheckedOut($this, $target, $checkedOutBy, $note, $originalValues, 1, $signInPlace));

            $this->increment('checkout_counter', 1);

            return true;
        }

        return false;
    }

    /**
     * Sets the detailedNameAttribute
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return string
     */
    public function getDetailedNameAttribute()
    {
        if ($this->assignedto) {
            $user_name = $this->assignedto->present()->name();
        } else {
            $user_name = 'Unassigned';
        }

        return $this->asset_tag.' - '.$this->name.' ('.$user_name.') '.($this->model) ? $this->model->name : '';
    }

    /**
     * Pulls in the validation rules
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
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
                                $this->{$field->db_column} = Crypt::encrypt(request()->input($field->db_column));
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

    public function manufacturer()
    {
        return $this->hasOneThrough(Manufacturer::class, AssetModel::class, 'id', 'id', 'model_id', 'manufacturer_id');
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, AssetModel::class, 'id', 'id', 'model_id', 'category_id');
    }

    /**
     * Establishes the asset -> depreciation relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function depreciation()
    {
        return $this->hasOneThrough(Depreciation::class, AssetModel::class, 'id', 'id', 'model_id', 'depreciation_id');
    }

    /**
     * Get components assigned to this asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function components()
    {
        return $this->belongsToMany('\App\Models\Component', 'components_assets', 'asset_id', 'component_id')
            ->withPivot('id', 'assigned_qty', 'created_at', 'note', 'created_by');
    }

    /**
     * Get depreciation attribute from associated asset model
     *
     * @todo Is this still needed?
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return Relation
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
     *
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
     *
     * @since  [v4.0]
     */
    public function assignedTo(): MorphTo
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Gets assets assigned to this asset
     *
     * Sigh.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function assignedAssets()
    {
        return $this->morphMany(self::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Establishes the accessory -> asset assignment relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assignedAccessories()
    {
        return $this->morphMany(AccessoryCheckout::class, 'assigned', 'assigned_type', 'assigned_to')->with('accessory');
    }

    public function accessories()
    {
        return $this->hasManyThrough(
            Accessory::class,
            AccessoryCheckout::class,
            'assigned_to',
            'id',
            'id',
            'accessory_id'
        )->where('assigned_type', self::class);
    }

    // {
    //     return $this->morphMany(AccessoryCheckout::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    // }

    /**
     * Get the asset's location based on the assigned user
     *
     * @todo Refactor this if possible. It's awful.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     */
    public function assetLoc($iterations = 1, $first_asset = null): ?Location
    {
        if (! empty($this->assignedType())) {
            if ($this->assignedType() == self::ASSET) {
                if (! $first_asset) {
                    $first_asset = $this;
                }
                if ($iterations > 10) {
                    throw new \Exception('Asset assignment Loop for Asset ID: '.e($first_asset->id));
                }
                $assigned_to = self::find($this->assigned_to); // have to do this this way because otherwise it errors
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

                // this makes no sense
                return $this->defaultLoc;

            }

        }

        return $this->defaultLoc;
    }

    /**
     * Gets the lowercased name of the type of target the asset is assigned to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v6.1.0]
     *
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
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function defaultLoc()
    {
        return $this->belongsTo(Location::class, 'rtd_location_id');
    }

    /**
     * Get the image URL of the asset.
     *
     * Check first to see if there is a specific image uploaded to the asset,
     * and if not, check for an image uploaded to the asset model.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return string | false
     */
    public function getImageUrl($path = null)
    {
        if ($this->image && ! empty($this->image)) {
            return Storage::disk('public')->url(FileStorage::Assets->publicPath().e($this->image));
        } elseif ($this->model && ! empty($this->model->image)) {
            return Storage::disk('public')->url(FileStorage::Models->publicPath().e($this->model->image));
        } elseif ($this->model?->category && ! empty($this->model->category->image)) {
            return Storage::disk('public')->url(FileStorage::Categories->publicPath().e($this->model->category->image));
        }

        return false;
    }

    /**
     * Get the asset's logs
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function assetlog()
    {
        return $this->hasMany(Actionlog::class, 'item_id')
            ->where('item_type', '=', self::class)
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Get the list of checkouts for this asset
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
     * Get the list of audits for this asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
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
     *
     * @since  [v2.0]
     *
     * @return Relation
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
     *
     * @since  [v2.0]
     *
     * @return Relation
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
     *
     * @since  1.0
     *
     * @return Relation
     */
    public function maintenances()
    {
        return $this->morphMany(Maintenance::class, 'item')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Establishes the asset -> status relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function status()
    {
        return $this->belongsTo(Statuslabel::class, 'status_id');
    }

    /**
     * Establishes the asset -> model relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function model()
    {
        return $this->belongsTo(AssetModel::class, 'model_id')->withTrashed();
    }

    /**
     * Return the assets with a warranty expiring within x days
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return mixed
     */
    public static function getExpiringWarrantyOrEol($days = 30)
    {
        $now = now();
        $end = now()->addDays($days);

        $expired_assets = self::query()
            ->where('archived', '=', '0')
            ->NotArchived()
            ->whereNull('deleted_at')
            ->whereNotNull('asset_eol_date')
            ->whereBetween('asset_eol_date', [$now, $end])
            ->get();

        $assets_with_warranties = self::query()
            ->where('archived', '=', '0')
            ->NotArchived()
            ->whereNull('deleted_at')
            ->whereNotNull('purchase_date')
            ->whereNotNull('warranty_months')
            ->get();

        $expired_warranties = $assets_with_warranties->filter(function ($asset) use ($now, $end) {
            $expiration_window = Carbon::parse($asset->purchase_date)->addMonths((int) $asset->warranty_months);

            return $expiration_window->betweenIncluded($now, $end);
        });

        return $expired_assets->concat($expired_warranties)
            ->unique('id')
            ->sortBy([
                ['asset_eol_date', 'ASC'],
                ['purchase_date', 'ASC'],
            ])
            ->values();
    }

    /**
     * Establishes the asset -> assigned licenses relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function licenses()
    {
        return $this->belongsToMany(License::class, 'license_seats', 'asset_id', 'license_id')->withPivot('notes', 'created_at', 'created_by');
    }

    /**
     * Establishes the asset -> license seats relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function licenseseats()
    {
        return $this->hasMany(LicenseSeat::class, 'asset_id');
    }

    /**
     * Sync-adapter side row: identity (source + external_id) plus
     * last-known network / OS inventory (primary MAC / IP / OS /
     * OS version / last seen). Nullable relation: only assets that
     * have been synced from an adapter have a row. Detail view +
     * assets table render these fields when the relation is present.
     */
    public function externalSource()
    {
        return $this->hasOne(AssetExternalSource::class);
    }

    /**
     * Establishes the asset -> aupplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Establishes the asset -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the next autoincremented asset tag
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return string | false
     */
    public static function autoincrement_asset(int $additional_increment = 0)
    {
        $settings = Setting::getSettings();

        if ($settings->auto_increment_assets == '1') {
            // Read next_auto_tag_base straight from the DB rather than
            // through the static Setting cache. AssetObserver::created's
            // UPDATE is visible to the current transaction, so sibling
            // calls in the same request still see each other's catch-up
            // writes, and a transaction rollback (eg., a sync-adapter
            // record whose later save fails) cleanly reverts the row so
            // the next call picks up the committed value instead of a
            // stale cache-inflated one (#19954).
            $base = (int) DB::table('settings')->where('id', $settings->id)->value('next_auto_tag_base');

            if ($settings->zerofill_count > 0) {
                return $settings->auto_increment_prefix.self::zerofill($base + $additional_increment, $settings->zerofill_count);
            }

            return $settings->auto_increment_prefix.($base + $additional_increment);
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
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v4.0]
     *
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
     *
     * @since  [v6.4.1]
     *
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
        return (float) $this->components->sum('calculated_purchase_cost');
    }

    /**
     * Return EOL progress percentage (0-100), based on elapsed months since
     * purchase date over the configured EOL window.
     */
    public function eolProgressPercent(): float
    {
        if (! $this->purchase_date || ! $this->asset_eol_date) {
            return 0.0;
        }

        return $this->calculateProgressPercent(
            start: Carbon::parse($this->purchase_date),
            end: Carbon::parse($this->asset_eol_date),
        );
    }

    /**
     * Return warranty progress percentage (0-100), based on elapsed months
     * since purchase date over the warranty window.
     */
    public function warrantyProgressPercent(): float
    {
        if (! $this->purchase_date || ! $this->warranty_expires) {
            return 0.0;
        }

        return $this->calculateProgressPercent(
            start: Carbon::parse($this->purchase_date),
            end: $this->warranty_expires,
        );
    }

    public function getAccessoryCost()
    {
        // purchase_cost no longer lives on the accessories parent —
        // per-unit cost is on the last OrderItem's price, with the
        // parent's default_purchase_cost as fallback. lastOrderDefaults()
        // encapsulates that fallback ladder.
        return (float) $this->accessories()
            ->get()
            ->sum(fn ($accessory) => (float) ($accessory->lastOrderDefaults()['unit_cost'] ?? 0));
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
     * @return Attribute<string|null, string|null>
     */
    protected function nextAuditDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function lastAuditDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function lastCheckout(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function lastCheckin(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function assetEolDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * This sets the requestable to a boolean 0 or 1. This accounts for forms or API calls that
     * explicitly pass the requestable field but it has a null or empty value.
     *
     * This will also correctly parse a 1/0 if "true"/"false" is passed.
     *
     * @param  $value
     * @return Attribute<int, mixed>
     */
    protected function requestable(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (int) filter_var($value, FILTER_VALIDATE_BOOLEAN),
            set: fn ($value) => (int) filter_var($value, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function journal()
    {
        return $this->assetlog()->where('action_type', '=', 'note added')
            ->withTrashed();
    }

    /**
     * Run additional, advanced searches. Overrides advancedTextSearch
     * on the Searchable trait to also search assigned user, location,
     * and asset-to-asset checkouts.
     *
     * $outer carries the leftJoins (joins cannot live inside Eloquent
     * nested-where closures). $nested carries the orWhere clauses
     * that reference the joined columns, confining them inside the
     * trait's scope AND group so a multi-term search cannot escape
     * the surrounding FMCS CompanyableScope.
     *
     * @param  array<int, string>  $terms  The search terms
     */
    public function advancedTextSearch(Builder $outer, Builder $nested, array $terms): void
    {
        /**
         * Assigned user
         */
        $outer->leftJoin(
            'users as assets_users', function ($leftJoin) {
                $leftJoin->on('assets_users.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', User::class);
            }
        );

        foreach ($terms as $term) {
            $nested
                ->orWhere('assets_users.first_name', 'LIKE', '%'.$term.'%')
                ->orWhere('assets_users.last_name', 'LIKE', '%'.$term.'%')
                ->orWhere('assets_users.display_name', 'LIKE', '%'.$term.'%')
                ->orWhere('assets_users.jobtitle', 'LIKE', '%'.$term.'%')
                ->orWhere('assets_users.username', 'LIKE', '%'.$term.'%')
                ->orWhere('assets_users.employee_num', 'LIKE', '%'.$term.'%')
                ->orWhereMultipleColumns(
                    [
                        'assets_users.first_name',
                        'assets_users.last_name',
                    ], $term
                )
                ->orWhereMultipleColumns(
                    [
                        'assets_users.last_name',
                        'assets_users.first_name',
                    ], $term
                );
        }

        /**
         * Assigned location
         */
        $outer->leftJoin(
            'locations as assets_locations', function ($leftJoin) {
                $leftJoin->on('assets_locations.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', Location::class);
            }
        );

        foreach ($terms as $term) {
            $nested->orWhere('assets_locations.name', 'LIKE', '%'.$term.'%');
        }

        /**
         * Assigned assets
         */
        $outer->leftJoin(
            'assets as assigned_assets', function ($leftJoin) {
                $leftJoin->on('assigned_assets.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', self::class);
            }
        );

        foreach ($terms as $term) {
            $nested->orWhere('assigned_assets.name', 'LIKE', '%'.$term.'%');
        }
    }

    public function calendarEventCompanyId(): ?int
    {
        return $this->company_id;
    }

    public function calendarEventDefinitions(): array
    {
        // Most entries are marked all_day: true because they represent
        // date-only obligations (an audit due on 2026-09-15, an EOL
        // date, a warranty expiration). The mixed cast metadata on
        // Asset (next_audit_date is 'datetime:m-d-Y', expected_checkin
        // and last_checkout are 'datetime', asset_eol_date has no cast)
        // means cast-based auto-detection wouldn't catch them
        // uniformly, so the flag is explicit per entry.
        //
        // last_checkout is the one exception. Checkout happens at a
        // specific moment in time, so the calendar shows it at that
        // hour rather than as an all-day marker. The transformer's
        // isAllDayField() reads all_day directly from this array, so
        // flipping the flag here is all that's needed to switch the
        // rendered event's shape.
        return [
            [
                'field' => 'next_audit_date',
                'event_type' => 'asset.audit_due',
                'all_day' => true,
            ],
            [
                'field' => 'expected_checkin',
                'event_type' => 'asset.expected_checkin',
                'all_day' => true,
            ],
            [
                'field' => 'last_checkout',
                'event_type' => 'asset.checkout',
                'all_day' => false,
            ],
            [
                'field' => 'asset_eol_date',
                'event_type' => 'asset.eol',
                'all_day' => true,
            ],
            [
                'field' => 'warranty_expires',
                'event_type' => 'asset.warranty_expiration',
                'trigger_fields' => ['purchase_date', 'warranty_months'],
                'all_day' => true,
            ],
        ];
    }

    public function newEloquentBuilder($query): AssetQueryBuilder
    {
        return new AssetQueryBuilder($query);
    }

    /**
     * Determines if the asset has an orphaned assignment where the assigned target no longer exists.
     * This occurs when:
     * 1. assigned_to is set but assigned_type is missing/null
     * 2. assigned_to and assigned_type are both set, but the relationship cannot be resolved (target was hard-deleted)
     */
    public function hasOrphanedAssignment(): bool
    {
        return ($this->assigned_to && ! $this->assigned_type)
            || ($this->assigned_to && $this->assigned_type && ! $this->assignedTo);
    }
}
