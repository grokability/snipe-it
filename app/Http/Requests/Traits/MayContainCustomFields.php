<?php

namespace App\Http\Requests\Traits;

use App\Models\AssetModel;
use App\Models\CustomField;

trait MayContainCustomFields
{
    // this gets called automatically on a form request
    public function withValidator($validator)
    {

        // In case the model is being changed via form
        if (request()->has('model_id')!='') {

            $asset_model = AssetModel::find(request()->input('model_id'));

        // or if we have it available to route-model-binding
        } elseif ((request()->route('asset') && (request()->route('asset')->model_id))) {

            $asset_model = AssetModel::find(request()->route('asset')->model_id);

        } else {

            if ($this->method() == 'POST') {
                $asset_model = AssetModel::find($this->model_id);
            }

            if ($this->method() == 'PATCH' || $this->method() == 'PUT') {
                $asset_model = $this->asset->model;
            }
        }


        // collect the custom fields in the request
        $validator->after(function ($validator) use ($asset_model) {
            $request_fields = $this->collect()->keys()->filter(function ($attributes) {
                return str_starts_with($attributes, '_snipeit_');
            });

            // if there are custom fields, find the ones that don't exist on the model's fieldset and add an error to the validator's error bag
            if (count($request_fields) > 0 && $validator->errors()->isEmpty()) {
                $request_fields->diff($asset_model?->fieldset?->fields?->pluck('db_column'))
                        ->each(function ($request_field_name) use ($request_fields, $validator) {
                            if (CustomField::where('db_column', $request_field_name)->exists()) {
                                $validator->errors()->add($request_field_name, trans('validation.custom.custom_field_not_found_on_model'));
                            } else {
                                $validator->errors()->add($request_field_name, trans('validation.custom.custom_field_not_found'));
                            }
                        });
            }
        });
    }
}

