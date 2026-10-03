<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use EasySlugger\Utf8Slugger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Watson\Validating\ValidatingTrait;

class CustomField extends Model
{
    use HasFactory;
    use UniqueUndeletedTrait,
        ValidatingTrait;

    /**
     * Custom field predfined formats
     *
     * @var array
     */
    public const PREDEFINED_FORMATS = [
        'ANY' => '',
        'CUSTOM REGEX' => '',
        'ALPHA' => 'alpha',
        'ALPHA-DASH' => 'alpha_dash',
        'NUMERIC' => 'numeric',
        'ALPHA-NUMERIC' => 'alpha_num',
        'EMAIL' => 'email',
        'DATE' => 'date',
        // Distinct pattern so getFormatAttribute() can reverse-map back to
        // 'DATETIME'. If both DATE and DATETIME shared 'date' the first-match
        // loop would always return 'DATE' and the DATETIME picker never fires.
        // The datetimepicker widget always outputs Y-m-d H:i:s, so this
        // date_format is the exact shape we expect.
        'DATETIME' => 'date_format:Y-m-d H:i:s',
        'URL' => 'url',
        'IP' => 'ip',
        'IPV4' => 'ipv4',
        'IPV6' => 'ipv6',
        'MAC' => 'regex:/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/',
        'BOOLEAN' => 'boolean',
        // Phone / fax validation is famously unreliable (international
        // formats, extensions, punctuation variance), so PHONE / FAX
        // deliberately use no-op patterns. 'string' / 'present' are chosen
        // over '' because an empty pattern would collide with 'ANY' in the
        // getFormatAttribute reverse-map. Both pass for any form input.
        'PHONE' => 'string',
        'FAX' => 'present',
    ];

    public const ELEMENT_KEYS = [
        'text', 'listbox', 'multi-listbox', 'textarea', 'markdown-textarea',
        'checkbox', 'radio', 'date_picker', 'datetime_picker',
    ];

    public const TEXT_ONLY_FORMATS = [
        'EMAIL', 'URL', 'PHONE', 'FAX', 'MAC', 'IP', 'IPV4', 'IPV6',
        'ALPHA', 'ALPHA-DASH', 'ALPHA-NUMERIC', 'NUMERIC',
    ];

    public $guarded = [
        'id',
    ];

    /**
     * Validation rules.
     * At least empty array must be provided if using ValidatingTrait.
     *
     * @var array
     */
    protected $rules = [
        'name' => 'required|unique:custom_fields',
        'element' => 'required|in:text,listbox,multi-listbox,textarea,markdown-textarea,checkbox,radio,date_picker,datetime_picker',
        'field_encrypted' => 'nullable|boolean',
        'auto_add_to_fieldsets' => 'boolean',
        'show_in_listview' => 'boolean',
        'show_in_requestable_list' => 'boolean',
        'show_in_email' => 'boolean',
        'format' => 'nullable|string',
    ];

    protected $casts = [
        'show_in_requestable_list' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'element',
        'format',
        'field_values',
        'field_encrypted',
        'help_text',
        'show_in_email',
        'is_unique',
        'display_in_user_view',
        'auto_add_to_fieldsets',
        'show_in_listview',
        'show_in_email',
        'display_checkout',
        'display_checkin',
        'display_audit',
        'show_in_requestable_list',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'format',
        'element',
        'db_column',
        'help_text',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'fieldset' => ['name'],
        'assetModels' => ['name'],
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    /**
     * This is confusing, since it's actually the custom fields table that
     * we're usually modifying, but since we alter the assets table, we have to
     * say that here, otherwise the new fields get added onto the custom fields
     * table instead of the assets table.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since  [v3.0]
     */
    public static $table_name = 'assets';

    /**
     * Convert the custom field's name property to a db-safe string.
     *
     * We could probably have used str_slug() here but not sure what it would
     * do with previously existing values. - @snipe
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return string
     */
    public static function name_to_db_name($name)
    {
        return '_snipeit_'.preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($name));
    }

    /**
     * Returns the element keys valid for a given format label.
     *
     * Single source of truth for the format/element compatibility matrix.
     * Consumed both by getRules() (server-side validation for UI and API)
     * and by the Livewire editor for live dropdown option disabling.
     */
    public static function allowedElementKeysForFormat(?string $formatLabel): array
    {
        if ($formatLabel === 'DATE') {
            return ['date_picker'];
        }
        if ($formatLabel === 'DATETIME') {
            return ['datetime_picker'];
        }

        // 'CUSTOM REGEX' is the friendly label; on saved records the
        // stored value is the actual regex pattern (e.g. 'regex:/^\d+$/').
        if ($formatLabel === 'CUSTOM REGEX' || str_starts_with((string) $formatLabel, 'regex:')) {
            return ['text'];
        }

        if (in_array($formatLabel, self::TEXT_ONLY_FORMATS, true)) {
            return ['text'];
        }

        if ($formatLabel === 'BOOLEAN') {
            return ['text', 'checkbox', 'radio'];
        }

        // ANY / null / empty — all elements allowed.
        return self::ELEMENT_KEYS;
    }

    /**
     * Returns whether encryption is allowed for a given element/format combo.
     *
     * DATE / DATETIME formats are backed by native date columns that can't
     * hold ciphertext. checkbox / radio elements store comma-separated
     * values that don't round-trip through encrypt/decrypt cleanly.
     */
    public static function canEncryptFor(?string $element, ?string $formatLabel): bool
    {
        if (in_array($formatLabel, ['DATE', 'DATETIME'], true)) {
            return false;
        }
        if (in_array($element, ['multi-listbox', 'checkbox', 'radio'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Whether an element needs a field_values list (options to pick from).
     */
    public static function elementRequiresFieldValues(?string $element): bool
    {
        return in_array($element, ['listbox', 'multi-listbox', 'checkbox', 'radio'], true);
    }

    /**
     * Returns the icon type (matching IconHelper keys) that should decorate
     * a text input for the given format, or null when no icon applies.
     * Kept in one place so the preview and the real asset-form render agree.
     */
    public static function iconForFormat(?string $formatLabel): ?string
    {
        return match ($formatLabel) {
            'EMAIL' => 'email',
            'URL' => 'link',
            'PHONE' => 'phone',
            'FAX' => 'fax',
            default => null,
        };
    }

    /**
     * Override Watson's getRules() to layer per-instance dynamic rules on
     * top of the static $rules array. Ensures the format/element matrix,
     * encryption compatibility, and field_values requirement are enforced
     * on ALL entry points (UI, API, factories, seeders) — not just the UI.
     */
    public function getRules()
    {
        $rules = $this->rules;

        // Uses the accessor, which reverse-maps stored patterns like
        // 'date' back to the label 'DATE' so the matrix keys line up.
        $format = $this->format;
        $element = $this->element;

        $allowedElements = self::allowedElementKeysForFormat($format);
        $rules['element'] = [
            'required',
            Rule::in(self::ELEMENT_KEYS),
            function ($attribute, $value, $fail) use ($allowedElements, $format) {
                if (! in_array($value, $allowedElements, true)) {
                    $fail(trans('admin/custom_fields/general.validation.element_not_valid_for_format', [
                        'element' => $value,
                        'format' => $format ?: 'ANY',
                        'allowed' => implode(', ', $allowedElements),
                    ]));
                }
            },
        ];

        if (self::elementRequiresFieldValues($element)) {
            $rules['field_values'] = ['required', 'string', function ($attribute, $value, $fail) use ($element) {
                if (trim((string) $value) === '') {
                    $fail(trans('admin/custom_fields/general.validation.field_values_required', [
                        'element' => $element,
                    ]));
                }
            }];
        }

        if ($this->field_encrypted && ! self::canEncryptFor($element, $format)) {
            $rules['field_encrypted'] = [function ($attribute, $value, $fail) use ($element, $format) {
                if (in_array($format, ['DATE', 'DATETIME'], true)) {
                    $fail(trans('admin/custom_fields/general.validation.cannot_encrypt_format', [
                        'format' => $format,
                    ]));

                    return;
                }
                $fail(trans('admin/custom_fields/general.validation.cannot_encrypt_element', [
                    'element' => $element,
                ]));
            }];
        }

        return $rules;
    }

    /**
     * Set some boot methods for creating and updating.
     *
     * There is never ever a time when we wouldn't want to be updating those asset
     * column names and the values of the db column name in the custom fields table
     * if they have changed, so we handle that here so that we don't have to remember
     * to do it in the controllers.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return bool
     */
    public static function boot()
    {
        parent::boot();
        self::created(
            function ($custom_field) {

                // Column already exists on the assets table - nothing to do here.
                // This *shouldn't* happen in the wild.
                if (Schema::hasColumn(self::$table_name, $custom_field->db_column)) {
                    return false;
                }

                // Add the column to the assets table. Most custom-field types
                // land in a TEXT column so we don't have to worry about width
                // or type coercion for freeform values. DATE and DATETIME
                // are the exceptions: creating them as native DATE / DATETIME
                // columns lets MySQL/MariaDB sort and range-filter
                // chronologically on that column instead of lexicographically
                // on a text string.
                Schema::table(
                    self::$table_name, function ($table) use ($custom_field) {
                        $column = $custom_field->convertUnicodeDbSlug();
                        if ($custom_field->format === 'DATETIME') {
                            $table->dateTime($column)->nullable();
                        } elseif ($custom_field->format === 'DATE') {
                            $table->date($column)->nullable();
                        } else {
                            $table->text($column)->nullable();
                        }
                    }
                );

                // Update the db_column property in the custom fields table.
                // syncOriginal() first because Laravel doesn't call it until
                // finishSave() completes, and finishSave() hasn't run yet at
                // this point in the `created` event. Without the explicit
                // sync, the internal save below arrives at the `updating`
                // hook with $original == [] — making every attribute look
                // dirty and tripping the format-lock check.
                $custom_field->syncOriginal();
                $custom_field->db_column = $custom_field->convertUnicodeDbSlug();
                $custom_field->save();
            }
        );

        // Format/element/encryption compatibility is enforced by getRules()
        // so both UI (Livewire) and API callers hit the same validation.
        // See allowedElementKeysForFormat() / canEncryptFor() above.

        self::updating(
            function ($custom_field) {

                // DATE / DATETIME custom fields are backed by native
                // DATE / DATETIME columns on the assets table. Changing
                // format to or from these types is a HARD block because
                // it requires an ALTER TABLE column-type change: existing
                // text values would fail conversion to a date column, and
                // existing date values can't round-trip back to text
                // without an explicit stringify step. Other format changes
                // (e.g. PHONE -> EMAIL) affect only validation rules and
                // are allowed — the Livewire UI warns the user before they
                // save that existing assets may fail future validation.
                if ($custom_field->isDirty('format')) {
                    $original = $custom_field->getOriginal('format');
                    $originalFriendly = collect(self::PREDEFINED_FORMATS)
                        ->search(fn ($pattern) => $pattern === $original) ?: $original;
                    $lockedFormats = ['DATE', 'DATETIME'];
                    $isOrWasLocked = in_array($originalFriendly, $lockedFormats, true)
                        || in_array($custom_field->format, $lockedFormats, true);
                    if ($isOrWasLocked) {
                        $custom_field->getErrors()->add(
                            'format',
                            'The format cannot be changed to or from '.($originalFriendly ?: 'the current type').' on an existing field. Create a new field with the desired format instead.'
                        );

                        return false;
                    }
                }

                // Column already exists on the assets table - nothing to do here.
                if ($custom_field->isDirty('name')) {
                    if (Schema::hasColumn(self::$table_name, $custom_field->convertUnicodeDbSlug())) {
                        return true;
                    }

                    // Rename the field if the name has changed
                    Schema::table(
                        self::$table_name, function ($table) use ($custom_field) {
                            $table->renameColumn($custom_field->convertUnicodeDbSlug($custom_field->getOriginal('name')), $custom_field->convertUnicodeDbSlug());
                        }
                    );

                    // Save the updated column name to the custom fields table
                    $custom_field->db_column = $custom_field->convertUnicodeDbSlug();
                    $custom_field->save();

                    return true;
                }

                return true;
            }
        );

        // Drop the assets column if we've deleted it from custom fields
        self::deleting(
            function ($custom_field) {
                // Guard against orphans from an earlier bug that could leave
                // db_column empty. There's no column to drop in that case;
                // still allow the custom_fields row itself to be deleted so
                // the record can be cleaned up.
                if (empty($custom_field->db_column)) {
                    return true;
                }
                if (! Schema::hasColumn(self::$table_name, $custom_field->db_column)) {
                    return true;
                }

                return Schema::table(
                    self::$table_name, function ($table) use ($custom_field) {
                        $table->dropColumn($custom_field->db_column);
                    }
                );
            }
        );
    }

    /**
     * Establishes the customfield -> fieldset relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function fieldset()
    {
        return $this->belongsToMany(CustomFieldset::class);
    }

    public function displayFieldInCheckinForm()
    {
        if ($this->display_checkin == '1') {
            return true;
        }

        return false;
    }

    public function displayFieldInCheckoutForm()
    {
        if ($this->display_checkout == '1') {
            return true;
        }

        return false;
    }

    public function displayFieldInAuditForm()
    {
        if ($this->display_audit == '1') {
            return true;
        }

        return false;
    }

    public function displayFieldInCurrentForm($form_type = null)
    {
        switch ($form_type) {
            case 'audit':
                return $this->displayFieldInAuditForm();
            case 'checkin':
                return $this->displayFieldInCheckinForm();
            case 'checkout':
                return $this->displayFieldInCheckoutForm();
        }
    }

    public function assetModels()
    {
        return $this->fieldset()->with('models')->get()->pluck('models')->flatten()->unique('id');
    }

    /**
     * Establishes the customfield -> admin user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Establishes the customfield -> default values relationship
     *
     * @author Hannah Tinkler
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function defaultValues()
    {
        return $this->belongsToMany(AssetModel::class, 'models_custom_fields')->withPivot('default_value');
    }

    /**
     * Returns the default value for a given model using the defaultValues
     * relationship
     *
     * @param  int  $modelId
     * @return string
     */
    public function defaultValue($modelId)
    {
        return $this->defaultValues->filter(
            function ($item) use ($modelId) {
                return $item->pivot->asset_model_id == $modelId;
            }
        )->map(
            function ($item) {
                return $item->pivot->default_value;
            }
        )->first();
    }

    /**
     * Checks the format of the attribute
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  $value  string
     *
     * @since  [v3.0]
     *
     * @return bool
     */
    public function check_format($value)
    {
        return preg_match('/^'.$this->attributes['format'].'$/', $value) === 1;
    }

    /**
     * Gets the DB column name.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return string
     */
    public function db_column_name()
    {
        return $this->db_column;
    }

    /**
     * Mutator for the 'format' attribute.
     *
     * This is used by the dropdown to store the laravel-specific
     * validator strings in the database but still return the
     * user-friendly text in the dropdowns, and in the custom fields display.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return string
     */
    public function getFormatAttribute($value)
    {
        foreach (self::PREDEFINED_FORMATS as $name => $pattern) {
            if ($pattern === $value || $name === $value) {
                return $name;
            }
        }

        return $value;
    }

    /**
     * Returns the display label of the ORIGINAL format (before any pending
     * in-memory changes). getFormatAttribute reads the current value; this
     * reads $original — needed to answer "is this field ALREADY locked into
     * DATE/DATETIME on disk" so the UI can render a readonly control.
     */
    public function getOriginalFormat(): string
    {
        $raw = $this->getOriginal('format') ?? '';
        foreach (self::PREDEFINED_FORMATS as $name => $pattern) {
            if ($pattern === $raw || $name === $raw) {
                return $name;
            }
        }

        return $raw;
    }

    /**
     * Format a value string as an array for select boxes and checkboxes.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return array
     */
    public function setFormatAttribute($value)
    {
        if (isset(self::PREDEFINED_FORMATS[$value])) {
            $this->attributes['format'] = self::PREDEFINED_FORMATS[$value];
        } else {
            $this->attributes['format'] = $value;
        }
    }

    /**
     * Format a value string as an array for select boxes and checkboxes.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return array
     */
    public function formatFieldValuesAsArray()
    {
        $result = [];
        $arr = preg_split('/\\r\\n|\\r|\\n/', $this->field_values ?? '');

        if (($this->element != 'checkbox') && ($this->element != 'radio') && ($this->element != 'multi-listbox')) {
            $result[''] = 'Select '.strtolower($this->format);
        }

        for ($x = 0; $x < count($arr); $x++) {
            $arr_parts = explode('|', $arr[$x]);
            if ($arr_parts[0] != '') {
                if (array_key_exists('1', $arr_parts)) {
                    $result[$arr_parts[0]] = trim($arr_parts[1]);
                } else {
                    $result[$arr_parts[0]] = trim($arr_parts[0]);
                }
            }
        }

        return $result;
    }

    /**
     * Check whether the field is encrypted
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return bool
     */
    public function isFieldDecryptable($string)
    {
        if (($this->field_encrypted == '1') && ($string != '')) {
            return true;
        }

        return false;
    }

    /**
     * Convert non-UTF-8 or weirdly encoded text into something that
     * won't break the database.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.4]
     *
     * @return string
     */
    public function convertUnicodeDbSlug($original = null)
    {
        $name = $original ? $original : $this->name;
        $id = $this->id ? $this->id : 'xx';

        if (! function_exists('transliterator_transliterate')) {
            $long_slug = '_snipeit_'.str_slug(mb_convert_encoding(trim($name), 'UTF-8'), '_');
        } else {
            $long_slug = '_snipeit_'.Utf8Slugger::slugify($name, '_');
        }

        return substr($long_slug, 0, 50).'_'.$id;
    }

    /**
     * Get validation rules for custom fields to use with Validator
     *
     * @author [V. Cordes] [<volker@fdatek.de>]
     *
     * @param  int  $id
     *
     * @since  [v4.1.10]
     *
     * @return array
     */
    public function validationRules($regex_format = null)
    {
        return [
            'format' => [
                Rule::in(array_merge(array_keys(self::PREDEFINED_FORMATS), self::PREDEFINED_FORMATS, [$regex_format])),
            ],
        ];
    }

    /**
     * Check to see if there is a custom regex format type
     *
     * @see https://github.com/grokability/snipe-it/issues/5896
     *
     * @author Wes Hulette <jwhulette@gmail.com>
     *
     * @since 5.0.0
     *
     * @return string
     */
    public function getFormatType()
    {
        if (stripos($this->format, 'regex') === 0 && ($this->format !== self::PREDEFINED_FORMATS['MAC'])) {
            return 'CUSTOM REGEX';
        }

        return $this->format;
    }
}
