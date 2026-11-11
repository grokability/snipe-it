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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use ReflectionClass;
use Watson\Validating\ValidatingTrait;

trait HasAndValidatesCustomFields
{
    use ValidatingTrait {
        getRules as getWatsonRules;
        getValidationAttributeNames as protected getWatsonValidationAttributeNames;
    }

    public static function bootHasAndValidatesCustomFields(): void
    {
        static::ensureWatsonValidationIsNotUsedSeparately();
        static::ensureCustomFieldRegistration(self::class);
    }

    protected static function ensureWatsonValidationIsNotUsedSeparately(): void
    {
        for ($class = new ReflectionClass(static::class); $class !== false; $class = $class->getParentClass()) {
            $traits = array_values($class->getTraits());
            while ($traits !== []) {
                $trait = array_pop($traits);
                if ($trait->getName() === HasAndValidatesCustomFields::class) {
                    continue;
                }

                if ($trait->getName() === ValidatingTrait::class) {
                    throw new \LogicException(static::class.' must use '.HasAndValidatesCustomFields::class.' without separately using '.ValidatingTrait::class.'; custom field validation already includes Watson validation.');
                }

                array_push($traits, ...array_values($trait->getTraits()));
            }
        }
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
        $rules = $this->getWatsonRules();

        return array_merge($rules, $this->custom_fieldset?->validation_rules($this) ?? []);
    }

    /**
     * @param  array<string, mixed>|Request  $request
     * @return array<string, mixed>
     */
    public function custom_fill(array|Request $request, bool $with_defaults = false, ?string $filter_field_expression = null, bool $only_present = true): array
    {
        $values = $request instanceof Request ? $request->input() : $request;

        // $filter_field_expression can be 'display_checkin', 'display_checkout', 'display_audit', etc.
        // leave it null to do 'all'
        // Check to see if any of the custom fields were included on the form and if they have any values
        $fieldset = $this->custom_fieldset;

        if ($fieldset === null) {
            return [];
        }
        $fill_results = [];

        $defaultValues = $with_defaults ? $this->customFieldDefaults() : [];

        foreach ($fieldset->fields as $field) {
            if ($filter_field_expression !== null && !$field->{$filter_field_expression}) {
                continue;
            }

            if ($only_present && !$with_defaults && !Arr::has($values, $field->db_column)) {
                continue;
            }

            $value = Arr::get($values, $field->db_column);
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

    protected int|string|null $cachedCustomFieldDependencyValue = null;

    /** @return array<string, string> */
    public function getCasts(): array
    {
        if ($this->resolvingCustomFieldCasts) {
            return parent::getCasts();
        }
        try {
            // we *have* to start this variable _here_ because the customFieldsetDependencyAttribute might do a recursive getCasts()
            $this->resolvingCustomFieldCasts = true;
            $value = $this->attributes[$this->customFieldsetDependencyAttribute()] ?? null;

            if ($this->customFieldCastCache !== null && $value === $this->cachedCustomFieldDependencyValue) {
                return parent::getCasts() + $this->customFieldCastCache;
            }

            if ($this->customFieldCastCache === null && $this->relationLoaded('custom_fieldset')) {
                // "First-Run" - if the relation is preloaded, and the customFieldCastCache is *null*,
                // then read the fieldset directly off of the relation
                $fieldset = $this->getRelation('custom_fieldset');
            } else {
                // we can *only* do a lookup when the fieldset-discriminator isn't null
                $fieldset = is_null($value) ? null : $this->custom_fieldset()->with('fields')->first();
                $this->setRelation('custom_fieldset', $fieldset);
            }

            $this->customFieldCastCache = $this->castsForCustomFieldset($fieldset);
            $this->cachedCustomFieldDependencyValue = $value;

            return parent::getCasts() + $this->customFieldCastCache;
        } finally {
            $this->resolvingCustomFieldCasts = false;
        }
    }

    protected function customFieldsetDependencyAttribute(): string
    {
        if (!array_key_exists(static::class, static::$customFieldsetDependencies)) {
            $relationship = Relation::noConstraints(fn () => $this->custom_fieldset());
            static::$customFieldsetDependencies[static::class] = match (true) {
                $relationship instanceof HasOneThrough => $relationship->getLocalKeyName(),
                $relationship instanceof BelongsTo && ! ($relationship instanceof MorphTo) => $relationship->getForeignKeyName(),
                default => throw new \LogicException('Unsupported custom fieldset relationship.'),
            };
        }

        return static::$customFieldsetDependencies[static::class];
    }

    /** @return array<string, string> */
    protected function castsForCustomFieldset(?CustomFieldset $fieldset): array
    {
        if (!$fieldset) {
            return [];
        }
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

    /** @return array<string, string> */
    public function getValidationAttributeNames(): array
    {
        $attributeNames = $this->getWatsonValidationAttributeNames();
        foreach ($this->custom_fieldset->fields ?? [] as $field) {
            $attributeNames[$field->db_column] ??= $field->name;
        }

        return $attributeNames;
    }
}
