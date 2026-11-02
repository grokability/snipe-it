<?php

namespace App\Http\Requests;

use App\Enums\ActivityItemType;
use App\Rules\ValidJson;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    /**
     * Normalize scalar item_type / target_type inputs into
     * single-element arrays so a request can pass either shape
     * (?item_type=asset OR ?item_type[]=asset&item_type[]=component)
     * and downstream code always sees an array. Multi-value is used
     * by the activity report toolbar's multi-select dropdowns.
     */
    protected function prepareForValidation(): void
    {
        foreach (['item_type', 'target_type'] as $key) {
            $value = $this->input($key);
            if (is_string($value) && $value !== '') {
                $this->merge([$key => [$value]]);
            }
        }
    }

    public function rules(): array
    {
        // Case-insensitive match against ActivityItemType short names
        // (asset / assetmodel / ...) and fully-qualified model class
        // strings (App\Models\Asset / ...) via the ActivityItemType::tryFromAny helper. This allows the activity report to accept either shape in the filter payload, and also allows future expansion of the enum without needing to update the validation rules here.
        $typeRule = function (string $attribute, mixed $value, Closure $fail): void {
            if (!is_string($value) || ActivityItemType::tryFromAny($value) === null) {
                $fail(trans('validation.in', ['attribute' => $attribute]));
            }
        };

        return [
            // Accept either array-form (?filter[field]=value, which
            // Laravel parses to an array) or the JSON-string form
            // (?filter={"field":"value"}).
            // Both converge in Searchable::scopeTextSearch,
            // which routes them to the same applySearchFilters method.
            'filter' => [
                'nullable', function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_array($value)) {
                        return;
                    }
                    (new ValidJson)->validate($attribute, $value, $fail);
                },
            ],
            'item_type' => ['nullable', 'array'],
            'item_type.*' => [$typeRule],
            'target_type' => ['nullable', 'array'],
            'target_type.*' => [$typeRule],
        ];
    }
}
