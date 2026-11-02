<?php

namespace App\Presenters;

/**
 * Class ManufacturerPresenter
 */
class ManufacturerPresenter extends Presenter
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
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/manufacturers/table.name'),
                'visible' => true,
                'formatter' => 'manufacturersLinkFormatter',
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
                'field' => 'url',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.url'),
                'visible' => true,
                'formatter' => 'externalLinkFormatter',
            ],
            [
                'field' => 'support_url',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.support_url'),
                'visible' => true,
                'formatter' => 'externalLinkFormatter',
            ],

            [
                'field' => 'support_phone',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.support_phone'),
                'visible' => true,
                'formatter' => 'phoneFormatter',
            ],

            [
                'field' => 'support_email',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.support_email'),
                'visible' => true,
                'formatter' => 'emailFormatter',
            ],
            [
                'field' => 'warranty_lookup_url',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/manufacturers/table.warranty_lookup_url'),
                'visible' => false,
                'formatter' => 'externalLinkFormatter',
            ],

            [
                'field' => 'assets_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.assets'),
                'visible' => true,
                'class' => 'css-barcode text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'licenses_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.licenses'),
                'visible' => true,
                'class' => 'css-license text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'consumables_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.consumables'),
                'visible' => true,
                'class' => 'css-consumable text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'accessories_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.accessories'),
                'visible' => true,
                'class' => 'css-accessory text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'components_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.components'),
                'visible' => true,
                'class' => 'css-component text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
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
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'manufacturersActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this manufacturers name
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\Manufacturer', $this])) {
            return '<a href="'.route('manufacturers.show', $this->id).'">'.e($this->display_name).'</a>';
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
        return route('manufacturers.show', $this->id);
    }

    public function formattedNameLink()
    {

        if (auth()->user()->can('view', ['\App\Models\Manufacturer', $this])) {
            return ($this->tag_color ? "<i class='fa-solid fa-fw fa-square' style='color: ".e($this->tag_color)."' aria-hidden='true'></i>" : '').'<a href="'.route('manufacturers.show', e($this->id)).'">'.e($this->display_name).'</a>';
        }

        return ($this->tag_color ? "<i class='fa-solid fa-fw fa-square' style='color: ".e($this->tag_color)."' aria-hidden='true'></i>" : '').e($this->display_name);
    }
}
