<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\CustomFieldset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class CustomFieldsetsTransformer
{
    public function transformCustomFieldsets(Collection $fieldsets, $total)
    {
        $array = [];
        foreach ($fieldsets as $fieldset) {
            $array[] = self::transformCustomFieldset($fieldset);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformCustomFieldset(CustomFieldset $fieldset)
    {
        $modelsArray = [];
        $modelNames = [];
        foreach ($fieldset->models as $model) {
            $label = $model->name.($model->model_number ? ' ('.$model->model_number.')' : '');
            $modelsArray[] = [
                'id' => $model->id,
                'name' => e($label),
            ];
            $modelNames[] = e($label);
        }

        $fieldsCount = (int) ($fieldset->fields_count ?? $fieldset->fields->count());
        $modelsCount = (int) ($fieldset->models_count ?? $fieldset->models->count());

        $array = [
            'id' => (int) $fieldset->id,
            'name' => e($fieldset->name),
            'fields_count' => $fieldsCount,
            'models_count' => $modelsCount,
            'models_list' => implode(', ', $modelNames),
            'fields' => (new CustomFieldsTransformer)->transformCustomFields($fieldset->fields, $fieldsCount),
            'models' => (new DatatablesTransformer)->transformDatatables($modelsArray, $modelsCount),
            'created_by' => ($fieldset->adminuser) ? [
                'id' => (int) $fieldset->adminuser->id,
                'name' => e($fieldset->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($fieldset->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($fieldset->updated_at, 'datetime'),
        ];

        $deletable = $fieldset->isDeletable();
        $array['available_actions'] = [
            'update' => Gate::allows('update', $fieldset),
            'delete' => $deletable,
            'clone' => Gate::allows('clone', $fieldset),
            // Consumed by checkboxEnabledFormatter on the fieldsets index.
            'bulk_selectable' => [
                'delete' => $deletable,
            ],
        ];

        return $array;
    }
}
