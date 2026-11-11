<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class ProfileTransformer
{
    public function transformFiles(Collection $files, $total)
    {
        $array = [];
        foreach ($files as $file) {
            $array[] = self::transformFile($file);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformFile(Actionlog $file)
    {
        $array = [
            'id' => (int) $file->id,
            'icon' => Helper::filetype_icon($file->filename),
            'item' => ($file->item) ? [
                'name' => $file->item->display_name ? e($file->item->display_name) : null,
                'type' => e($file->itemType()),
            ] : null,
            'filename' => e($file->filename),
            'signature_file' => ($file->accept_signature) ? route('profile.signature.view', ['filename' => $file->accept_signature]) : null,
            'note' => e($file->note),
            'url' => route('profile.storedeula.download', ['filename' => $file->filename]),
            'file' => route('profile.storedeula.download', ['filename' => $file->filename]),
            'created_at' => Helper::getFormattedDateObject($file->created_at, 'datetime'),
        ];

        return $array;
    }

    public function transformAssets(Collection $assets, int $total): array
    {
        $rows = [];
        foreach ($assets as $asset) {
            $rows[] = $this->transformAsset($asset);
        }

        return (new DatatablesTransformer)->transformDatatables($rows, $total);
    }

    public function transformAsset(Asset $asset): array
    {
        $array = [
            'id' => (int) $asset->id,
            'name' => e($asset->name),
            'asset_tag' => e($asset->asset_tag),
            'serial' => $asset->serial ? e($asset->serial) : null,
            'image' => $asset->getImageUrl() ?: null,
            'byod' => (bool) $asset->byod,
            'model' => $asset->model ? e($asset->model->name) : null,
            'model_number' => (($asset->model) && ($asset->model->model_number)) ? e($asset->model->model_number) : null,
            'category' => (($asset->model) && ($asset->model->category)) ? e($asset->model->category->name) : null,
            'manufacturer' => (($asset->model) && ($asset->model->manufacturer)) ? e($asset->model->manufacturer->name) : null,
            'status_label' => $asset->status ? e($asset->status->name) : null,
            'location' => $asset->location ? e($asset->location->name) : null,
            'rtd_location' => $asset->defaultLoc ? e($asset->defaultLoc->name) : null,
            'last_checkout' => Helper::getFormattedDateObject($asset->last_checkout, 'datetime'),
            'expected_checkin' => Helper::getFormattedDateObject($asset->expected_checkin, 'datetime'),
            'last_audit_date' => Helper::getFormattedDateObject($asset->last_audit_date, 'datetime'),
            'next_audit_date' => Helper::getFormattedDateObject($asset->next_audit_date, 'date'),
            'asset_eol_date' => $asset->asset_eol_date ? Helper::getFormattedDateObject($asset->asset_eol_date, 'date') : null,
        ];

        if (Gate::allows('self.view_purchase_cost')) {
            $array['purchase_cost'] = Helper::formatCurrencyOutput($asset->purchase_cost);
        }

        $array['custom_fields'] = $this->transformAssetCustomFields($asset);

        return $array;
    }

    /**
     * Custom-field map keyed by field name (same shape bs-table's
     * customFieldsFormatter expects). Encrypted fields stay out of the
     * response entirely.
     */
    protected function transformAssetCustomFields(Asset $asset): object|array
    {
        return (new CustomFieldValuesTransformer)->transform(
            $asset,
            includeEncrypted: false,
            renderMarkdown: false,
            filterFieldExpression: 'display_in_user_view',
        ) ?: new \stdClass;
    }
}
