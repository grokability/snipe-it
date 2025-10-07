@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.depreciation') }}: {{ $depreciation->name }} ({{ $depreciation->months }} {{ trans('general.months') }})

    @parent
@stop

@section('header_right')
    <div class="btn-group pull-right">
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="{{ route('depreciations.edit', ['depreciation' => $depreciation->id]) }}">{{ trans('general.update') }}</a></li>
            <li><a href="{{ route('depreciations.create') }}">{{ trans('general.create') }}</a></li>
        </ul>
    </div>
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">


            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#assets" data-toggle="tab">
                            {{ trans('general.assets') }}

                            {!! ($depreciation->assets()->AssetsForShow()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($depreciation->assets()->AssetsForShow()->count()).'</span>' : '' !!}
                        </a>
                    </li>
                    <li>
                        <a href="#licenses" data-toggle="tab">
                            {{ trans('general.licenses') }}

                            {!! ($depreciation->licenses_count > 0 ) ? '<span class="badge badge-secondary">'.number_format($depreciation->licenses_count).'</span>' : '' !!}
                        </a>
                    </li>
                    <li>
                        <a href="#models" data-toggle="tab">
                            {{ trans('general.asset_models') }}

                            {!! ($depreciation->models_count > 0 ) ? '<span class="badge badge-secondary">'.number_format($depreciation->models_count).'</span>' : '' !!}
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <div class="tab-pane active" id="assets">

                        @include('partials.asset-bulk-actions', [
                                'id_divname' => 'assetsBulkEditToolbar',
                                'id_formname' => 'assetsBulkForm',
                                'id_button' => 'assetEditButton'
                                ])

                        <table
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                data-show-columns-search="true"
                                data-cookie-id-table="depreciationsAssetTable"
                                data-id-table="depreciationsAssetTable"
                                id="depreciationsAssetTable"
                                data-side-pagination="server"
                                data-sort-order="asc"
                                data-sort-name="name"
                                data-toolbar="#assetsBulkEditToolbar"
                                data-bulk-button-id="#assetEditButton"
                                data-bulk-form-id="#assetsBulkForm"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.assets.index',['depreciation_id'=> $depreciation->id]) }}"
                                data-export-options='{
                        "fileName": "export-depreciations-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                        </table>

                    </div> <!-- end tab-pane -->

                    <!-- tab-pane -->
                    <div class="tab-pane" id="licenses">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="table-responsive">

                                    <table
                                            data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
                                            data-cookie-id-table="depreciationsLicenseTable"
                                            data-id-table="depreciationsLicenseTable"
                                            id="depreciationsLicenseTable"
                                            data-side-pagination="server"
                                            data-sort-order="asc"
                                            data-sort-name="name"
                                            class="table table-striped snipe-table"
                                            data-url="{{ route('api.licenses.index',['depreciation_id'=> $depreciation->id]) }}"
                                            data-export-options='{
                        "fileName": "export-depreciations-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                                    </table>

                                </div>

                            </div>

                        </div> <!--/.row-->
                    </div> <!-- /.tab-pane -->

                    <!-- tab-pane -->
                    <div class="tab-pane" id="models">

                        <div class="row">
                            <form method="POST" action="{{ route('models.bulkedit.index') }}" accept-charset="UTF-8" class="form-inline" id="bulkForm">
                            @csrf
                            <div class="col-md-12">
                                <div id="toolbar">
                                    <label for="bulk_actions" class="sr-only">{{ trans('general.bulk_actions') }}</label>
                                    <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="width: 300px;">
                                        <option value="edit">{{ trans('general.bulk_edit') }}</option>
                                        <option value="delete">{{ trans('general.bulk_delete') }}</option>
                                    </select>
                                    <button class="btn btn-primary" id="AssetModelsBulkEditButton" disabled>{{ trans('button.go') }}</button>
                                </div>

                                <div class="table-responsive">
                                    <table
                                            data-columns="{{ \App\Presenters\AssetModelPresenter::dataTableLayout() }}"
                                            data-cookie-id-table="depreciationsModelsTable"
                                            data-id-table="depreciationsModelsTable"
                                            id="depreciationsModelsTable"
                                            data-toolbar="#toolbar"
                                            data-side-pagination="server"
                                            data-sort-order="asc"
                                            data-sort-name="name"
                                            data-bulk-button-id="#AssetModelsBulkEditButton"
                                            data-bulk-form-id="#bulkForm"
                                            class="table table-striped snipe-table"
                                            data-url="{{ route('api.models.index',['depreciation_id'=> $depreciation->id]) }}"
                                            data-export-options='{
                        "fileName": "export-depreciations-bymodel-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                                    </table>


                                </div>

                            </div>
                            </form>

                        </div> <!--/.row-->
                    </div> <!-- /.tab-pane -->

                </div> <!-- /.tab-content -->



            </div> <!-- /.tab-content -->
            </div> <!-- nav-tabs-custom -->


        </div>

    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
