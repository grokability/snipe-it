@props([
    'item' => null,
    'route' => null,
    'wide' => false,
    'tooltip' => trans('general.create'),
])

@can('create', $item)
    <!-- start add button component -->
    <a href="{{ $route }}" class="btn btn-sm btn-info hidden-print{{ ($wide=='true') ?? ' btn-block btn-social'  }}" data-tooltip="true"  data-placement="top" data-title="{{ $tooltip }}">
        <x-icon type="create" class="fa-fw"  />
        @if ($wide=='true')
            {{ trans('general.create') }}
        @endif
        <!-- end add button component -->
    </a>
@endcan