@props([
    'item' => null,
    'route' => null,
    'wide' => false,
])

@can('update', $item)
    @if ($item->deleted_at!='')
    <!-- start restore button component -->
    {{-- Pull-right on the form matches the delete button's
         alignment, so swapping delete↔restore between live and
         soft-deleted views keeps the destructive-action slot in
         the same spot in the info-panel. --}}
    <form method="POST" action="{{ $route }}" class="inline pull-right">
    @csrf
        <button class="btn btn-sm btn-warning hidden-print{{ $wide == 'true' ? ' btn-block btn-social' : '' }}" data-tooltip="true" data-placement="top" data-title="{{ trans('general.restore') }}">
        <x-icon type="restore" class="fa-fw" />
            @if ($wide=='true')
                {{ trans('general.restore') }}
            @endif
        </button>
    </form>
    @endif
    <!-- end restore button component -->
@endcan
