<?php

namespace App\Http\Transformers;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\AssetModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AssetModelsTransformer
{
    public function transformAssetModels(Collection $assetmodels, $total)
    {
        $array = [];
        foreach ($assetmodels as $assetmodel) {
            $array[] = self::transformAssetModel($assetmodel);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAssetModel(AssetModel $assetmodel)
    {

        $default_field_values = [];

        // Reach into the custom fields and models_custom_fields pivot table to find the default values for this model
        if ($assetmodel->fieldset) {
            foreach ($assetmodel->fieldset->fields as $field) {
                $default_field_values[] = [
                    'name' => e($field->name),
                    'db_column_name' => e($field->db_column_name()),
                    'default_value' => ($field->defaultValue($assetmodel->id)) ? e($field->defaultValue($assetmodel->id)) : null,
                    'format' => e($field->format),
                    'required' => ($field->pivot->required == '1') ? true : false,
                ];
            }
        }

        $array = [
            'id' => (int) $assetmodel->id,
            'name' => e($assetmodel->name),
            'manufacturer' => ($assetmodel->manufacturer) ? [
                'id' => (int) $assetmodel->manufacturer->id,
                'name' => e($assetmodel->manufacturer->name),
                'tag_color' => ($assetmodel->manufacturer->tag_color) ? e($assetmodel->manufacturer->tag_color) : null,
            ] : null,
            'image' => ($assetmodel->image != '') ? Storage::disk('public')->url(FileStorage::Models->publicPath().e($assetmodel->image)) : null,
            'qr_code_url' => route('qr_code/common', ['object_type' => 'models', 'id' => $assetmodel->id]),
            'model_number' => ($assetmodel->model_number ? e($assetmodel->model_number) : null),
            'min_amt' => ($assetmodel->min_amt) ? (int) $assetmodel->min_amt : null,
            'mfg_marketing_name' => ($assetmodel->mfg_marketing_name) ? e($assetmodel->mfg_marketing_name) : null,
            'mfg_release_date' => Helper::getFormattedDateObject($assetmodel->mfg_release_date, 'date'),
            'mfg_discontinued_date' => Helper::getFormattedDateObject($assetmodel->mfg_discontinued_date, 'date'),

            'depreciation' => ($assetmodel->depreciation) ? [
                'id' => (int) $assetmodel->depreciation->id,
                'name' => e($assetmodel->depreciation->name),
            ] : null,
            'assets_count' => (int) $assetmodel->assets_count,
            'assets_assigned_count' => (int) $assetmodel->assets_assigned_count,
            'assets_archived_count' => (int) $assetmodel->assets_archived_count,
            'remaining' => $assetmodel->remaining !== null
                ? (int) $assetmodel->remaining
                : (int) ($assetmodel->assets_count - (int) $assetmodel->assets_assigned_count) - (int) $assetmodel->assets_archived_count,
            'percent_remaining' => round($assetmodel->percentRemaining()),
            'category' => ($assetmodel->category) ? [
                'id' => (int) $assetmodel->category->id,
                'name' => e($assetmodel->category->name),
                'tag_color' => ($assetmodel->category->tag_color) ? e($assetmodel->category->tag_color) : null,
            ] : null,
            'fieldset' => ($assetmodel->fieldset) ? [
                'id' => (int) $assetmodel->fieldset->id,
                'name' => e($assetmodel->fieldset->name),
            ] : null,
            'default_fieldset_values' => $default_field_values,
            'eol' => ($assetmodel->eol > 0) ? $assetmodel->eol.' months' : 'None',
            'requestable' => ($assetmodel->requestable == '1') ? true : false,
            'require_serial' => ($assetmodel->require_serial == '1') ? true : false,
            'notes' => Helper::parseEscapedMarkedownInline($assetmodel->notes),
            'created_by' => ($assetmodel->adminuser) ? [
                'id' => (int) $assetmodel->adminuser->id,
                'name' => e($assetmodel->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($assetmodel->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($assetmodel->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($assetmodel->deleted_at, 'datetime'),

        ];

        // Whether the current caller has an open request against this
        // model. Drives the request/cancel button-swap on the
        // requestable tab. Only populates when the relation was
        // preloaded (which the requestable() endpoint does); the
        // standard index endpoint doesn't preload requests, so the
        // relationLoaded gate keeps a per-row query out of that path.
        // Mirrors the shape AccessoriesTransformer uses.
        $userHasOpenRequest = auth()->check() && $assetmodel->relationLoaded('requests') && $assetmodel->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );

        $array['assigned_to_self'] = $userHasOpenRequest;

        $permissions_array['available_actions'] = [
            'create_asset' => (Gate::allows('create', Asset::class) && ($assetmodel->deleted_at == '')),
            // The requestable-tab JS reads this to decide whether to
            // render the name as an <a> link or as plain text. Keeps
            // the permission check server-side so the JS doesn't need
            // a compile-time @can inside its Blade-hosted formatter.
            'view' => Gate::allows('view', $assetmodel),
            'update' => (Gate::allows('update', $assetmodel) && ($assetmodel->deleted_at == '')),
            'delete' => $assetmodel->isDeletable(),
            'clone' => (Gate::allows('clone', $assetmodel) && ($assetmodel->deleted_at == '')),
            'restore' => (Gate::allows('create', AssetModel::class) && ($assetmodel->deleted_at != '')),
            // Request / cancel: if the requestable flag is off the row
            // never surfaces on /account/requestable anyway (scoped
            // out by Requestable()), but honor it here too for
            // any consumer hitting the standard index endpoint.
            'request' => (bool) $assetmodel->requestable && ! $userHasOpenRequest,
            'cancel' => (bool) $assetmodel->requestable && $userHasOpenRequest,
            'bulk_selectable' => [
                'edit' => (Gate::allows('update', $assetmodel) && ($assetmodel->deleted_at == '')),
                'delete' => (Gate::allows('delete', $assetmodel) && $assetmodel->isDeletable()),
                'merge' => (Gate::allows('delete', AssetModel::class) && ($assetmodel->deleted_at == '')),
            ],
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformRequestedModels(Collection $assetmodels, $total)
    {
        $array = [];
        foreach ($assetmodels as $assetmodel) {
            $array[] = self::transformRequestedModel($assetmodel);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRequestedModel(AssetModel $assetmodel)
    {
        $userHasOpenRequest = auth()->check() && $assetmodel->relationLoaded('requests') && $assetmodel->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );

        return [
            'id' => (int) $assetmodel->id,
            'name' => e($assetmodel->name),
            'image' => ($assetmodel->image != '') ? Storage::disk('public')->url(FileStorage::Models->publicPath().e($assetmodel->image)) : null,
            'model_number' => ($assetmodel->model_number) ? e($assetmodel->model_number) : null,
            'remaining' => $assetmodel->remaining !== null
                ? (int) $assetmodel->remaining
                : (int) ($assetmodel->assets_count - (int) $assetmodel->assets_assigned_count) - (int) $assetmodel->assets_archived_count,
            'assigned_to_self' => $userHasOpenRequest,
            'available_actions' => [
                'view' => Gate::allows('view', $assetmodel),
                'request' => ! $userHasOpenRequest,
                'cancel' => $userHasOpenRequest,
            ],
        ];
    }

    public function transformAssetModelFiles($assetmodel, $total)
    {

        $array = [];
        foreach ($assetmodel->uploads as $file) {
            $array[] = self::transformAssetModelFile($file, $assetmodel);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAssetModelFile($file, $assetmodel)
    {

        $array = [
            'id' => (int) $file->id,
            'filename' => e($file->filename),
            'note' => $file->note ? e($file->note) : null,
            'url' => route('show/modelfile', [$assetmodel->id, $file->id]),
            'created_by' => ($file->adminuser) ? [
                'id' => (int) $file->adminuser->id,
                'name' => e($file->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($file->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($file->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($file->deleted_at, 'datetime'),
        ];

        $permissions_array['available_actions'] = [
            'delete' => (Gate::allows('update', $assetmodel) && ($assetmodel->deleted_at == '')),
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformAssetModelsDatatable($assetmodels)
    {
        return (new DatatablesTransformer)->transformDatatables($assetmodels);
    }
}
