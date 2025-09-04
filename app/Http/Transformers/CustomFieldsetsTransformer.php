<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\AssetModel;
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
        $models = [];
        $modelsArray = [];
        if ($fieldset->type == Asset::class) {
            \Log::debug("Item pivot id is: ".$fieldset->item_pivot_id);
            $models = AssetModel::where('fieldset_id', $fieldset->id)->get();
            \Log::debug("And the models object count is: ".$models->count());
        }

        foreach ($models as $model) {
            $modelsArray[] = [
              'id' => $model->id,
              'name' => e($model->name),
            ];
        }
        \Log::debug("Models array is: ".print_r($modelsArray,true));

        $array = [
            'id' => (int) $fieldset->id,
            'name' => e($fieldset->name),
            'fields' => (new CustomFieldsTransformer)->transformCustomFields($fields, $fieldset->fields_count),
            'customizables' => (new DatatablesTransformer)->transformDatatables($fieldset->customizables(),count($fieldset->customizables())),
            'created_at' => Helper::getFormattedDateObject($fieldset->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($fieldset->updated_at, 'datetime'),
            'type' => $fieldset->type,
        ];
        if ($fieldset->type == Asset::class) {
            // TODO - removeme - legacy column just for Assets?
            $array['models'] = (new DatatablesTransformer)->transformDatatables($modelsArray, count($modelsArray));
        }

        return $array;
    }
}
