@props([
    'item' => null,
    'route',
])

@can('checkout', $item)
    @if ((method_exists($item, 'numRemaining')) && ($item->numRemaining() > 0))
        <a href="{{ $route  }}" class="btn btn-sm bg-maroon btn-social btn-block hidden-print">
            <x-icon type="checkout" />
            {{ trans('general.checkout') }}
        </a>
    @endif
@endcan
