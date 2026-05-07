@props([
    'item' => null,
    'route' => null,
    'wide' => false,
])
@if ($item->deleted_at=='')
    @can('update', $item)
        <!-- start note button component -->
        <a href="#" data-toggle="modal" data-target="#createNoteModal" class="btn btn-sm btn-theme hidden-print{{ $wide == 'true' ? ' btn-block btn-social' : '' }}" data-tooltip="true" data-placement="top" data-title="{{ trans('general.add_note') }}">
            <x-icon type="note" class="fa-fw"/>

            @if ($wide=='true')
                {{ trans('general.add_note') }}
            @endif

        </a>
        <!-- end note button component -->
    @endcan
@endif
