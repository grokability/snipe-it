<?php

namespace App\Presenters;

/**
 * Class SupplierPresenter
 */
class SupplierPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
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
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'suppliersLinkFormatter',
            ], [
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
                'field' => 'assets_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.assets'),
                'titleTooltip' => trans('general.assets'),
                'visible' => true,
                'class' => 'css-barcode text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ],  [
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
            ],
            [
                'field' => 'licenses_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.licenses'),
                'titleTooltip' => trans('general.licenses'),
                'visible' => true,
                'class' => 'css-license text-right text-padding-number-cell',
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
            ], [
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
            ], [
                'field' => 'url',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.url'),
                'visible' => true,
                'formatter' => 'externalLinkFormatter',
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
                'field' => 'contact',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.contact'),
                'visible' => false,
            ], [
                'field' => 'email',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.email'),
                'visible' => false,
                'formatter' => 'emailFormatter',
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
            ],  [
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
                'formatter' => 'suppliersActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this supplier name
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\Supplier', $this])) {
            return '<a href="'.route('suppliers.show', $this->id).'">'.e($this->display_name).'</a>';
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
        if (auth()->user()->can('view', ['\App\Models\Supplier', $this])) {
            return '<a href="'.route('suppliers.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }
    }

    public function glyph()
    {
        return '<x-icon type="suppliers" />';
    }

    public function fullName()
    {
        return $this->name;
    }

    public function formattedNameLink()
    {

        if (auth()->user()->can('view', ['\App\Models\Supplier', $this])) {
            return ($this->tag_color ? "<i class='fa-solid fa-square fa-fw' style='color: ".e($this->tag_color)."' aria-hidden='true'></i> " : '').'<a href="'.route('suppliers.show', e($this->id)).'">'.e($this->name).'</a>';
        }

        return ($this->tag_color ? "<i class='fa-solid fa-square fa-fw' style='color: ".e($this->tag_color)."' aria-hidden='true'></i> " : '').e($this->name);
    }
}
