@extends('layouts/default')

@section('title0')
    @if (request('status') === 'Archived')
        {{ trans('general.archived') }}
    @elseif (request('status') === 'Deleted')
        {{ ucfirst(trans('general.deleted')) }}
    @endif
    {{ trans('general.accessories') }}
@stop

{{-- Page title --}}
@section('title')
    @yield('title0')  @parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="accessories" sr_only_title>
            <x-table.accessories
                name="accessories"
                :route="route('api.accessories.index', ['status' => e(request('status'))])"
                fixed_right_number="3"
                :presenter="\App\Presenters\AccessoryPresenter::dataTableLayout(match (request('status')) {
                    'Deleted' => ['archived'],
                    'Archived' => ['deleted_at'],
                    default => ['archived', 'deleted_at'],
                })"
            />
        </x-box>
        <x-shiftclick/>
    </x-container>




    @can('update', \App\Models\Accessory::class)
    <x-modals.adjust-quantity />
@endcan
@stop


@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <x-table.advanced-search
        tableId="accessoriesListingTable"
        :model="\App\Models\Accessory::class"
    />
@stop
