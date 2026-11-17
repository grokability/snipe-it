<?php

namespace App\Presenters;

use App\Models\CustomField;

/**
 * bs-table column layout for the admin /requests view.
 * Pairs with GET /api/v1/requests + CheckoutRequestsTransformer, so
 * the internal admin UI eats the same API surface external integrators
 * consume for #15541.
 */
class CheckoutRequestPresenter extends Presenter
{
    /**
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                // Selection column for the bulk-cancel action. Rows
                // gate on available_actions.bulk_selectable.cancel via
                // the standard checkboxEnabledFormatter, so a request
                // that's already been canceled between "load table"
                // and "click Go" simply cannot be selected.
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ], [
                'field' => 'requestable.image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'visible' => true,
                'title' => trans('general.image'),
                'formatter' => 'requestableImageFormatter',
            ], [
                'field' => 'requestable.name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'requestableNameFormatter',
            ], [
                'field' => 'requestable.location.name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.location'),
                'visible' => true,
            ], [
                // Category is resolved polymorphically by the
                // transformer (direct on most types, through model
                // on Asset). Full object (not .name) so the
                // formatter can render the tag_color icon. Off-by-
                // default to keep the row narrow for admins that
                // don't care - the switchable column picker turns
                // it on when they do.
                'field' => 'requestable.category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'visible' => false,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                // Company is direct on every requestable type except
                // AssetModel (models are shared across companies), so
                // model rows render empty here. Off-by-default like
                // category - the FMCS crowd toggles it on via the
                // switchable column picker.
                'field' => 'requestable.company.name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'visible' => false,
                'title' => trans('general.company'),
            ], [
                'field' => 'requestable.expected_checkin',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/hardware/form.expected_checkin'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                // Qty the requester asked for. Emitted on every row
                // (defaults to 1) since checkoutable requests carry
                // a per-row quantity even for the not-strictly-qty-
                // tracked types. Header reads "Requested" to
                // disambiguate from the neighbouring "Remaining"
                // (on-hand) column.
                'field' => 'quantity',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => true,
                'title' => trans('admin/hardware/general.requested'),
                'class' => 'text-right text-padding-number-cell',
            ], [
                // On-hand available count for qty-tracked types
                // (accessory / consumable / component). Null for
                // asset / model / license rows; the default
                // formatter renders that as empty which is the right
                // read ("N/A here"). Sort is handled in-PHP inside
                // the controller because numRemaining() is a
                // computed method that differs per polymorphic type,
                // so a DB orderBy isn't feasible - the whitelist
                // entry in Api\CheckoutRequest::index maps this key
                // to the polymorphic method call.
                'field' => 'requestable.remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => true,
                'title' => trans('admin/components/general.remaining'),
                'class' => 'text-right text-padding-number-cell',
            ], [
                // Reservation window start (nullable). Requesters
                // that just want "whenever" leave both blank and the
                // column renders empty via dateDisplayFormatter.
                'field' => 'start_date',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => true,
                'title' => trans('general.start_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'end_date',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => true,
                'title' => trans('general.end_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                // Requester's free-text notes. Hidden by default so
                // the table stays scannable; admins toggle it on via
                // the switchable column picker when they need the
                // per-row context.
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ], [
                'field' => 'user',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/hardware/table.requesting_user'),
                'visible' => true,
                'formatter' => 'requesterFormatter',
            ], [
                'field' => 'requested_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/hardware/table.requested_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('button.actions'),
                'visible' => true,
                'formatter' => 'checkoutRequestActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        // Custom fields flagged for the requestable list, appended
        // as switchable/off-by-default columns so admins evaluating
        // a request can toggle the per-instance context they care
        // about (CPU, warranty, whatever the model's fieldset
        // carries) without cluttering the default view. Same
        // fieldset-must-be-attached-to-a-model guard the standard
        // asset layout uses. Only asset requests have per-instance
        // values so non-asset rows render blank in these columns;
        // the transformer emits an empty object for those rows.
        $fields = CustomField::assignedToInUseFieldsets()->where('field_encrypted', 0)
            ->where('show_in_requestable_list', 1)
            ->get();

        foreach ($fields as $field) {
            $layout[] = [
                'field' => 'requestable.custom_fields.'.$field->db_column,
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'visible' => false,
                'title' => e($field->name),
            ];
        }

        return json_encode($layout);
    }
}
