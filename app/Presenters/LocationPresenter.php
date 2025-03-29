<?php

namespace App\Presenters;

/**
 * Class LocationPresenter
 */
class LocationPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'bulk_selectable',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
            ], [
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
                'title' => trans('admin/locations/table.name'),
                'visible' => true,
                'formatter' => 'locationsLinkFormatter',
            ], 
            [
                'field' => 'type',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.type'),
                'titleTooltip' => trans('general.type'),
                'visible' => true,
            ],
 [
                'field' => 'parent',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.parent'),
                'visible' => true,
                'formatter' => 'locationsLinkObjFormatter',
            ],
            [
                'field' => 'status',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.status'),
                'titleTooltip' => trans('general.status'),
                'visible' => true,
            ], [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/message.current_location'),
                'visible' => true,
            ], [
                'field' => 'rtd_assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/hardware/form.default_location'),
                'titleTooltip' => trans('admin/hardware/form.default_location'),
                'tooltip' => 'true',
                'visible' => false,
                'class' => 'css-house-flag',
            ], [
                'field' => 'assigned_assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/message.assigned_assets'),
                'titleTooltip' =>  trans('admin/locations/message.assigned_assets'),
                'visible' => true,
                'class' => 'css-house-laptop',
            ],
            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'accessories_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.accessories'),
                'titleTooltip' =>  trans('general.accessories'),
                'visible' => true,
                'class' => 'css-accessory',
            ], [
                'field' => 'assigned_accessories_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.accessories_assigned'),
                'titleTooltip' =>  trans('general.accessories_assigned'),
                'visible' => true,
                'class' => 'css-accessory-alt',
            ],
            [
                'field' => 'users_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.people'),
                'titleTooltip' =>  trans('general.people'),
                'visible' => true,
                'class' => 'css-house-user',
            ], [
                'field' => 'currency',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('general.currency_text'),
                'titleTooltip' =>  trans('general.currency_text'),
                'visible' => true,
                'class' => 'css-currency',
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
                'field' => 'ldap_ou',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/locations/table.ldap_ou'),
                'visible' => false,
            ], [
                'field' => 'manager',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/users/table.manager'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],  [
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
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'locationsActionsFormatter',
            ],
        ];

        return json_encode($layout);
    }

    public static function assignedAccessoriesDataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'accessory',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.accessory'),
                'visible' => true,
                'formatter' => 'accessoriesLinkObjFormatter',
            ],
            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'note',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'created_at',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/hardware/table.checkout_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.admin'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'available_actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'accessoriesInOutFormatter',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this locations name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('locations.show', $this->name, $this->id);
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
        return route('locations.show', $this->id);
    }

    public function glyph()
    {
        return '<x-icon type="locations" />';
    }

    public function fullName()
    {
        return $this->name;
    }
}
