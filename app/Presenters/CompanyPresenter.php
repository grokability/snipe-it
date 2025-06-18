<?php

namespace App\Presenters;

/**
 * Class CompanyPresenter
 */
class CompanyPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/companies/table.name'),
                'visible' => true,
                'formatter' => 'companiesLinkFormatter',
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
                'field' => 'email',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/suppliers/table.email'),
                'visible' => true,
				'formatter' => 'emailFormatter',
            ], [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'users_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.users'),
                'visible' => true,
                'class' => 'css-users',

            ], [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.assets'),
                'visible' => true,
                'class' => 'css-barcode',

            ], [
                'field' => 'licenses_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.licenses'),
                'visible' => true,
                'class' => 'css-license',
            ], [
                'field' => 'accessories_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.accessories'),
                'visible' => true,
                'class' => 'css-accessory',
            ], [
                'field' => 'consumables_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.consumables'),
                'visible' => true,
                'class' => 'css-consumable',
            ], [
                'field' => 'components_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.components'),
                'visible' => true,
                'class' => 'css-component',
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
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
                'formatter' => 'companiesActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this companies name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('companies.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('companies.show', $this->id);
    }
}
