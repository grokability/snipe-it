@props([
    'item' => null,
    'wide' => false,
    'route' => null,
])
@if ($item->deleted_at=='')
    <form method="POST" action="{{ $route }}" accept-charset="UTF-8" class="form-inline" target="_blank" id="bulkForm" style="display: inline;">
        @csrf
        <input type="hidden" name="bulk_actions" value="labels"/>
        <input type="hidden" name="ids[{{$item->id}}]" value="{{ $item->id }}"/>
        <button class="btn btn-sm btn-default hidden-print{{ $wide == 'true' ? ' btn-block btn-social' : '' }}" id="bulkEdit" {{ (!$item->model ? ' disabled' : '') }} data-tooltip="true" title="{!! (!$item->model ? ' '.trans('admin/hardware/general.model_invalid') : trans_choice('button.generate_labels', 1)) !!}">
            <x-icon type="assets" class="fa-fw"/>
            @if ($wide=='true')
                {{ trans_choice('button.generate_labels', 1) }}
            @endif

        </button>
    </form>
@endif