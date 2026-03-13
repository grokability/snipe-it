<?php
namespace App\Presenters;

use DateTime;

/**
 * Class DepreciationReportPresenter
 * @package App\Presenters
 */
class DepreciationReportPresenter extends Presenter
{

    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
           [
                "field" => "company",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.company'),
                "visible" => false,
            ], [
                "field" => "category",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.category'),
                "visible" => true,
            ], [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                'switchable' => false,
                "title" => trans('admin/hardware/form.name'),
                "visible" => false,
            ], [
                "field" => "asset_tag",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.asset_tag'),
                "visible" => true,
            ],[
                "field" => "model",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.asset_model'),
                "visible" => true,
            ],  [
                "field" => "model_number",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/models/table.modelnumber'),
                "visible" => false
            ], [
                "field" => "serial",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/hardware/form.serial'),
                "visible" => true,
            ], [
                "field" => "depreciation",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.depreciation'),
                "visible" => true,
            ], [
                "field" => "number_of_months",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/depreciations/general.number_of_months'),
                "visible" => true,
            ],  [
                "field" => "status",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/hardware/table.status'),
                "visible" => true,
            ], [
                "field" => "checked_out_to",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/hardware/table.checkoutto'),
                "visible" => false,
            ], [
                "field" => "location",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/hardware/table.location'),
                "visible" => true,
            ],  [
                "field" => "manufacturer",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.manufacturer'),
                "visible" => false,
            ],[
                "field" => "supplier",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.supplier'),
                "visible" => false,
            ], [
                "field" => "purchase_date",
                "searchable" => true,
                "sortable" => true,
                "visible" => true,
                "title" => trans('general.purchase_date'),
                "formatter" => "dateDisplayFormatter"
            ], [
                "field" => "currency",
                "searchable" => false,
                "sortable" => false,
                "visible" => false,
                "title" =>  'Currency',
            ], [
                "field" => "purchase_cost",
                "searchable" => true,
                "sortable" => true,
                "visible" => true,
                "title" => trans('general.purchase_cost'),
                "footerFormatter" => 'sumFormatter',
                "class" => "text-right",
            ], [
                "field" => "order_number",
                "searchable" => true,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.order_number'),
            ],  [
                "field" => "eol",
                "searchable" => false,
                "sortable" => false,
                "visible" => false,
                "title" => trans('general.eol'),
                "formatter" => "dateDisplayFormatter"
            ], [
                "field" => "book_value",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "title" => trans('admin/hardware/table.book_value'),
                "footerFormatter" => 'sumFormatter',
                "class" => "text-right",
            ], [
                "field" => "monthly_depreciation",
                "searchable" => true,
                "sortable" => true,
                "visible" => true,
                "title" => trans('admin/hardware/table.monthly_depreciation')
            ],[
                "field" => "diff",
                "searchable" => false,
                "sortable" => false,
                "visible" => true,
                "title" => trans('admin/hardware/table.diff'),
                "footerFormatter" => 'sumFormatter',
                "class" => "text-right",
            ],[
                "field" => "warranty_expires",
                "searchable" => false,
                "sortable" => false,
                "visible" => false,
                "title" => trans('admin/hardware/form.warranty_expires'),
                "formatter" => "dateDisplayFormatter"
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
        if (auth()->user()->can('view', ['\App\Models\Depreciation', $this])) {
            return (string)link_to_route('depreciations.show', e($this->display_name), $this->id);
        } else {
            return e($this->display_name);
        }
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
        if ($this->image && !empty($this->image)) {
            $imagePath = $this->image;
            $imageAlt = $this->name;
        } elseif ($this->model && !empty($this->model->image)) {
            $imagePath = $this->model->image;
            $imageAlt = $this->model->name;
        }
        $url = config('app.url');
        if (!empty($imagePath)) {
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
        if ($this->image && !empty($this->image)) {
            $imagePath = $this->image;
        } elseif ($this->model && !empty($this->model->image)) {
            $imagePath = $this->model->image;
        }
        if (!empty($imagePath)) {
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

        if (( $this->purchase_date ) && ( $this->model->model ) && ($this->model->model->eol) ) {
            $date = date_create($this->purchase_date);
            date_add($date, date_interval_create_from_date_string($this->model->model->eol . ' months'));
            return date_format($date, 'Y-m-d');
        }

    }

    /**
     * How many months until this asset hits EOL.
     * @return null
     */
    public function months_until_eol()
    {

        $today = date("Y-m-d");
        $d1    = new DateTime($today);
        $d2    = new DateTime($this->eol_date());

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
        if ($this->model->assigned) {
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
    public function fullStatusText() {
        // Make sure the status is valid
        if ($this->assetstatus) {

            // If the status is assigned to someone or something...
            if ($this->model->assigned) {

                // If it's assigned and not set to the default "ready to deploy" status
                if ($this->assetstatus->name != trans('general.ready_to_deploy')) {
                    return trans('general.deployed'). ' (' . $this->model->assetstatus->name.')';
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
     * Date the warantee expires.
     * @return false|string
     */
    public function warranty_expires()
    {
        if (($this->purchase_date) && ($this->warranty_months)) {
            $date = date_create($this->purchase_date);
            date_add($date, date_interval_create_from_date_string($this->warranty_months . ' months'));
            return date_format($date, 'Y-m-d');
        }

        return false;
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
        return '<x-icon type="reports" class="text-orange" />';
    }
}
