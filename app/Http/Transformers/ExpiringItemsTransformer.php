<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use Carbon\Carbon;

class ExpiringItemsTransformer
{
    public function transformAssets($assets, $total)
    {
        $rows = [];

        foreach ($assets as $asset) {
            $rows[] = [
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
                'model' => $asset->model->name ?? '',
                'model_number' => $asset->model->model_number ?? '',
                'purchase_date' => Helper::getFormattedDateObject($asset->purchase_date, 'date'),
                'eol_rate' => (($asset->asset_eol_date != '') && ($asset->purchase_date != '')) ? (int)Carbon::parse($asset->asset_eol_date)->diffInMonths($asset->purchase_date, true) . ' months' : null,
                'eol_date' => Helper::getFormattedDateObject($asset->eol_date, 'date'),
                'warranty_expires' => $asset->warranty_expires ? $asset->warranty_expires_formatted_date .' ('.$asset->warranty_expires_diff_for_humans.')' : '',
            ];
        }

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }

    public function transformLicenses($licenses, $total)
    {
        $rows = [];

        foreach ($licenses as $license) {

            $rows[] = [
                'id' => $license->id,
                'name' => $license->name,
                'serial' => $license,
                'purchase_date' => $license->purchase_date_formatted ?? null,
                'expiration' => $license->expires_formatted_date ? $license->expires_formatted_date . ($license->expires_diff_for_humans ? ' ('.$license->expires_diff_for_humans.')' : '') : null,
                'termination_date' => $license->terminates_formatted_date ? $license->terminates_formatted_date . ($license->terminates_diff_for_humans ? ' ('.$license->terminates_diff_for_humans.')' : '') : null,
            ];
        }

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }
}