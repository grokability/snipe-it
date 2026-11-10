<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomFieldDefault extends Pivot
{
    /** @use HasFactory<\Database\Factories\CustomFieldDefaultFactory> */
    use HasFactory;

    protected $table = 'models_custom_fields';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'asset_model_id',
        'custom_field_id',
        'default_value',
    ];

    /** @return BelongsTo<AssetModel, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class, 'asset_model_id');
    }

    /** @return BelongsTo<CustomField, $this> */
    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    public static function ownerFor(Model $item, Relation $relationship): ?Model
    {
        if ($relationship instanceof HasOneThrough) {
            $value = $item->getAttribute($relationship->getLocalKeyName());
            if ($value === null) {
                return null;
            }

            $query = $relationship->getParent()->newQuery();
            if ($relationship->throughParentSoftDeletes()
                && in_array('SoftDeletableHasManyThrough', $relationship->getQuery()->removedScopes(), true)) {
                $query->withoutGlobalScope(SoftDeletingScope::class);
            }

            return $query->where($relationship->getFirstKeyName(), $value)->first();
        }

        if ($relationship instanceof BelongsTo && ! ($relationship instanceof MorphTo)) {
            return $item;
        }

        throw new \LogicException('Unsupported custom fieldset relationship.');
    }

    /** @return array<string, mixed> */
    public static function valuesFor(?Model $owner): array
    {
        if (! ($owner instanceof AssetModel) || ! $owner->exists) {
            return [];
        }

        return $owner->defaultValues->pluck('pivot.default_value', 'db_column')->all();
    }

    public static function valueForField(CustomField $field, int|string|null $ownerId): mixed
    {
        return $field->defaultValues->where('id', $ownerId)->pluck('pivot.default_value')->first();
    }

    /**
     * Replace defaults only after every supplied field and value has been validated.
     *
     * @param array<int, mixed> $values Values keyed by custom field ID.
     *
     * @throws ValidationException
     */
    public static function saveFor(Model $owner, ?CustomFieldset $fieldset, array $values): void
    {
        if (! ($owner instanceof AssetModel) || ! $owner->exists) {
            throw new \LogicException('Custom field defaults storage does not support this owner yet.');
        }

        $fields = $fieldset?->fields->keyBy('id') ?? collect();
        $data = [];
        $attributes = [];

        foreach ($values as $id => $value) {
            $field = $fields->get($id);
            if (! ($field instanceof CustomField)) {
                // seems weird, but the idea is that $field might be `null` somehow, if someone else removed a field
                throw ValidationException::withMessages([
                    'default_values.'.$id => trans('validation.exists', ['attribute' => 'default_values.'.$id]),
                ]);
            }

            $data[$field->db_column] = $value;
            $attributes[$field->db_column] = $field->name;
        }

        $rules = [];
        // If the field is marked as required, eliminate the rule so it doesn't interfere with the default values
        // (we are at model level, the rule still applies when creating a new asset using this model)
        foreach ($fieldset?->validation_rules() ?? [] as $column => $validation) {
            $rules[$column] = array_map(fn (mixed $rule): mixed => $rule === 'required' ? 'nullable' : $rule, $validation);
        }
        Validator::make($data, $rules)->setAttributeNames($attributes)->validate();

        $records = [];
        foreach ($values as $id => $value) {
            $normalized = is_array($value) ? implode(', ', $value) : $value;
            if ($normalized !== null && $normalized !== '') {
                $records[$id] = ['default_value' => is_bool($normalized) ? (int) $normalized : $normalized];
            }
        }

        DB::transaction(fn () => $owner->defaultValues()->sync($records));
        $owner->unsetRelation('defaultValues');
    }
}
