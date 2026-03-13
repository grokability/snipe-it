<?php

namespace App\Http\Transformers;

use App\Models\Asset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;

class ComponentsAssetsTransformer
{
    public function transformAssets(Collection $assets, $total)
    {
        $array = [];
        foreach ($assets as $asset) {
            $array[] = self::transformAsset($asset);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAsset(Asset $asset)
    {
        $array = [
            'id' => $asset->id,
            'name' => e($asset->name),
            'created_at' => $asset->created_at->format('Y-m-d'),
            'qty' => $asset->components()->count(),
            'user_can_checkout' => $asset->availableForCheckout(),
            'note' => e($asset->note),
        ];

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', Asset::class),
            'checkin' => Gate::allows('checkin', Asset::class),
            'update' => Gate::allows('update', Asset::class),
            'delete' => Gate::allows('delete', Asset::class),
        ];

        $array += $permissions_array;

        if ($asset->model->fieldset) {
            foreach ($asset->model->fieldset->fields as $field) {
                $fields_array = [$field->name => $asset->{$field->db_column}];
                $array += $fields_array;
            }
        }

        return $array;
    }

    public function transformAssetsDatatable($assets)
    {
        return (new DatatablesTransformer)->transformDatatables($assets);
    }
}
