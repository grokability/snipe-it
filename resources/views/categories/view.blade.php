@extends('layouts/default')

{{-- Page title --}}
@section('title')

{{ $category->name }}

@parent
@stop

{{-- Page content --}}
@section('content')



    <div class="row">
        <div class="col-md-12">

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#items" data-toggle="tab" title="{{ trans('general.items') }}">
                            @if ($category->category_type=='asset')
                                {{ trans('general.assets') }}
                                @if ($category->showableAssets()->count() > 0)
                                    <span class="badge badge-secondary"> {{ $category->showableAssets()->count() }}</span>
                                @endif
                            @elseif ($category->category_type=='accessory')
                                {{ trans('general.accessories') }}
                            @elseif ($category->category_type=='license')
                                {{ trans('general.licenses') }}
                            @elseif ($category->category_type=='consumable')
                                {{ trans('general.consumables') }}
                            @elseif ($category->category_type=='component')
                                {{ trans('general.components') }}
                            @endif

                        </a>
                    </li>
                    @if ($category->category_type=='asset')
                    <li>
                        <a href="#models" data-toggle="tab" title="{{ trans('general.asset_models') }}">
                            {{ trans('general.asset_models') }}
                            @if ($category->models->count() > 0)
                                <span class="badge badge-secondary"> {{ $category->models->count()}}</span>
                            @endif
                        </a>
                    </li>
                   @endif
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade in active" id="items">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    @if ($category->category_type=='asset')
                                        @include('partials.asset-bulk-actions')
                                    @endif

                                    <table

                                            @if ($category->category_type=='asset')
                                            data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                            data-show-columns-search="true"
                                            data-cookie-id-table="categoryAssetsTable"
                                            id="categoryAssetsTable"
                                            data-buttons="assetButtons"
                                            data-id-table="categoryAssetsTable"
                                            data-toolbar="#assetsBulkEditToolbar"
                                            data-bulk-button-id="#bulkAssetEditButton"
                                            data-bulk-form-id="#assetsBulkForm"
                                            data-export-options='{
                    "fileName": "export-{{ str_slug($category->name) }}-assets-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'
                                            @elseif ($category->category_type=='accessory')
                                            data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                                            data-cookie-id-table="categoryAccessoryTable"
                                            id="categoryAccessoryTable"
                                            data-buttons="accessoryButtons"
                                            data-id-table="categoryAccessoryTable"
                                            data-export-options='{
                      "fileName": "export-{{ str_slug($category->name) }}-accessories-{{ date('Y-m-d') }}",
                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                      }'
                                            @elseif ($category->category_type=='consumable')
                                                data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                                            data-cookie-id-table="categoryConsumableTable"
                                            id="categoryConsumableTable"
                                            data-buttons="consumableButtons"
                                            data-id-table="categoryConsumableTable"
                                            data-export-options='{
                      "fileName": "export-{{ str_slug($category->name) }}-consumables-{{ date('Y-m-d') }}",
                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                      }'
                                            @elseif ($category->category_type=='component')
                                            data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                                            data-cookie-id-table="categoryCompomnentTable"
                                            id="categoryCompomnentTable"
                                            data-buttons="componentButtons"
                                            data-id-table="categoryCompomnentTable"
                                            data-export-options='{
                      "fileName": "export-{{ str_slug($category->name) }}-components-{{ date('Y-m-d') }}",
                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                      }'
                                            @elseif ($category->category_type=='license')
                                            data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
                                            data-cookie-id-table="categoryLicenseTable"
                                            id="categoryLicenseTable"
                                            data-buttons="licenseButtons"
                                            data-id-table="categoryLicenseTable"
                                            data-export-options='{
                      "fileName": "export-{{ str_slug($category->name) }}-licenses-{{ date('Y-m-d') }}",
                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                      }'
                                            @endif
                                            data-show-footer="true"
                                            data-side-pagination="server"
                                            data-sort-order="asc"
                                            class="table table-striped snipe-table"
                                            data-url="{{ route('api.'.$category_type_route.'.index',['category_id'=> $category->id]) }}">

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="models">
                        <div class="row">
                            <div class="col-md-12">

                                @can('update', \App\Models\AssetModel::class)
                                @if ($category->models->count() > 0)
                                    @if ($category->category_type=='asset')
                                        @include('partials.models-bulk-actions')
                                    @endif
                                @endif
                                @endcan

                                    <table
                                            data-columns="{{ \App\Presenters\AssetModelPresenter::dataTableLayout() }}"
                                            data-cookie-id-table="assetModelsTable"
                                            data-id-table="assetModelsTable"
                                            data-show-footer="true"
                                            data-side-pagination="server"
                                            data-toolbar="#modelsBulkEditToolbar"
                                            data-bulk-button-id="#bulkModelsEditButton"
                                            data-bulk-form-id="#modelsBulkForm"
                                            data-sort-order="asc"
                                            id="assetModelsTable"
                                            data-buttons="modelButtons"
                                            class="table table-striped snipe-table"
                                            data-url="{{ route('api.models.index', ['status' => request('status'), 'category_id' => $category->id]) }}"
                                            data-export-options='{
              "fileName": "export-models-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
                                    </table>

                            </div>
                        </div>
                    </div>

                </div> <!-- .tab-content-->
            </div> <!-- .nav-tabs-custom -->
        </div> <!-- .col-md-12> -->
    </div> <!-- .row -->
@stop





@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
