@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ $statuslabel->name }} {{ trans('general.assets') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
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
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- ./box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'assets-export',
        'search' => true,
        'columns' => \App\Presenters\AssetPresenter::dataTableLayout()
    ])

@stop
