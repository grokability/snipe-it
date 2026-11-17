<?php

namespace App\Presenters;

/**
 * Class LocationPresenter
 */
class LocationPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     *
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'bulk_selectable',
                'scope' => 'col',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ], [
                'field' => 'id',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.company'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ],
            [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'locationsLinkFormatter',
            ], [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'parent',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.parent'),
                'visible' => true,
                'formatter' => 'locationsLinkObjFormatter',
            ], [
                'field' => 'users_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.people'),
                'titleTooltip' => trans('general.people'),
                'visible' => true,
                'class' => 'css-house-user text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'assets_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/message.current_location'),
                'titleTooltip' => trans('admin/locations/message.current_location'),
                'visible' => true,
                'class' => 'css-house-laptop text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'rtd_assets_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.default_location'),
                'titleTooltip' => trans('admin/hardware/form.default_location'),
                'tooltip' => 'true',
                'visible' => false,
                'class' => 'css-house-flag text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'assigned_assets_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/message.assigned_assets'),
                'titleTooltip' => trans('admin/locations/message.assigned_assets'),
                'visible' => true,
                'class' => 'css-barcode text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'accessories_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.accessories'),
                'titleTooltip' => trans('general.accessories'),
                'visible' => true,
                'class' => 'css-accessory text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'assigned_accessories_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.accessories_assigned'),
                'titleTooltip' => trans('general.accessories_assigned'),
                'visible' => true,
                'class' => 'css-accessory-alt text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'components_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.components'),
                'titleTooltip' => trans('general.components'),
                'visible' => true,
                'class' => 'css-component text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'consumables_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.consumables'),
                'titleTooltip' => trans('general.consumables'),
                'visible' => true,
                'class' => 'css-consumable text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'children_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.child_locations'),
                'titleTooltip' => trans('general.child_locations'),
                'visible' => true,
                'class' => 'css-child-locations text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'currency',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.currency_text'),
                'titleTooltip' => trans('general.currency_text'),
                'visible' => true,
                'class' => 'css-currency',
            ], [
                'field' => 'address',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.address'),
                'visible' => true,
            ], [
                'field' => 'address2',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.address2'),
                'visible' => false,
            ], [
                'field' => 'city',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.city'),
                'visible' => true,
            ], [
                'field' => 'state',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.state'),
                'visible' => true,
            ], [
                'field' => 'zip',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.zip'),
                'visible' => false,
            ], [
                'field' => 'country',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.country'),
                'visible' => false,
            ], [
                'field' => 'phone',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.phone'),
                'visible' => false,
                'formatter' => 'phoneFormatter',
            ], [
                'field' => 'fax',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/suppliers/table.fax'),
                'visible' => false,
                'formatter' => 'phoneFormatter',
            ], [
                'field' => 'ldap_ou',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/locations/table.ldap_ou'),
                'visible' => false,
            ], [
                'field' => 'manager',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.manager'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'tag_color',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.tag_color'),
                'visible' => false,
                'formatter' => 'colorTagFormatter',
            ], [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
            ], [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'locationsActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    public static function assignedAccessoriesDataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'accessory',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.accessory'),
                'visible' => true,
                'formatter' => 'accessoriesLinkObjFormatter',
            ],
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/hardware/table.checkout_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'available_actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'accessoriesInOutFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this locations name
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\Location', $this])) {
            return '<a href="'.route('locations.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }
    }

    /**
     * Getter for Polymorphism.
     *
     * @return mixed
     */
    public function name()
    {
        return $this->model->name;
    }

    /**
     * Url to view this item.
     *
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

    public function formattedNameLink()
    {

        if (auth()->user()->can('view', ['\App\Models\Location', $this])) {
            return ($this->tag_color ? "<i class='fa-solid fa-fw fa-square' style='color: ".e($this->tag_color)."' aria-hidden='true'></i>" : '').'<a href="'.route('locations.show', e($this->id)).'">'.e($this->display_name).'</a>';
        }

        return ($this->tag_color ? "<i class='fa-solid fa-fw fa-square' style='color: ".e($this->tag_color)."' aria-hidden='true'></i> " : '').e($this->display_name);
    }
}
