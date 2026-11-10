<?php

namespace App\Models\Traits;

use App\Casts\SnipeBoolean;
use App\Casts\SnipeCrypt;
use App\Casts\SnipeFlattenArray;
use App\Models\CustomFieldDefault;
use App\Models\CustomFieldset;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

trait ValidatesCustomFields
{
    use ValidatingTrait {
        getRules as getWatsonRules;
        getModelAttributes as protected getWatsonModelAttributes;
    }

    public static function bootValidatesCustomFields(): void
    {
        // if (method_exists(new (static::class)(), 'rules')) {
        //     throw new \LogicException("class " . static::class . " cannot have a rules() method and use this Trait");
        // }
        //
        // if (!static::class instanceof CustomFieldRelation) {
        //     throw new \LogicException("class " . static::class . " must implement CustomFieldRelation");
        // }
        static::ensureCustomFieldRegistration(self::class);
    }

    /** @param class-string $modelClass */
    protected static function ensureCustomFieldRegistration(string $modelClass): void
    {
        if (! array_key_exists($modelClass, CustomFieldset::FIELDSET_ASSIGNMENT_MODELS)) {
            throw new \LogicException($modelClass.' must be in the CustomFieldset::FIELDSET_ASSIGNMENT_MODELS array');
        }
    }

    abstract public function custom_fieldset(): Relation;

    public function getRules(): array
    {
        return $this->getWatsonRules() + ($this->custom_fieldset?->validation_rules() ?? []);
    }

    /** @return array<string, mixed> */
    public function custom_fill(Request $request, bool $with_defaults = false, ?string $filter_field_expression = null, bool $only_present = true): array
    {
        // $filter_field_expression can be 'display_checkin', 'display_checkout', 'display_audit', etc.
        // leave it null to do 'all'
        // Check to see if any of the custom fields were included on the form and if they have any values
        $fill_results = [];
        $fieldset = $this->custom_fieldset;

        if ($fieldset === null) {
            return $fill_results;
        }

        $defaultValues = $with_defaults ? $this->customFieldDefaults() : [];

        foreach ($fieldset->fields as $field) {
            if ($filter_field_expression !== null && ! $field->{$filter_field_expression}) {
                continue;
            }

            if ($only_present && ! $with_defaults && ! $request->exists($field->db_column)) {
                continue;
            }

            $value = $request->input($field->db_column);
            if ($with_defaults && $value === null) {
                $value = $defaultValues[$field->db_column] ?? null;
            }

            $fill_results[$field->db_column] = $value;

            if ($field->field_encrypted && ! Gate::allows('assets.view.encrypted_custom_fields')) {
                $this->encryptedCustomFieldUpdateSkipped = true;
                continue;
            }

            $this->{$field->db_column} = $value;
        }

        return $fill_results;
    }

    /** @return array<string, mixed> */
    public function customFieldDefaults(): array
    {
        $owner = CustomFieldDefault::ownerFor($this, $this->custom_fieldset());

        return CustomFieldDefault::valuesFor($owner);
    }

    protected bool $encryptedCustomFieldUpdateSkipped = false;

    public function hasSkippedEncryptedCustomFields(): bool
    {
        return $this->encryptedCustomFieldUpdateSkipped;
    }

    protected bool $resolvingCustomFieldCasts = false;

    /** @var array<class-string, string> */
    protected static array $customFieldsetDependencies = [];

    /** @var array<string, string>|null */
    protected ?array $customFieldCastCache = null;

    protected ?string $cachedCustomFieldDependencyValue = null;

    /** @return array<string, string> */
    public function getCasts(): array
    {
        $casts = parent::getCasts();
        if ($this->resolvingCustomFieldCasts) {
            return $casts;
        }

        $this->resolvingCustomFieldCasts = true;

        try {
            $attribute = $this->customFieldsetDependencyAttribute();
            $rawValue = $this->attributes[$attribute] ?? null;
            $value = $rawValue === null ? null : (string) $rawValue;

            if ($this->customFieldCastCache === null || $value !== $this->cachedCustomFieldDependencyValue) {
                if ($this->customFieldCastCache !== null) {
                    $this->unsetRelation('custom_fieldset');
                }

                if (! $this->relationLoaded('custom_fieldset')) {
                    $fieldset = $value === null ? null : $this->custom_fieldset()->with('fields')->first();
                    $this->setRelation('custom_fieldset', $fieldset);
                }

                $fieldset = $this->getRelation('custom_fieldset');
                $this->customFieldCastCache = $fieldset === null ? [] : $this->castsForCustomFieldset($fieldset);
                $this->cachedCustomFieldDependencyValue = $value;
            }

            return array_merge($casts, $this->customFieldCastCache);
        } finally {
            $this->resolvingCustomFieldCasts = false;
        }
    }

    protected function customFieldsetDependencyAttribute(): string
    {
        $modelClass = static::class;
        if (! array_key_exists($modelClass, static::$customFieldsetDependencies)) {
            $relationship = Relation::noConstraints(fn () => $this->custom_fieldset());
            static::$customFieldsetDependencies[$modelClass] = match (true) {
                $relationship instanceof HasOneThrough => $relationship->getLocalKeyName(),
                $relationship instanceof BelongsTo && ! ($relationship instanceof MorphTo) => $relationship->getForeignKeyName(),
                default => throw new \LogicException('Unsupported custom fieldset relationship.'),
            };
        }

        return static::$customFieldsetDependencies[$modelClass];
    }

    /** @return array<string, string> */
    protected function castsForCustomFieldset(CustomFieldset $fieldset): array
    {
        $casts = [];
        foreach ($fieldset->fields as $field) {
            $casts[$field->db_column_name()] = match (true) {
                (bool) $field->field_encrypted => SnipeCrypt::class,
                $field->format === 'BOOLEAN' => SnipeBoolean::class,
                default => SnipeFlattenArray::class,
            };
        }

        return $casts;
    }

    /** @return array<string, mixed> */
    public function getModelAttributes(): array
    {
        $attributes = $this->getWatsonModelAttributes();
        foreach ($this->getCasts() as $key => $cast) {
            if ($cast === SnipeCrypt::class && array_key_exists($key, $this->attributes)) {
                $attributes[$key] = $this->attributes[$key];
            }
        }

        return $attributes;
    }
}
