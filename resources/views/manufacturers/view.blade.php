@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ $manufacturer->name }}
 {{ trans('general.manufacturer') }}
@parent
@stop

@section('header_right')

  <a href="{{ route('manufacturers.index') }}" class="btn btn-primary text-right" style="margin-right: 10px;">{{ trans('general.back') }}</a>


  <div class="btn-group pull-right">
     <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
     <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <li><a href="{{ route('manufacturers.edit', $manufacturer->id) }}">{{ trans('admin/manufacturers/table.update') }}</a></li>
        <li><a href="{{ route('manufacturers.create') }}">{{ trans('admin/manufacturers/table.create') }}</a></li>
      </ul>
  </div>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="nav-tabs-custom">

      <ul class="nav nav-tabs">
        <li class="active">

          <a href="#assets" data-toggle="tab">
            <span class="hidden-lg hidden-md">
              <i class="fas fa-barcode fa-2x"></i>
            </span>
            <span class="hidden-xs hidden-sm">
                {{ trans('general.assets') }}
                {!! ($manufacturer->assets()->AssetsForShow()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($manufacturer->assets()->AssetsForShow()->count()).'</span>' : '' !!}
            </span>
          </a>

        </li>
        <li>
          <a href="#licenses" data-toggle="tab">
            <span class="hidden-lg hidden-md">
               <x-icon type="licenses" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">
              {{ trans('general.licenses') }}
              {!! ($manufacturer->licenses->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($manufacturer->licenses->count()).'</span>' : '' !!}
            </span>

          </a>
        </li>
        <li>
          <a href="#accessories" data-toggle="tab">

             <span class="hidden-lg hidden-md">
              <x-icon type="accessories" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">
              {{ trans('general.accessories') }}
              {!! ($manufacturer->accessories->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($manufacturer->accessories->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>
        <li>
          <a href="#consumables" data-toggle="tab">

             <span class="hidden-lg hidden-md">
               <x-icon type="consumables" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">
              {{ trans('general.consumables') }}
              {!! ($manufacturer->consumables->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($manufacturer->consumables->count()).'</span>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#components" data-toggle="tab">

             <span class="hidden-lg hidden-md">
               <x-icon type="components" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">
              {{ trans('general.components') }}
              {!! ($manufacturer->components->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($manufacturer->components->count()).'</span>' : '' !!}
            </span>

          </a>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade in active" id="assets">

          @include('partials.asset-bulk-actions')
          <div class="table table-responsive">
          <table
                  data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                  data-show-columns-search="true"
                  data-cookie-id-table="assetsListingTable"
                  data-id-table="assetsListingTable"
                  data-toolbar="#assetsBulkEditToolbar"
                  data-bulk-button-id="#bulkAssetEditButton"
                  data-bulk-form-id="#assetsBulkForm"
                  data-side-pagination="server"
                  data-sort-order="asc"
                  id="assetsListingTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.assets.index', ['manufacturer_id' => $manufacturer->id, 'itemtype' => 'assets']) }}"
                  data-export-options='{
              "fileName": "export-manufacturers-{{ str_slug($manufacturer->name) }}-assets-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>
          </div>

        </div> <!-- /.tab-pane assets -->

        <div class="tab-pane fade" id="licenses">

          <table
                  data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
                  data-cookie-id-table="licensesTable"
                  data-id-table="licensesTable"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-sort-order="asc"
                  id="licensesTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.licenses.index', ['manufacturer_id' => $manufacturer->id]) }}"
                  data-export-options='{
              "fileName": "export-manufacturers-{{ str_slug($manufacturer->name) }}-licenses-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>


        </div><!-- /.tab-pan licenses-->

        <div class="tab-pane fade" id="accessories">

          <table
                  data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                  data-cookie-id-table="accessoriesTable"
                  data-id-table="accessoriesTable"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-sort-order="asc"
                  id="accessoriesTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.accessories.index', ['manufacturer_id' => $manufacturer->id]) }}"
                  data-export-options='{
              "fileName": "export-manufacturers-{{ str_slug($manufacturer->name) }}-accessories-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>


        </div> <!-- /.tab-pan accessories-->

        <div class="tab-pane fade" id="consumables">

          <table
                  data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                  data-cookie-id-table="consumablesTable"
                  data-id-table="consumablesTable"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-sort-order="asc"
                  id="consumablesTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.consumables.index', ['manufacturer_id' => $manufacturer->id]) }}"
                  data-export-options='{
              "fileName": "export-manufacturers-{{ str_slug($manufacturer->name) }}-consumables-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>

        </div> <!-- /.tab-pan consumables-->

        <div class="tab-pane fade" id="components">

          <table
                  data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                  data-cookie-id-table="componentsTable"
                  data-id-table="componentsTable"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-sort-order="asc"
                  id="componentsTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.components.index', ['manufacturer_id' => $manufacturer->id]) }}"
                  data-export-options='{
              "fileName": "export-manufacturers-{{ str_slug($manufacturer->name) }}-components-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>

        </div> <!-- /.tab-pan consumables-->

      </div> <!-- /.tab-content -->
    </div>  <!-- /.nav-tabs-custom -->
  </div><!-- /. col-md-12 -->
</div> <!-- /.row -->
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'manufacturer' . $manufacturer->name . '-export', 'search' => false])

@stop
