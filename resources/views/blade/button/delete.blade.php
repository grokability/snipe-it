@props([
    'item' => null,
    'route' => null,
    'count' => null,
    'type' => 'item',
    'wide' => false,
])

@can('delete', $item)
    <!-- start delete button component -->
    @if ((method_exists($item, 'isDeletable')) && ($item->deleted_at==''))
        @if (!$item->isDeletable())
            <button class="pull-right btn btn-sm btn-danger hidden-print disabled {{ $wide == 'true' ? ' btn-block btn-social' : '' }}" style="margin-right: 8px;" data-tooltip="true" data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}">
                <x-icon type="delete" class="fa-fw"  />
            </button>
        @else
            <button class="pull-right btn btn-sm btn-danger delete-asset{{ $wide == 'true' ? ' btn-block btn-social' : '' }}" style="margin-right: 8px;" data-toggle="modal" title="{{ ($item->assignedTo) ? trans('general.checkin_and_delete') : trans('general.delete') }}" data-title="{{ ($item->assignedTo) ? trans('general.checkin_and_delete') : trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $item->display_name]) }}" data-target="#dataConfirmModal" data-tooltip="true" data-icon="fa fa-trash" data-placement="top" onClick="return false;">
                <x-icon type="delete" class="fa-fw" />
            </button>
        @endif
    @endif
    <!-- end delete button component -->
@endif

