@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.consumables') }}
@parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>
            <x-table.consumables :route="route('api.consumables.index')" />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'consumables-export', 'search' => true,'showFooter' => true, 'columns' => \App\Presenters\ConsumablePresenter::dataTableLayout()])
@stop
