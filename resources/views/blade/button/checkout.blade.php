@props([
    'item' => null,
    'permission' => null,
    'route',
    'wide' => false,
])

@can('checkout', $item)
    @if ((method_exists($item, 'numRemaining')) && ($item->numRemaining() > 0))
        <a href="{{ $route  }}" class="btn btn-sm bg-maroon hidden-print" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.checkout') }}">
            <x-icon type="checkout" class="fa-fw" />

            @if ($wide=='true')
                {{ trans('general.checkout') }}
            @endif
        </a>
    @endif

@endcan
