<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Traits\HasAndValidatesCustomFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class CustomFieldValuesTransformer
{
    /**
     * @return ($valuesOnly is true ? array<string, mixed> : array<string, array{field: string, value: mixed, field_format: mixed, element: mixed}>)
     */
    public function transform(Model $item, bool $includeEncrypted = true, bool $renderMarkdown = true, ?string $filterFieldExpression = null, bool $valuesOnly = false): array
    {
        if (! in_array(HasAndValidatesCustomFields::class, class_uses_recursive($item), true)) {
            return [];
        }

        $fields = [];
        foreach ($item->custom_fieldset->fields ?? [] as $field) {
            if ($filterFieldExpression !== null && ! $field->{$filterFieldExpression}) {
                continue;
            }

            if (! $includeEncrypted && $field->field_encrypted) {
                continue;
            }

            $value = $item->getAttribute($field->db_column);
            if ($field->isFieldDecryptable($value) && ! Gate::allows('assets.view.encrypted_custom_fields')) {
                $value = e(strtoupper(trans('admin/custom_fields/general.encrypted')));
            } else {
                if (in_array($field->format, ['DATE', 'DATETIME'], true) && $value !== null && $value !== '') {
                    $value = Helper::getFormattedDateObject($value, $field->format === 'DATETIME' ? 'datetime' : 'date', false);
                }

                $value = $renderMarkdown && $field->element === 'markdown-textarea' ? Helper::renderMarkdown($value) : e($value);
            }

            if ($valuesOnly) {
                $fields[$field->db_column] = $value;
            } else {
                $fields[$field->name] = [
                    'field' => e($field->db_column),
                    'value' => $value,
                    'field_format' => $field->format,
                    'element' => $field->element,
                ];
            }
        }

        return $fields;
    }
}
