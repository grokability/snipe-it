@props([
    'item' => null,
    'permission' => null,
    'route',
    'wide' => false,
])

@can('checkin', $item)
    @if ($item->showCheckinButton($item) == 'show-active')
        <a href="{{ $route  }}" class="btn btn-sm bg-purple hidden-print" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.checkin') }}">
            <x-icon type="checkin" class="fa-fw" />
            @if ($wide=='true')
                {{ trans('general.checkin') }}
            @endif
        </a>
    @endif
@endcan
