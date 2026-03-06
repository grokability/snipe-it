@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ $statuslabel->name }} {{ trans('general.assets') }}
    @parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-box>
                @include('partials.asset-bulk-actions')

                    <table
                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                        data-cookie-id-table="assetsListingTable"
                        data-id-table="assetsListingTable"
                        data-side-pagination="server"
                        data-sort-order="asc"
                        data-toolbar="#assetsBulkEditToolbar"
                        data-bulk-button-id="#bulkAssetEditButton"
                        data-bulk-form-id="#assetsBulkForm"
                        id="assetsListingTable"
                        data-show-columns-search="true"
                        data-buttons="assetButtons"
                        class="table table-striped snipe-table"
                        data-url="{{route('api.assets.index', ['status_id' => $statuslabel->id]) }}"
                        data-export-options='{
                          "fileName": "export-assets-{{ str_slug($statuslabel->name) }}-assets-{{ date('Y-m-d') }}",
                          "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                          }'>
                    </table>
            </x-box>
        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$statuslabel">

                    <x-slot:buttons>
                        <x-button.edit :item="$statuslabel" :route="route('statuslabels.edit', $statuslabel->id)" />
                        <x-button.delete :item="$statuslabel" />
                    </x-slot:buttons>


                </x-box.info-panel>
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
