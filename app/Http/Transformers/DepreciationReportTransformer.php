<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;

/**
 *  This tranformer looks like it's extraneous, since we return as much or more
 * info in the AssetsTransformer, but we want to flatten these results out so that they 
 * don't dislose more information than we want. Folks with depreciation powers don't necessaily 
 * have the right to see additional info, and inspecting the API call here could disclose 
 * info they're not supposed to see.
 * 
 * @author [A. Gianotto] [<snipe@snipe.net>]
 * @since [v5.2.0]
 */
class DepreciationReportTransformer
{
    public function transformAssets(Collection $assets, $total)
    {
        $array = array();
        foreach ($assets as $asset) {
            $array[] = self::transformAsset($asset);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }


    public function transformAsset(Asset $asset)
    {

        /**
         * Set some default values here
         */
        $purchase_cost = null;
        $depreciated_value = null;
        $monthly_depreciation = null;
        $diff = null;
        $checkout_target = null;

        /**
         * If there is a location set and a currency set, use that for display
         */
        if ($asset->location && $asset->location->currency) {
            $purchase_cost_currency = $asset->location->currency;
        } else {
            $purchase_cost_currency = \App\Models\Setting::getSettings()->default_currency;
        }

        /**
         * If there is a NOT an empty purchase cost (meaning not null or '' but it *could* be zero),
         * format the purchase cost. We coould do this inline in the transformer, but we need that value 
         * for the other calculations that come after, like diff, etc.
         */
        if ($asset->purchase_cost!='') {
            $purchase_cost = $asset->purchase_cost;
        }
       

        /**
         * Override the previously set null values if there is a valid model and associated depreciation
         */
        if (($asset->model) && ($asset->model->depreciation) && ($asset->model->depreciation->months !== 0)) {
            $depreciated_value = Helper::formatCurrencyOutput($asset->getDepreciatedValue());
            $monthly_depreciation =Helper::formatCurrencyOutput($asset->purchase_cost / $asset->model->depreciation->months);
            $diff = Helper::formatCurrencyOutput(($asset->purchase_cost - $asset->getDepreciatedValue()));
        }
        else if($asset->model->eol !== null) {
            $monthly_depreciation = Helper::formatCurrencyOutput(($asset->model->eol > 0 ? ($asset->purchase_cost / $asset->model->eol) : 0));
        }

        if ($asset->assigned) {
            $checkout_target = $asset->assigned->name;
            if ($asset->checkedOutToUser()) {
                $checkout_target = $asset->assigned->getFullNameAttribute();
            } 

        }
                   
        $array = [
    
            'company' => ($asset->company) ? e($asset->company->name) : null,
            'name' => e($asset->name),
            'asset_tag' => e($asset->asset_tag),
            'serial' => e($asset->serial),
            'model' => ($asset->model) ?  e($asset->model->name) : null,
            'model_number' => (($asset->model) && ($asset->model->model_number)) ? e($asset->model->model_number) : null,
            'eol' => ($asset->purchase_date!='') ? Helper::getFormattedDateObject($asset->present()->eol_date(), 'date') : null ,
            'status_label' => ($asset->assetstatus) ? e($asset->assetstatus->name) : null,
            'status' => ($asset->assetstatus) ?  e($asset->present()->statusMeta) : null,
            'category' => (($asset->model) && ($asset->model->category)) ? e($asset->model->category->name) : null,
            'manufacturer' => (($asset->model) && ($asset->model->manufacturer)) ? e($asset->model->manufacturer->name) : null,
            'supplier' => ($asset->supplier) ? e($asset->supplier->name) : null,
            'notes' => ($asset->notes) ? e($asset->notes) : null,
            'order_number' => ($asset->order_number) ? e($asset->order_number) : null,
            'location' => ($asset->location) ? e($asset->location->name) : null,
            'warranty_expires' => ($asset->warranty_months > 0) ?  Helper::getFormattedDateObject($asset->warranty_expires, 'date') : null,
            'currency' => $purchase_cost_currency,
            'purchase_date' => Helper::getFormattedDateObject($asset->purchase_date, 'date'),
            'purchase_cost' => Helper::formatCurrencyOutput($asset->purchase_cost),
            'book_value' => Helper::formatCurrencyOutput($depreciated_value),
            'monthly_depreciation' => $monthly_depreciation,
            'checked_out_to' => ($checkout_target) ? e($checkout_target) : null,
            'diff' =>  Helper::formatCurrencyOutput($diff),
            'number_of_months' =>  ($asset->model && $asset->model->depreciation) ? e($asset->model->depreciation->months) : null,
            'depreciation' => (($asset->model) && ($asset->model->depreciation)) ?  e($asset->model->depreciation->name) : null,
            

        
        ];

        return $array;
    }

    public function transformAssetsDatatable($assets)
    {
        return (new DatatablesTransformer)->transformDatatables($assets);
    }


   
}
