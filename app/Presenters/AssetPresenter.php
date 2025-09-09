<?php

namespace App\Presenters;

use App\Models\CustomField;
use Carbon\CarbonImmutable;
use DateTime;

/**
 * Class AssetPresenter
 */
class AssetPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'checkbox',
                'checkbox' => true,
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
            ], [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'company',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.company'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.name'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ], [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/table.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'asset_tag',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/hardware/table.asset_tag'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ], [
                'field' => 'serial',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.serial'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ],  [
                'field' => 'model',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.model'),
                'visible' => true,
                'formatter' => 'modelsLinkObjFormatter',
            ], [
                'field' => 'model_number',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/models/table.modelnumber'),
                'visible' => false,
            ], [
                'field' => 'category',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.category'),
                'visible' => true,
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'status_label',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.status'),
                'visible' => true,
                'formatter' => 'statuslabelsLinkObjFormatter',
            ], [
                'field' => 'assigned_to',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.checkedout_to'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ], [
                'field' => 'employee_number',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.employee_number'),
                'visible' => false,
                'formatter' => 'employeeNumFormatter',
            ],[
                'field' => 'jobtitle',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/users/table.title'),
                'visible' => false,
                'formatter' => 'jobtitleFormatter',
            ], [
                'field' => 'location',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.location'),
                'visible' => true,
                'formatter' => 'deployedLocationFormatter',
            ], [
                'field' => 'rtd_location',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.default_location'),
                'visible' => false,
                'formatter' => 'deployedLocationFormatter',
            ], [
                'field' => 'manufacturer',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ], [
                'field' => 'supplier',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
                'formatter' => 'suppliersLinkObjFormatter',
            ], [
                'field' => 'purchase_date',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.purchase_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'age',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('general.age'),
            ], [
                'field' => 'purchase_cost',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.purchase_cost'),
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ], [
                "field" => "book_value",
                "searchable" => false,
                "sortable" => false,
                "title" => trans('admin/hardware/table.book_value'),
                "footerFormatter" => 'sumFormatter',
                "class" => "text-right",
            ],[
                'field' => 'order_number',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.order_number'),
                'formatter' => 'orderNumberObjFilterFormatter',
            ], [
                'field' => 'eol',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.eol_rate'),
            ],
            [
                'field' => 'asset_eol_date',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.eol_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'warranty_months',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.warranty'),
            ], [
                'field' => 'warranty_expires',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('admin/hardware/form.warranty_expires'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'requestable',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.requestable'),
                'formatter' => 'trueFalseFormatter',

            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),

            ], [
                'field' => 'checkout_counter',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.checkouts_count'),

            ], [
                'field' => 'checkin_counter',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.checkins_count'),

            ], [
                'field' => 'requests_counter',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.user_requests_count'),

            ], [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
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
                'field' => 'last_checkout',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/table.checkout_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'last_checkin',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/table.last_checkin_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'expected_checkin',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.expected_checkin'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'last_audit_date',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.last_audit'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'next_audit_date',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.next_audit_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'byod',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.byod'),
                'class' => 'byod',
                'formatter' => 'trueFalseFormatter',

            ],
        ];

        // This looks complicated, but we have to confirm that the custom fields exist in custom fieldsets
        // *and* those fieldsets are associated with models, otherwise we'll trigger
        // javascript errors on the bootstrap tables side of things, since we're asking for properties
        // on fields that will never be passed through the REST API since they're not associated with
        // models. We only pass the fieldsets that pertain to each asset (via their model) so that we
        // don't junk up the REST API with tons of custom fields that don't apply

        $fields = CustomField::whereHas('fieldset', function ($query) {
            $query->whereHas('models');
        })->get();

        // Note: We do not need to e() escape the field names here, as they are already escaped when
        // they are presented in the blade view. If we escape them here, custom fields with quotes in their
        // name can break the listings page. - snipe
        foreach ($fields as $field) {
            $layout[] = [
                'field' => $field->db_column,
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => $field->name,
                'formatter'=> 'customFieldsFormatter',
                'escape' => true,
                'class' => ($field->field_encrypted == '1') ? 'css-padlock' : '',
                'visible' => ($field->show_in_listview == '1') ? true : false,
            ];
        }

        $layout[] = [
            'field' => 'checkincheckout',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('general.checkin').'/'.trans('general.checkout'),
            'visible' => true,
            'formatter' => 'hardwareInOutFormatter',
            'printIgnore' => true,
        ];

        $layout[] = [
            'field' => 'actions',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'hardwareActionsFormatter',
            'printIgnore' => true,
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
                'title' => trans('general.created_by'),
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
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Generate html link to this items name.
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('hardware.show', e($this->name), $this->id);
    }

    public function modelUrl()
    {
        if ($this->model->model) {
            return $this->model->model->present()->nameUrl();
        }

        return '';
    }

    /**
     * Generate img tag to this items image.
     * @return mixed|string
     */
    public function imageUrl()
    {
        $imagePath = '';
        if ($this->image && ! empty($this->image)) {
            $imagePath = $this->image;
            $imageAlt = $this->name;
        } elseif ($this->model && ! empty($this->model->image)) {
            $imagePath = $this->model->image;
            $imageAlt = $this->model->name;
        }
        $url = config('app.url');
        if (! empty($imagePath)) {
            $imagePath = '<img src="'.$url.'/uploads/assets/'.$imagePath.' height="50" width="50" alt="'.$imageAlt.'">';
        }

        return $imagePath;
    }

    /**
     * Generate img tag to this items image.
     * @return mixed|string
     */
    public function imageSrc()
    {
        $imagePath = '';
        if ($this->image && ! empty($this->image)) {
            $imagePath = $this->image;
        } elseif ($this->model && ! empty($this->model->image)) {
            $imagePath = $this->model->image;
        }
        if (! empty($imagePath)) {
            return config('app.url').'/uploads/assets/'.$imagePath;
        }

        return $imagePath;
    }

    /**
     * Get Displayable Name
     * @return string
     *
     * @todo this should be factored out - it should be subsumed by fullName (below)
     *
     **/
    public function name()
    {
        return $this->fullName;
    }

    /**
     * Helper for notification polymorphism.
     * @return mixed
     */
    public function fullName()
    {
        $str = '';

        // Asset name
        if ($this->model->name) {
            $str .= $this->model->name;
        }

        // Asset tag
        if ($this->asset_tag) {
            $str .= ' ('.$this->model->asset_tag.')';
        }

        // Asset Model name
        if ($this->model->model) {
            $str .= ' - '.$this->model->model->name;
        }

        return $str;
    }

    /**
     * Returns the date this item hits EOL.
     * @return false|string
     */
    public function eol_date()
    {
        if (($this->purchase_date) && ($this->model->model) && ($this->model->model->eol)) {
            return CarbonImmutable::parse($this->purchase_date)->addMonths($this->model->model->eol)->format('Y-m-d');
        }
    }

    /**
     * How many months until this asset hits EOL.
     * @return null
     */
    public function months_until_eol()
    {
        $today = date('Y-m-d');
        $d1 = new DateTime($today);
        $d2 = new DateTime($this->eol_date());

        if ($this->eol_date() > $today) {
            $interval = $d2->diff($d1);
        } else {
            $interval = null;
        }

        return $interval;
    }

    /**
     * @return string
     * This handles the status label "meta" status of "deployed" if
     * it's assigned. Should maybe deprecate.
     */
    public function statusMeta()
    {
        if ($this->model->assigned_to) {
            return 'deployed';
        }

        return $this->model->assetstatus->getStatuslabelType();
    }

    /**
     * @return string
     * This handles the status label "meta" status of "deployed" if
     * it's assigned. Should maybe deprecate.
     */
    public function statusText()
    {
        if ($this->model->assigned) {
            return trans('general.deployed');
        }

        return $this->model->assetstatus->name;
    }

    /**
     * @return string
     * This handles the status label "meta" status of "deployed" if
     * it's assigned. Results look like:
     *
     * (if assigned and the status label is "Ready to Deploy"):
     * (Deployed)
     *
     * (f assigned and status label is not "Ready to Deploy":)
     * Deployed (Another Status Label)
     *
     * (if not deployed:)
     * Another Status Label
     */
    public function fullStatusText()
    {
        // Make sure the status is valid
        if ($this->assetstatus) {

            // If the status is assigned to someone or something...
            if ($this->model->assigned) {

                // If it's assigned and not set to the default "ready to deploy" status
                if ($this->assetstatus->name != trans('general.ready_to_deploy')) {
                    return trans('general.deployed').' ('.$this->model->assetstatus->name.')';
                }

                // If it's assigned to the default "ready to deploy" status, just
                // say it's deployed - otherwise it's confusing to have a status that is
                // both "ready to deploy" and deployed at the same time.
                return trans('general.deployed');
            }

            // Return just the status name
            return $this->model->assetstatus->name;
        }

        // This status doesn't seem valid - either data has been manually edited or
        // the status label was deleted.
        return 'Invalid status';
    }

    /**
     * Date the warranty expires.
     * @return false|string
     */
    public function warranty_expires()
    {
        if (($this->purchase_date) && ($this->warranty_months)) {
            $date = date_create($this->purchase_date);
            date_add($date, date_interval_create_from_date_string($this->warranty_months.' months'));

            return date_format($date, 'Y-m-d');
        }

        return false;
    }

    /**
     * Used to take user created URL and dynamically fill in the needed values per asset
     * @return string
     */
    public function dynamicUrl($dynamic_url)
    {
        $url = (str_replace('{LOCALE}',\App\Models\Setting::getSettings()->locale, $dynamic_url));
        $url = (str_replace('{SERIAL}', urlencode($this->model->serial), $url));
        $url = (str_replace('{MODEL_NAME}', urlencode($this->model->model->name), $url));
        $url = (str_replace('{MODEL_NUMBER}', urlencode($this->model->model->model_number), $url));
        return $url;
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('hardware.show', $this->id);
    }

    public function glyph()
    {
        return '<x-icon type="assets" />';
    }
}
