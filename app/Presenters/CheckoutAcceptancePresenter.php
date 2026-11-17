<?php

namespace App\Presenters;

class CheckoutAcceptancePresenter extends Presenter
{
    /**
     * Table-layout methods on presenters are long by nature. Sibling
     * presenters (CheckoutRequestPresenter, AssetPresenter) run the
     * same shape at 200+ lines without complaint. Splitting into
     * micro-helpers hides the column list behind indirection without
     * reducing total LoC in a meaningful way, so the length warning
     * is suppressed here to match the rest of the presenter family.
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function dataTableLayout(): string
    {
        $showingDeleted = request('deleted') === 'deleted';

        $columns = [];

        // Pending-only view carries the bulk-select checkbox. Trashed
        // rows have no bulk-eligible action so the column drops out.
        if (! $showingDeleted) {
            $columns[] = [
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ];
        }

        $columns[] = [
            'field' => 'created_at',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => true,
            'title' => trans('general.date'),
            'visible' => true,
            'formatter' => 'dateDisplayFormatter',
        ];

        $columns[] = [
            'field' => 'company.name',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            'switchable' => true,
            'visible' => true,
            'title' => trans('general.company'),
        ];

        $columns[] = [
            'field' => 'category.name',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            'switchable' => true,
            'visible' => true,
            'title' => trans('general.category'),
        ];

        $columns[] = [
            'field' => 'model.name',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            'switchable' => true,
            'visible' => true,
            'title' => trans('admin/hardware/form.model'),
        ];

        // Item / target / checked_out_by all render through the shared
        // polymorphicItemFormatter, which reads value.type to pick the
        // per-morph-type icon + destination path. Keeps per-type
        // rendering out of this file.
        $columns[] = [
            'field' => 'item',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            // item/target/checked_out_by are nested objects, so bs-table's
            // default sorter can't compare them meaningfully. sortName
            // points it at the string name field inside the object so
            // header clicks sort alphabetically by display name.
            'sortName' => 'item.name',
            'title' => trans('general.name'),
            'visible' => true,
            'formatter' => 'polymorphicItemFormatter',
        ];

        $columns[] = [
            'field' => 'asset_tag',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            'switchable' => true,
            'visible' => true,
            'title' => trans('admin/hardware/table.asset_tag'),
        ];

        $columns[] = [
            'field' => 'target',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            'sortName' => 'target.name',
            'title' => trans('admin/hardware/table.checkoutto'),
            'visible' => true,
            'formatter' => 'polymorphicItemFormatter',
        ];

        $columns[] = [
            'field' => 'checked_out_by',
            'scope' => 'col',
            'searchable' => true,
            'sortable' => true,
            'sortName' => 'checked_out_by.name',
            'switchable' => true,
            'visible' => true,
            'title' => trans('general.created_by'),
            'formatter' => 'polymorphicItemFormatter',
        ];

        // Pending view ends with the Actions column (reminder / delete).
        // Deleted view swaps it for the Deleted At timestamp since
        // nothing's actionable on a trashed row.
        $columns[] = $showingDeleted ? [
            'field' => 'deleted_at',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => true,
            'switchable' => true,
            'visible' => true,
            'title' => trans('general.deleted_at'),
            'formatter' => 'dateDisplayFormatter',
        ] : [
            'field' => 'actions',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('button.actions'),
            'visible' => true,
            'formatter' => 'acceptanceActionsFormatter',
            'printIgnore' => true,
            'class' => 'hidden-print text-nowrap',
        ];

        return json_encode($columns);
    }
}
