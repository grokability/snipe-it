@props([
    'item' => null,
    'route' => null,
])

@can('update', $item)
    @if ($item->deleted_at!='')
    <!-- start restore button component -->
    <form method="POST" action="{{ $route }}">
    @csrf
        <button class="btn btn-sm btn-block btn-warning btn-social hidden-print">
        <x-icon type="restore" />
        {{ trans('general.restore') }}
        </button>
    </form>
    @endif
    <!-- end restore button component -->
@endcan
