@props([
    'item' => null,
    'route' => null,
    'wide' => false,
])

@can('create', $item)
    <!-- start clone button component -->
    <a href="{{ $route }}" class="btn btn-sm btn-info hidden-print{{ ($wide=='true') ?? ' btn-block btn-social'  }}" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.clone') }}">
    <x-icon type="clone" class="fa-fw"  />
        @if ($wide=='true')
            {{ trans('general.clone') }}
        @endif
    <!-- end clone button component -->
</a>
@endcan