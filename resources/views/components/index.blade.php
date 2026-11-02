@extends('layouts/default')

@section('title0')
    @if (request('status') === 'Archived')
        {{ trans('general.archived') }}
    @elseif (request('status') === 'Deleted')
        {{ ucfirst(trans('general.deleted')) }}
    @endif
    {{ trans('general.components') }}
@stop

{{-- Page title --}}
@section('title')
    @yield('title0')  @parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="components" sr_only_title>
            <x-table.components
                name="components"
                :route="route('api.components.index', ['status' => e(request('status'))])"
                :presenter="\App\Presenters\ComponentPresenter::dataTableLayout(request('status') === 'Deleted')"
            />
        </x-box>
    </x-container>
@can('update', \App\Models\Component::class)
    <x-modals.adjust-quantity />
@endcan
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'components-export', 'search' => true, 'showFooter' => true, 'columns' => \App\Presenters\ComponentPresenter::dataTableLayout()])

<x-table.advanced-search
    tableId="componentsListingTable"
    :model="\App\Models\Component::class"
/>
@stop
