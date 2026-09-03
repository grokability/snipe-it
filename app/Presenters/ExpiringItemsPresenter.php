<?php

namespace App\Presenters;

/**
 * Class ExpiringAssetsLicensesReportPresenter
 *
 * @package App\Presenters
 */
class ExpiringItemsPresenter extends Presenter
{
    /**
     * JSON column layout for expiring assets table.
     *
     * @return string
     */
    public static function assetsDataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.id'),
                'visible' => true,
            ],
            [
                'field' => 'asset_tag',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.tag'),
                'visible' => true,
            ],
            [
                'field' => 'model',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/hardware/form.model'),
                'visible' => true,
            ],
            [
                'field' => 'model_number',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.model_no'),
                'visible' => true,
            ],
            [
                'field' => 'purchase_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.purchase_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'eol_rate',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.eol_rate'),
                'visible' => true,
            ],
            [
                'field' => 'eol_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.eol_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'warranty_expires',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.warranty_expires'),
                'visible' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * JSON column layout for expiring licenses table.
     *
     * @return string
     */
    public static function licensesDataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.id'),
                'visible' => true,
            ],
            [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.name'),
                'visible' => true,
            ],
            [
                'field' => 'serial',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.license_serial'),
                'visible' => true,
            ],
            [
                'field' => 'purchase_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.purchase_date'),
                'visible' => true,
            ],
            [
                'field' => 'expiration',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.expiration'),
                'visible' => true,
            ],
            [
                'field' => 'termination_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.termination_date'),
                'visible' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Combined report payload.
     *
     * @param array $assets
     * @param array $licenses
     * @param int $days
     * @param bool $includeExpired
     * @return array
     */
    public static function reportData(array $assets, array $licenses, int $days, bool $includeExpired)
    {
        return [
            'assets' => $assets,
            'licenses' => $licenses,
            'meta' => [
                'days' => $days,
                'include_expired' => $includeExpired,
                'asset_count' => count($assets),
                'license_count' => count($licenses),
            ],
            'table_layouts' => [
                'assets' => json_decode(self::assetsDataTableLayout(), true),
                'licenses' => json_decode(self::licensesDataTableLayout(), true),
            ],
        ];
    }
}