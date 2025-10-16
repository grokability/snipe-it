<?php

namespace App\Presenters;

/**
 * Class ManufacturerPresenter
 */
class ManufacturerPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field'        => 'checkbox',
                'checkbox'     => true,
                'titleTooltip' => trans('general.select_all_none'),
            ],
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/manufacturers/table.name'),
                'visible' => true,
                'formatter' => 'manufacturersLinkFormatter',
            ],
            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'url',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.url'),
                'visible' => true,
                'formatter' => 'externalLinkFormatter',
            ],
            [
                'field' => 'support_url',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.support_url'),
                'visible' => true,
                'formatter' => 'externalLinkFormatter',
            ],

            [
                'field' => 'support_phone',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.support_phone'),
                'visible' => true,
                'formatter' => 'phoneFormatter',
            ],

            [
                'field' => 'support_email',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.support_email'),
                'visible' => true,
                'formatter' => 'emailFormatter',
            ],
            [
                'field' => 'warranty_lookup_url',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.warranty_lookup_url'),
                'visible' => false,
                'formatter' => 'externalLinkFormatter',
            ],

            [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.assets'),
                'visible' => true,
                'class' => 'css-barcode',
            ],
            [
                'field' => 'licenses_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.licenses'),
                'visible' => true,
                'class' => 'css-license',
            ],
            [
                'field' => 'consumables_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.consumables'),
                'visible' => true,
                'class' => 'css-consumable',
            ],
            [
                'field' => 'accessories_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.accessories'),
                'visible' => true,
                'class' => 'css-accessory',
            ], [
                'field' => 'components_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.components'),
                'visible' => true,
                'class' => 'css-component',
            ], [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'manufacturersActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this manufacturers name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('manufacturers.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('manufacturers.show', $this->id);
    }
}
