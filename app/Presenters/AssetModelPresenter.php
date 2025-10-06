<?php

namespace App\Presenters;

use App\Helpers\Helper;

/**
 * Class AssetModelPresenter
 */
class AssetModelPresenter extends Presenter
{
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'checkbox',
                'checkbox' => true,
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
            ],
            [
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
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'visible' => true,
                'title' => trans('general.name'),
                'formatter' => 'modelsLinkFormatter',
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
                'field' => 'manufacturer',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ],
            [
                'field' => 'model_number',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/models/table.modelnumber'),
                'visible' => true,
            ],
            [
                'field' => 'min_amt',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('mail.min_QTY'),
                'visible' => true,
                'formatter' => 'minAmtFormatter',
                'class' => 'text-right text-padding-number-cell',
            ],

            [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/models/table.numassets'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'assets_assigned_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.assigned'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'remaining',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.remaining'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'assets_archived_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.archived'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'depreciation',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.depreciation'),
                'visible' => false,
                'formatter' => 'depreciationsLinkObjFormatter',
            ],
            [
                'field' => 'category',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.category'),
                'visible' => true,
                'formatter' => 'categoriesLinkObjFormatter',
            ],
            [
                'field' => 'eol',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.eol_rate'),
                'visible' => true,
            ],
            [
                'field' => 'fieldset',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/models/general.fieldset'),
                'visible' => true,
                'formatter' => 'fieldsetsLinkObjFormatter',
            ],
            [
                'field' => 'requestable',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.requestable'),
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'require_serial',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.require_serial'),
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => false,
                'formatter' => 'notesFormatter',
            ],
            [
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
            ],

        ];

        $layout[] = [
            'field' => 'actions',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'modelsActionsFormatter',
            'printIgnore' => true,
        ];

        return json_encode($layout);
    }

    /**
     * Formatted note for this model
     * @return string
     */
    public function note()
    {
        if ($this->model->note) {
            return Helper::parseEscapedMarkedown($this->model->note);
        }
    }

    public function eolText()
    {
        if ($this->eol) {
            return $this->eol.' '.trans('general.months');
        }

        return '';
    }

    /**
     * Pretty name for this model
     * @return string
     */
    public function modelName()
    {
        $name = '';
        if ($this->model->manufacturer) {
            $name .= $this->model->manufacturer->name.' ';
        }
        $name .= $this->name;

        if ($this->model_number) {
            $name .= ' (#'.$this->model_number.')';
        }

        return $name;
    }

    /**
     * Standard url for use to view page.
     * @return string
     */
    public function nameUrl()
    {
        return  (string) link_to_route('models.show', $this->name, $this->id);
    }

    /**
     * Generate img tag to this models image.
     * @return string
     */
    public function imageUrl()
    {
        if (! empty($this->image)) {
            return '<img src="'.config('app.url').'/uploads/models/'.$this->image.'" alt="'.$this->name.'" height="50" width="50">';
        }

        return '';
    }

    /**
     * Generate img tag to this models image.
     * @return string
     */
    public function imageSrc()
    {
        if (! empty($this->image)) {
            return config('app.url').'/uploads/models/'.$this->image;
        }

        return '';
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('models.show', $this->id);
    }
}
