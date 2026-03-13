<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\CustomFieldset;
use Illuminate\Database\Eloquent\Collection;

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
        $fields = $fieldset->fields;
        $models = $fieldset->models;
        $modelsArray = [];

        foreach ($models as $model) {
            $modelsArray[] = [
              'id' => $model->id,
              'name' => e($model->name),
            ];
        }

        $array = [
            'id' => (int) $fieldset->id,
            'name' => e($fieldset->name),
            'fields' => (new CustomFieldsTransformer)->transformCustomFields($fields, $fieldset->fields_count),
            'models' => (new DatatablesTransformer)->transformDatatables($modelsArray, $fieldset->models_count),
            'created_at' => Helper::getFormattedDateObject($fieldset->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($fieldset->updated_at, 'datetime'),
        ];

        return $array;
    }
}
