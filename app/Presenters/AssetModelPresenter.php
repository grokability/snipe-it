<?php

namespace App\Presenters;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;

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
            ], [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'visible' => true,
                'title' => trans('general.name'),
                'formatter' => 'modelsLinkFormatter',
            ],
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'manufacturer',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ],
            [
                'field' => 'model_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/models/table.modelnumber'),
                'visible' => true,
            ],
            [
                'field' => 'min_amt',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('mail.min_QTY'),
                'visible' => true,
                'formatter' => 'minAmtFormatter',
                'class' => 'text-right text-padding-number-cell',
            ],
            [
                'field' => 'default_purchase_cost',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.default_purchase_cost'),
                'visible' => false,
                'class' => 'text-right text-padding-number-cell',
            ],
            [
                'field' => 'default_currency',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/settings/general.default_currency'),
                'visible' => false,
                'class' => 'text-right text-padding-number-cell',
            ],
            [
                'field' => 'assets_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/models/table.numassets'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'assets_assigned_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.assigned'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'remaining',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.remaining'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'percent_remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => '% '.trans('general.remaining'),
                'visible' => true,
                'formatter' => 'progressBarFormatter',
            ],
            [
                'field' => 'assets_archived_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.archived'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'depreciation',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.depreciation'),
                'visible' => false,
                'formatter' => 'depreciationsLinkObjFormatter',
            ],
            [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.category'),
                'visible' => true,
                'formatter' => 'categoriesLinkObjFormatter',
            ],
            [
                'field' => 'eol',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.eol_rate'),
                'visible' => true,
            ],
            [
                'field' => 'fieldset',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/models/general.fieldset'),
                'visible' => true,
                'formatter' => 'fieldsetsLinkObjFormatter',
            ],
            [
                'field' => 'requestable',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.requestable'),
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'require_serial',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.require_serial'),
                'formatter' => 'trueFalseFormatter',
            ],
            [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => false,
                'formatter' => 'notesFormatter',
            ],
            [
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

        ];

        $layout[] = [
            'field' => 'actions',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'modelsActionsFormatter',
            'printIgnore' => true,
            'class' => 'hidden-print',
        ];

        return json_encode($layout);
    }

    /**
     * Formatted note for this model
     *
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
     *
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
     *
     * @return string
     */
    public function nameUrl()
    {
        return '<a href="'.route('models.show', $this->id).'">'.e($this->name).'</a>';
    }

    /**
     * Generate img tag to this models image.
     *
     * @return string
     */
    public function imageUrl()
    {
        if (! empty($this->image)) {
            $url = Storage::disk('public')->url(FileStorage::Models->publicPath().e($this->image));

            return '<img src="'.$url.'" alt="'.e($this->name).'" height="50" width="50">';
        }

        return '';
    }

    /**
     * Generate img tag to this models image.
     *
     * @return string
     */
    public function imageSrc()
    {
        if (! empty($this->image)) {
            return Storage::disk('public')->url(FileStorage::Models->publicPath().e($this->image));
        }

        return '';
    }

    /**
     * Url to view this item.
     *
     * @return string
     */
    public function viewUrl()
    {
        return route('models.show', $this->id);
    }

    public function formattedNameLink()
    {

        if (auth()->user()->can('view', ['\App\Models\AssetModel', $this])) {
            return '<a href="'.route('models.show', e($this->id)).'" class="'.(($this->deleted_at != '') ? 'deleted' : '').'">'.e($this->display_name).'</a>';
        }

        return '<span class="'.(($this->deleted_at != '') ? 'deleted' : '').'">'.e($this->display_name).'</span>';
    }

    /**
     * Column layout for the models tab on /account/requestable. Feeds
     * <x-table> via api.assetmodels.requestable. Row shape comes from
     * AssetModelsTransformer with assigned_to_self +
     * available_actions.request/cancel/view populated so the
     * assetmodelRequestable*Formatter JS helpers can render the
     * request/cancel button-swap and link-vs-plain-text decision
     * without a compile-time @can.
     */
    public static function dataTableLayoutRequestable(): string
    {
        return json_encode([
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.image'),
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.asset_model'),
                'formatter' => 'assetmodelRequestableNameFormatter',
            ], [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/accessories/general.remaining'),
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'assetmodelRequestableActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);
    }
}
