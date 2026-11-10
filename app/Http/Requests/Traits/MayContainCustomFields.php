<?php

namespace App\Http\Requests\Traits;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;

trait MayContainCustomFields
{
    // this gets called automatically on a form request
    public function withValidator($validator)
    {

        // In case the model is being changed via form
        if (request()->has('model_id') != '') {

            $asset_model = AssetModel::find(request()->input('model_id'));

            // or if we have it available to route-model-binding
        } elseif (request()->route('asset') instanceof Asset && request()->route('asset')->model_id) {

            $asset_model = AssetModel::find(request()->route('asset')->model_id);

        } elseif ($this->method() == 'POST') {
            $asset_model = AssetModel::find($this->model_id);
        } else {
            // Bulk update / audit paths (no single {asset} in the URL) — the
            // model to validate against can't be pinned to one asset, so let
            // per-row saves surface any bad custom-field values at save time.
            $asset_model = null;
        }

        // collect the custom fields in the request
        $validator->after(function ($validator) use ($asset_model) {
            $request_fields = $this->collect()->keys()->filter(function ($attributes) {
                return str_starts_with($attributes, '_snipeit_');
            });

            if ($request_fields->isEmpty() || ! $validator->errors()->isEmpty()) {
                return;
            }

            // Bulk update path: no single target model to check membership
            // against, since different assets in the batch may carry
            // different fieldsets. We still flag truly nonexistent
            // custom-field columns (typo catch); per-model membership is
            // deferred to save-time, where applyAssetUpdate silently
            // ignores fields the asset's own model doesn't carry.
            if ($asset_model === null) {
                $request_fields->each(function ($request_field_name) use ($validator) {
                    if (! CustomField::where('db_column', $request_field_name)->exists()) {
                        $validator->errors()->add($request_field_name, trans('validation.custom.custom_field_not_found'));
                    }
                });

                return;
            }

            // Single-target-model path: fields present in the request but
            // not on this asset's fieldset are errors.
            $request_fields->diff(CustomFieldset::find($asset_model->fieldset_id)?->fields->pluck('db_column') ?? collect())
                ->each(function ($request_field_name) use ($validator) {
                    if (CustomField::where('db_column', $request_field_name)->exists()) {
                        $validator->errors()->add($request_field_name, trans('validation.custom.custom_field_not_found_on_model'));
                    } else {
                        $validator->errors()->add($request_field_name, trans('validation.custom.custom_field_not_found'));
                    }
                });
        });
    }
}
