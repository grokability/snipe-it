<?php

namespace App\Presenters;

use App\Enums\FileStorage;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Class UserPresenter
 *
 * @property \App\Models\User $model
 */
class UserPresenter extends Presenter
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
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
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
                'field' => 'username',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/users/table.username'),
                'visible' => true,
                'formatter' => 'usernameRoleLinkFormatter',
            ],
            [
                'field' => 'avatar',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.importer.avatar'),
                'visible' => false,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/users/table.name'),
                'visible' => true,
                'formatter' => 'usersLinkFormatter',
            ], [
                'field' => 'first_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.first_name'),
                'visible' => false,
                'formatter' => 'usersLinkFormatter',
            ], [
                'field' => 'last_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.last_name'),
                'visible' => false,
                'formatter' => 'usersLinkFormatter',
            ],
            [
                'field' => 'display_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.display_name'),
                'visible' => false,
                'formatter' => 'usersLinkFormatter',
            ],
            [
                'field' => 'companies',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.companies'),
                'visible' => false,
                'formatter' => 'companiesArrayLinkFormatter',
            ],
            [
                'field' => 'employee_num',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.employee_number'),
                'visible' => false,
            ],
            [
                'field' => 'jobtitle',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.title'),
                'visible' => true,
                'formatter' => 'usersLinkFormatter',
            ],
            [
                'field' => 'vip',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/general.vip_label'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'remote',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/general.remote'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'email',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.email'),
                'visible' => true,
                'formatter' => 'emailFormatter',
            ],
            [
                'field' => 'phone',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.phone'),
                'visible' => false,
                'formatter' => 'phoneFormatter',
            ],
            [
                'field' => 'mobile',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.mobile'),
                'visible' => false,
                'formatter' => 'mobileFormatter',
            ],
            [
                'field' => 'website',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.website'),
                'visible' => false,
                'formatter' => 'externalLinkFormatter',
            ],
            [
                'field' => 'address',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.address'),
                'visible' => false,
            ],
            [
                'field' => 'city',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.city'),
                'visible' => false,
            ],
            [
                'field' => 'state',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.state'),
                'visible' => false,
            ],
            [
                'field' => 'country',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.country'),
                'visible' => false,
            ],
            [
                'field' => 'zip',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.zip'),
                'visible' => false,
            ],

            [
                'field' => 'locale',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.language'),
                'visible' => false,
            ],
            [
                'field' => 'department',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.department'),
                'visible' => true,
                'formatter' => 'departmentsLinkObjFormatter',
            ],
            [
                'field' => 'department_manager',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/general.department_manager'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/table.location'),
                'visible' => true,
                'formatter' => 'locationsLinkObjFormatter',
            ],
            [
                'field' => 'manager',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/users/table.manager'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'assets_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'escape' => true,
                'class' => 'css-barcode',
                'title' => trans('general.assets'),
                'titleTooltip' => trans('general.assets'),
                'visible' => true,
                'formatter' => 'linkNumberToUserAssetsFormatter',
            ],
            [
                'field' => 'licenses_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'class' => 'css-license',
                'title' => trans('general.licenses'),
                'titleTooltip' => trans('general.licenses'),
                'visible' => true,
                'formatter' => 'linkNumberToUserLicensesFormatter',
            ],
            [
                'field' => 'consumables_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'class' => 'css-consumable',
                'title' => trans('general.consumables'),
                'titleTooltip' => trans('general.consumables'),
                'visible' => true,
                'formatter' => 'linkNumberToUserConsumablesFormatter',
            ],
            [
                'field' => 'accessories_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'class' => 'css-accessory',
                'title' => trans('general.accessories'),
                'titleTooltip' => trans('general.accessories'),
                'visible' => true,
                'formatter' => 'linkNumberToUserAccessoriesFormatter',
            ],
            [
                'field' => 'manages_users_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'class' => 'css-users',
                'title' => trans('admin/users/table.managed_users'),
                'titleTooltip' => trans('admin/users/table.managed_users'),
                'visible' => true,
                'formatter' => 'linkNumberToUserManagedUsersFormatter',
            ],
            [
                'field' => 'manages_locations_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'class' => 'css-location',
                'title' => trans('admin/users/table.managed_locations'),
                'titleTooltip' => trans('admin/users/table.managed_locations'),
                'visible' => true,
                'formatter' => 'linkNumberToUserManagedLocationsFormatter',
            ],
            [
                'field' => 'assigned_maintenances_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'class' => 'css-maintenances',
                'title' => trans('general.maintenances'),
                'titleTooltip' => trans('general.maintenances'),
                'visible' => false,
                'formatter' => 'linkNumberToUserAssignedMaintenancesFormatter',
            ],
            [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'groups',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.groups'),
                'visible' => true,
                'formatter' => 'groupsFormatter',
            ],
            [
                'field' => 'ldap_import',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/settings/general.ldap_enabled'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'two_factor_enrolled',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/general.two_factor_enrolled'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'two_factor_optin',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/users/general.two_factor_active'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'activated',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.login_enabled'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'autoassign_licenses',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.autoassign_licenses'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'start_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.start_date'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'end_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.end_date'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'last_login',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.last_login'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'activated_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.activated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'show_in_list',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.show_in_list'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'usersActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    public function emailLink()
    {
        if ($this->email) {
            return '<a href="mailto:'.$this->email.'">'.$this->email.'</a><a href="mailto:'.$this->email.'" class="hidden-xs hidden-sm"><i class="far fa-envelope"></i></a>';
        }

        return '';
    }

    /**
     * Returns the user full name, it simply concatenates
     * the user first and last name.
     *
     * @return string
     */
    //    public function fullName()
    //    {
    //        if ($this->display_name) {
    //            return 'kjdfh'.html_entity_decode($this->display_name, ENT_QUOTES | ENT_XML1, 'UTF-8');
    //        }
    //        return 'roieuoe'.html_entity_decode($this->first_name.' '.$this->last_name, ENT_QUOTES | ENT_XML1, 'UTF-8');
    //    }

    //    /**
    //     * Standard accessor.
    //     * @TODO Remove presenter::fullName() entirely?
    //     * @return string
    //     */
    //    public function name()
    //    {
    //        return $this->fullName();
    //    }

    /**
     * Returns the user Gravatar image url.
     *
     * @return string
     */
    public function gravatar()
    {

        // User's specific avatar
        if ($this->avatar) {

            // Check if it's a google avatar or some external avatar
            if ($this->isAvatarExternal()) {
                return $this->avatar;
            }

            // Otherwise assume it's an uploaded image
            return Storage::disk('public')->url(FileStorage::Avatars->publicPath().e($this->avatar));
        }

        // If the default is system default
        if (Setting::getSettings()->default_avatar == 'default.png') {
            return Storage::disk('public')->url('default.png');
        }

        // If there is a custom default avatar
        if (Setting::getSettings()->default_avatar != '') {
            return Storage::disk('public')->url(FileStorage::Avatars->publicPath().e(Setting::getSettings()->default_avatar));
        }

        // If there is no default and no custom avatar, check for gravatar
        if ((Setting::getSettings()->load_remote == '1') && (Setting::getSettings()->default_avatar == '')) {

            if ($this->model->gravatar != '') {
                $gravatar = md5(strtolower(trim($this->model->gravatar)));

                return '//gravatar.com/avatar/'.$gravatar;

            } elseif ($this->email != '') {
                $gravatar = md5(strtolower(trim($this->email)));

                return '//gravatar.com/avatar/'.$gravatar;
            }
        }

        return false;
    }

    /**
     * Formatted url for use in tables.
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\User', $this])) {
            return '<a title="'.e($this->display_name).'" href="'.route('users.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }

    }

    /**
     * Url to view this item.
     *
     * @return string
     */
    public function viewUrl()
    {
        return route('users.show', $this->id);
    }

    public function glyph()
    {
        return '<x-icon type="user"/>';
    }

    public function formattedNameLink()
    {

        if (auth()->user()->can('view', ['\App\Models\User', $this])) {
            return '<a href="'.route('users.show', e($this->id)).'" class="'.(($this->deleted_at != '') ? 'deleted' : '').'">'.e($this->display_name).'</a>';
        }

        return '<span class="'.(($this->deleted_at != '') ? 'deleted' : '').'">'.e($this->display_name).'</span>';
    }

    public function calendarUrl(): ?string
    {
        return route('users.show', $this->model->id);
    }

    public function calendarColor(): ?string
    {
        return $this->model->department?->tag_color
            ?? $this->model->company?->color;
    }

    public static function consumablesDataTableLayout(): string
    {
        $layout = [
            [
                'field' => 'consumable',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'consumablesLinkObjFormatter',
            ],
            [
                'field' => 'qty',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.qty'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ],
            [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.unit_cost'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
        ];

        return json_encode($layout);
    }

    public static function accessoriesDataTableLayout(): string
    {
        $layout = [
            [
                'field' => 'accessory',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'accessoriesLinkObjFormatter',
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.unit_cost'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ],
            [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'accessoriesInOutFormatter',
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    public static function licensesDataTableLayout(): string
    {
        $layout = [
            [
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
            [
                'field' => 'license',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'licensesLinkObjFormatter',
            ],
            [
                'field' => 'serial',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/licenses/form.license_key'),
                'visible' => true,
                'formatter' => 'licenseKeyFormatter',
            ],
            [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.purchase_cost'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ],
            [
                'field' => 'purchase_order',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/licenses/form.purchase_order'),
                'visible' => true,
            ],
            [
                'field' => 'order_number',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.order_number'),
                'visible' => true,
            ],
            [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'licenseSeatInOutFormatter',
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }
}
