@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.components') }}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>
            <x-table.components :route="route('api.components.index')" />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'components-export', 'search' => true, 'showFooter' => true, 'columns' => \App\Presenters\ComponentPresenter::dataTableLayout()])



@stop
