@props([
    'item' => null,
    'route' => null,
    'wide' => false,
    'count' => 0,
    'tooltip' => trans('admin/users/general.print_assigned'),
])

@can('view', $item)
    @if ($count > 0)
        <a href="{{ $route }}" class="btn btn-sm btn-primary hidden-print" data-tooltip="true" title="{{ $tooltip }}">
             <x-icon type="print"/>
             @if ($wide=='true')
                {{ trans('general.print') }}
            @endif
        </a>
    @endif
@endcan
