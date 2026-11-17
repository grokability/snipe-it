@extends('layouts/default')

@section('title0')
    @if (request('status') === 'Archived')
        {{ trans('general.archived') }}
    @elseif (request('status') === 'Deleted')
        {{ ucfirst(trans('general.deleted')) }}
    @endif
    {{ trans('general.consumables') }}
@stop

{{-- Page title --}}
@section('title')
    @yield('title0')  @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="consumables" sr_only_title>
            {{-- Remaining and % Remaining measure "available to give out,"
                 which has no meaning for archived (retired) or deleted
                 rows. Drop both columns on those two filter views. --}}
            <x-table.consumables
                name="consumables"
                :route="route('api.consumables.index', ['status' => e(request('status'))])"
                :presenter="\App\Presenters\ConsumablePresenter::dataTableLayout(
                    request('status') === 'Deleted',
                    in_array(request('status'), ['Archived', 'Deleted'], true) ? ['remaining', 'percent_remaining'] : [],
                )"
            />
        </x-box>
        <x-shiftclick/>
    </x-container>
@can('update', \App\Models\Consumable::class)
    <x-modals.adjust-quantity />
@endcan
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'consumables-export', 'search' => true,'showFooter' => true, 'columns' => \App\Presenters\ConsumablePresenter::dataTableLayout()])

<x-table.advanced-search
    tableId="consumablesListingTable"
    :model="\App\Models\Consumable::class"
/>
@stop
