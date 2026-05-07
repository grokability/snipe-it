@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ $statuslabel->name }} {{ trans('general.assets') }}
    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-box name="assets">
                <x-table.assets name="assets" :table_header="trans('general.assets')" :route="route('api.assets.index', ['status_id' => $statuslabel->id])"/>
            </x-box>
        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$statuslabel">

                    <x-slot:buttons>
                        <x-button.edit :item="$statuslabel" :route="route('statuslabels.edit', $statuslabel->id)" />
                        <x-button.delete :item="$statuslabel" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'assets-export',
        'search' => true,
        'columns' => \App\Presenters\AssetPresenter::dataTableLayout()
    ])

@stop
