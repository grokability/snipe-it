<?php

namespace App\Presenters;

/**
 * Class LicensePresenter
 *
 * @property \App\Models\License $model
 */
class LicensePresenter extends Presenter
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
                'field' => 'checkbox',
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
            ],  [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'formatter' => 'licensesLinkFormatter',
            ], [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'product_key',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.license_key'),
                'formatter' => 'licenseKeyFormatter',
            ], [
                'field' => 'expiration_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.expiration'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'termination_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/licenses/form.termination_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'license_email',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.to_email'),
                'formatter' => 'emailFormatter',
            ], [
                'field' => 'license_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.to_name'),
            ], [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.category'),
                'visible' => false,
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'supplier',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
                'formatter' => 'suppliersLinkObjFormatter',
            ], [
                'field' => 'manufacturer',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.manufacturer'),
                'formatter' => 'manufacturersLinkObjFormatter',
            ],  [
                'field' => 'min_amt',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('mail.min_QTY'),
                'formatter' => 'minAmtFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'seats',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/accessories/general.total'),
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'free_seats_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/accessories/general.remaining'),
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'percent_remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => '% '.trans('general.remaining'),
                'visible' => true,
                'formatter' => 'progressBarFormatter',
            ], [
                'field' => 'purchase_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.purchase_date'),
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'depreciation',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.depreciation'),
                'visible' => false,
                'formatter' => 'depreciationsLinkObjFormatter',
            ],

            [
                'field' => 'maintained',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/licenses/form.maintained'),
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'reassignable',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/licenses/form.reassignable'),
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.purchase_cost'),
                'footerFormatter' => 'sumFormatterQuantity',
                'class' => 'text-right',
            ], [
                'field' => 'purchase_order',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/licenses/form.purchase_order'),
            ], [
                'field' => 'order_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.order_number'),
            ], [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ],
            [
                'field' => 'requestable',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.requestable'),
                'formatter' => 'trueFalseFormatter',
            ],
        ];

        $layout[] = [
            'field' => 'checkincheckout',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('general.checkin').'/'.trans('general.checkout'),
            'visible' => true,
            'formatter' => 'licenseInOutFormatter',
            'printIgnore' => true,
        ];

        $layout[] = [
            'field' => 'actions',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'licensesActionsFormatter',
            'printIgnore' => true,
            'class' => 'hidden-print',
        ];

        return json_encode($layout);
    }

    /**
     * Json Column Layout for bootstrap table
     *
     * @return string
     */
    public static function dataTableLayoutSeats(bool $withCheckbox = true)
    {
        $layout = [];

        if ($withCheckbox) {
            $layout[] = [
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ];
        }

        $layout = array_merge($layout, [[
            'field' => 'id',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => true,
            'switchable' => true,
            'title' => trans('general.id'),
            'visible' => false,
        ], [
            'field' => 'assigned_user',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => true,
            'title' => trans('admin/licenses/general.user'),
            'visible' => true,
            'formatter' => 'usersLinkObjFormatter',
        ], [
            'field' => 'assigned_user.email',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => true,
            'title' => trans('admin/users/table.email'),
            'visible' => true,
            'formatter' => 'emailFormatter',
        ],
            [
                'field' => 'assigned_user.companies',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.companies'),
                'visible' => true,
                'formatter' => 'companiesArrayLinkFormatter',
            ],
            [
                'field' => 'assigned_user.department',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.department'),
                'visible' => false,
                'formatter' => 'departmentNameLinkFormatter',
            ], [
                'field' => 'assigned_asset',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/licenses/form.asset'),
                'visible' => true,
                'formatter' => 'hardwareLinkObjFormatter',
            ], [
                'field' => 'location',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.location'),
                'visible' => true,
                'formatter' => 'locationsLinkObjFormatter',
            ],
            [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ],
            [
                'field' => 'checkincheckout',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('general.checkin').'/'.trans('general.checkout'),
                'visible' => true,
                'formatter' => 'licenseSeatInOutFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);

        return json_encode($layout);
    }

    public static function dataTableLayoutSeatsCheckedOutToAssets($hide_fields = [])
    {
        $layout = [];

        if (! in_array('checkbox', $hide_fields)) {
            $layout[] = [
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ];
        }

        $layout = array_merge($layout, [
            [
                'field' => 'id',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'license',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'formatter' => 'licensesLinkObjFormatter',
            ],
            [
                'field' => 'license.serial',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/licenses/form.license_key'),
                'formatter' => 'licenseKeyFormatter',
            ],
            [
                'field' => 'expiration_date',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/licenses/form.expiration'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ],
            [
                'field' => 'checkincheckout',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('general.checkin'),
                'visible' => true,
                'formatter' => 'licenseSeatInOutFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);

        return json_encode($layout);
    }

    /**
     * Link to this licenses Name
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\License', $this])) {
            return '<a href="'.route('licenses.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }

    }

    /**
     * Link to this licenses Name
     *
     * @return string
     */
    public function fullName()
    {
        return $this->name;
    }

    /**
     * Url to view this item.
     *
     * @return string
     */
    public function viewUrl()
    {
        return route('licenses.show', $this->id);
    }

    public function calendarUrl(): ?string
    {
        return route('licenses.show', $this->model->id);
    }

    public function calendarColor(): ?string
    {
        return $this->model->category?->tag_color
            ?? $this->model->manufacturer?->tag_color
            ?? $this->model->supplier?->tag_color;
    }

    /**
     * Column layout for the licenses tab on /account/requestable.
     * Licenses don't carry an image column; category + free-seats
     * remaining stand in for the location + numRemaining pair the
     * other tabs use. Paired with api.licenses.requestable +
     * LicensesTransformer.
     */
    public static function dataTableLayoutRequestable(): string
    {
        return json_encode([
            [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.name'),
                'formatter' => 'licenseRequestableNameFormatter',
            ], [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'company.name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.company'),
            ], [
                'field' => 'remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/licenses/form.remaining_seats'),
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'licenseRequestableActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);
    }
}
