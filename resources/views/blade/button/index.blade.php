@props([
    'item' => null,
    'permission' => null,
    'route',
])

@can($permission, $item)
    <a href="{{ $route  }}" {{ $attributes->merge(['class' => 'btn btn-sm hidden-print']) }} data-tooltip="true"  data-placement="top" data-title="{{ trans('general.'.$permission) }}">
        <x-icon type="{{ $permission }}" />
    </a>
@endcan
