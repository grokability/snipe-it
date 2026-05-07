@props([
    'item' => null,
    'route' => null,
    'wide' => false,
])

@can('update', $item)
<!-- start update button component -->
@if ($item->deleted_at=='')
    <a href="{{ ($item->deleted_at == '') ? $route: '#' }}" class="btn btn-sm btn-warning hidden-print{{ $wide == 'true' ? ' btn-block btn-social' : '' }}{{ ($item->deleted_at!='') ? ' disabled' : '' }}" data-tooltip="true" data-placement="top" data-title="{{ trans('general.update') }}">
    <x-icon type="edit" class="fa-fw" />

    @if ($wide=='true')
        {{ trans('general.update') }}
    @endif

</a>
@endif
<!-- end update button component -->
@endcan