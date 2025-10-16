<?php

namespace App\Presenters;

/**
 * Class LocationPresenter
 */
class SupplierPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
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
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'suppliersLinkFormatter',
            ], [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.assets'),
                'titleTooltip' =>  trans('general.assets'),
                'visible' => true,
                'class' => 'css-barcode',
            ],  [
                'field' => 'accessories_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.accessories'),
                'titleTooltip' =>  trans('general.accessories'),
                'visible' => true,
                'class' => 'css-accessory',
            ],
            [
                'field' => 'licenses_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.licenses'),
                'titleTooltip' =>  trans('general.licenses'),
                'visible' => true,
                'class' => 'css-license',
            ], [
                'field' => 'components_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.components'),
                'titleTooltip' =>  trans('general.components'),
                'visible' => true,
                'class' => 'css-component',
            ], [
                'field' => 'consumables_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.consumables'),
                'titleTooltip' =>  trans('general.consumables'),
                'visible' => true,
                'class' => 'css-consumable',
            ], [
                'field' => 'url',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.url'),
                'visible' => true,
                'formatter' => 'externalLinkFormatter',
            ], [
                'field' => 'address',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.address'),
                'visible' => true,
            ], [
                'field' => 'address2',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.address2'),
                'visible' => false,
            ], [
                'field' => 'city',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.city'),
                'visible' => true,
            ], [
                'field' => 'state',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.state'),
                'visible' => true,
            ], [
                'field' => 'zip',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.zip'),
                'visible' => false,
            ], [
                'field' => 'country',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.country'),
                'visible' => false,
            ], [
                'field' => 'phone',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.phone'),
                'visible' => false,
                'formatter'    => 'phoneFormatter',
            ], [
                'field' => 'fax',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/suppliers/table.fax'),
                'visible' => false,
                'formatter'    => 'phoneFormatter',
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
            ], [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],  [
                'field' => 'created_by',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'suppliersActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }
    

    /**
     * Link to this supplier name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('suppliers.show', $this->name, $this->id);
    }

    /**
     * Getter for Polymorphism.
     * @return mixed
     */
    public function name()
    {
        return $this->model->name;
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('suppliers.show', $this->id);
    }

    public function glyph()
    {
        return '<x-icon type="suppliers" />';
    }

    public function fullName()
    {
        return $this->name;
    }
}
