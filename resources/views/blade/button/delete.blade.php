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
            <button class="btn btn-sm btn-danger hidden-print disabled {{ ($wide=='true') ?? ' btn-block btn-social'  }}" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}">
                <x-icon type="delete" class="fa-fw"  />
            </button>
        @else
            <button class="btn btn-sm btn-danger delete-asset{{ ($wide=='true') ?? ' btn-block btn-social'  }}" data-toggle="modal" title="{{ trans('general.delete_what', ['item'=> trans('general.'.$type)]) }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $item->name]) }}" data-target="#dataConfirmModal" data-tooltip="true" data-icon="fa fa-trash" data-placement="top" data-title="{{ trans('general.delete_what', ['item'=> trans('general.'.$type)]) }}" onClick="return false;">
                <x-icon type="delete" class="fa-fw" />
            </button>
        @endif
    @endif
    <!-- end delete button component -->
@endif

