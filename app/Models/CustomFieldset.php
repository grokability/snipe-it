<?php

namespace App\Models;

use App\Models\Contracts\HasCustomFields;
use App\Presenters\CustomFieldsetPresenter;
use App\Presenters\Presentable;
use App\Rules\DecryptableAttribute;
use Gate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperCustomFieldset
 */
class CustomFieldset extends SnipeModel
{
    use HasFactory;
    use Presentable;
    use ValidatingTrait;

    protected $presenter = CustomFieldsetPresenter::class;

    protected $guarded = ['id'];

    public const FIELDSET_ASSIGNMENT_MODELS = [
        Asset::class => [AssetModel::class => 'fieldset_id'],
        // License::class, once it stores a fieldset assignment.
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public $rules = [
        'name' => 'required|unique:custom_fieldsets',
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
     * Establishes the fieldset -> field relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since  [v3.0]
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(CustomField::class)->withPivot(['required', 'order'])->orderBy('pivot_order');
    }

    /**
     * Establishes the fieldset -> models relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    // TODO - there needs to be a better way to do this. Not all custom fieldsets will have 'models'
    public function models()
    {
        return $this->hasMany(AssetModel::class, 'fieldset_id');
    }

    public function isDeletable(): bool
    {
        if (! Gate::allows('delete', $this)) {
            return false;
        }

        foreach (static::FIELDSET_ASSIGNMENT_MODELS as $assignments) {
            foreach ($assignments as $fieldsetOwnerClass => $fieldsetFieldName) {
                $isInUse = $fieldsetOwnerClass::withoutGlobalScopes()
                    ->where($fieldsetFieldName, $this->getKey())
                    ->exists();

                if ($isInUse) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Advanced-search widget definitions for the fieldsets index.
     * The models filter is the real win here: pick one or more asset
     * models and get every fieldset assigned to any of them.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filterableFields(): array
    {
        return [
            [
                'field' => 'models',
                'type' => 'fk',
                'column' => 'models',
                'endpoint' => 'models',
                'label' => trans('general.asset_models'),
            ],
            [
                'field' => 'created_by',
                'type' => 'fk',
                'column' => 'created_by',
                'endpoint' => 'users',
                'label' => trans('general.created_by'),
            ],
            [
                'field' => 'created_at',
                'type' => 'date-range',
                'label' => trans('general.created_at'),
            ],
        ];
    }

    /**
     * Establishes the fieldset -> admin user relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class); // WARNING - not all CustomFieldsets have a User!!
    }

    public function displayAnyFieldsInForm($form_type = null)
    {
        if ($this->fields) {

            switch ($form_type) {
                case 'audit':
                    return $this->fields->where('display_audit', '1')->count() > 0;
                case 'checkin':
                    return $this->fields->where('display_checkin', '1')->count() > 0;
                case 'checkout':
                    return $this->fields->where('display_checkout', '1')->count() > 0;
                default:
                    return true;
            }
        }

        return false;
    }

    /**
     * Determine the validation rules we should apply based on the
     * custom field format
     *
     * Supplying a consuming model also validates its encrypted attribute storage.
     * Omit the model when validating plaintext defaults.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     */
    public function validation_rules((Model&HasCustomFields)|null $model = null): array
    {
        $rules = [];
        foreach ($this->fields as $field) {
            $rule = [];

            if (($field->field_encrypted != '1')
                || (($field->field_encrypted == '1') && (Gate::allows('admin')))
            ) {
                $rule[] = ($field->pivot->required == '1') ? 'required' : 'nullable';
            }

            if ($field->is_unique == '1') {
                $rule[] = 'unique_undeleted';
            }

            if ($field->attributes['format'] != '') {
                array_push($rule, $field->attributes['format']);
            }

            // add not_array to rules for all fields but checkboxes
            if ($field->element != 'checkbox') {
                $rule[] = 'not_array';
            }

            if ($field->element == 'checkbox') {
                $rule[] = 'checkboxes';
            }

            if ($field->element == 'radio') {
                $rule[] = 'radio_buttons';
            }

            $column = $field->db_column_name();
            if ($model !== null && $field->field_encrypted) {
                $rule[] = new DecryptableAttribute($model);
            }

            $rules[$column] = $rule;
        }

        return $rules;
    }
}
