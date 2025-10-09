@extends('layouts/default')

{{-- Page title --}}
@section('title')

  {{ trans('admin/suppliers/table.view') }} -
  {{ $supplier->name }}

  @parent
@stop

@section('header_right')
    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-default pull-right">
        {{ trans('admin/suppliers/table.update') }}</a>

    <a href="{{ route('suppliers.index') }}" class="btn btn-primary text-right" style="margin-right: 10px;">{{ trans('general.back') }}</a>

@stop


{{-- Page content --}}
@section('content')

  <div class="row">
    <div class="col-md-9">

      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs hidden-print">
          
          <li class="active">
            <a href="#assets" data-toggle="tab">

                <span class="hidden-lg hidden-md">
                    <x-icon type="assets" class="fa-2x" />
                </span>
                <span class="hidden-xs hidden-sm">
                    {{ trans('general.assets') }}
                    {!! ($supplier->assets()->AssetsForShow()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($supplier->assets()->AssetsForShow()->count()).'</span>' : '' !!}
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
                          {!! ($supplier->accessories->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($supplier->accessories->count()).'</span>' : '' !!}
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
                          {!! ($supplier->licenses->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($supplier->licenses->count()).'</span>' : '' !!}
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
                        {!! ($supplier->components->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($supplier->components->count()).'</span>' : '' !!}
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
                        {!! ($supplier->consumables->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($supplier->consumables->count()).'</span>' : '' !!}
                    </span>
                </a>
            </li>

          <li>
            <a href="#maintenances" data-toggle="tab">
                    <span class="hidden-lg hidden-md">
                        <x-icon type="maintenances" class="fa-2x" />
                    </span>
              <span class="hidden-xs hidden-sm">
                        {{ trans('admin/maintenances/general.maintenances') }}
                        {!! ($supplier->maintenances->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($supplier->maintenances->count()).'</span>' : '' !!}
                    </span>
            </a>
          </li>
        </ul>


        <div class="tab-content">


          <div class="tab-pane active" id="assets">
            <h2 class="box-title">{{ trans('general.assets') }}</h2>

            <div class="table table-responsive">
              @include('partials.asset-bulk-actions')
              <table
                      data-cookie-id-table="suppliersAssetsTable"
                      data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                      data-show-columns-search="true"
                      data-id-table="suppliersAssetsTable"
                      data-show-footer="true"
                      data-side-pagination="server"
                      data-sort-order="asc"
                      data-toolbar="#assetsBulkEditToolbar"
                      data-bulk-button-id="#bulkAssetEditButton"
                      data-bulk-form-id="#assetsBulkForm"
                      id="suppliersAssetsTable"
                      class="table table-striped snipe-table"
                      data-url="{{route('api.assets.index', ['supplier_id' => $supplier->id]) }}"
                      data-export-options='{
                              "fileName": "export-suppliers-{{ str_slug($supplier->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
              </table>

            </div><!-- /.table-responsive -->
          </div><!-- /.tab-pane -->



          <div class="tab-pane" id="accessories">
            <h2 class="box-title">{{ trans('general.accessories') }}</h2>
            <div class="table table-responsive">
              <table
                      data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                      data-cookie-id-table="accessoriesListingTable"
                      data-id-table="accessoriesListingTable"
                      data-side-pagination="server"
                      data-sort-order="asc"
                      id="accessoriesListingTable"
                      class="table table-striped snipe-table"
                      data-url="{{route('api.accessories.index', ['supplier_id' => $supplier->id]) }}"
                      data-export-options='{
                              "fileName": "export-suppliers-{{ str_slug($supplier->name) }}-accessories-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
              </table>
            </div><!-- /.table-responsive -->
          </div><!-- /.tab-pane -->


          <div class="tab-pane" id="licenses">
            <h2 class="box-title">{{ trans('general.licenses') }}</h2>

            <div class="table table-responsive">
              <table
                      data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
                      data-cookie-id-table="licensesListingTable"
                      data-id-table="licensesListingTable"
                      data-side-pagination="server"
                      data-sort-order="asc"
                      id="licensesListingTable"
                      class="table table-striped snipe-table"
                      data-url="{{route('api.licenses.index', ['supplier_id' => $supplier->id]) }}"
                      data-export-options='{
                              "fileName": "export-suppliers-{{ str_slug($supplier->name) }}-licenses-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
              </table>

            </div><!-- /.table-responsive -->
          </div><!-- /.tab-pane -->

            <div class="tab-pane" id="components">
                <h2 class="box-title">{{ trans('general.components') }}</h2>
                <div class="table table-responsive">
                    <table
                            data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                            data-cookie-id-table="componentsListingTable"
                            data-id-table="componentsListingTable"
                            data-side-pagination="server"
                            data-sort-order="asc"
                            id="accessoriesListingTable"
                            class="table table-striped snipe-table"
                            data-url="{{route('api.components.index', ['supplier_id' => $supplier->id]) }}"
                            data-export-options='{
                              "fileName": "export-suppliers-{{ str_slug($supplier->name) }}-components-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                    </table>
                </div><!-- /.table-responsive -->
            </div><!-- /.tab-pane -->

            <div class="tab-pane" id="consumables">
            <h2 class="box-title">{{ trans('general.consumables') }}</h2>
            <div class="table table-responsive">
                <table
                        data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                        data-cookie-id-table="consumablesListingTable"
                        data-id-table="consumablesListingTable"
                        data-side-pagination="server"
                        data-sort-order="asc"
                        id="accessoriesListingTable"
                        class="table table-striped snipe-table"
                        data-url="{{route('api.consumables.index', ['supplier_id' => $supplier->id]) }}"
                        data-export-options='{
                              "fileName": "export-suppliers-{{ str_slug($supplier->name) }}-consumables-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                </table>
            </div><!-- /.table-responsive -->
        </div><!-- /.tab-pane -->


          <div class="tab-pane" id="maintenances">
            <h2 class="box-title">{{ trans('admin/maintenances/general.maintenances') }}</h2>
            <div class="table table-responsive">

              <table
                      data-columns="{{ \App\Presenters\MaintenancesPresenter::dataTableLayout() }}"
                      data-cookie-id-table="maintenancesTable"
                      data-id-table="maintenancesTable"
                      data-side-pagination="server"
                      data-sort-order="asc"
                      id="maintenancesTable"
                      data-buttons="maintenanceButtons"
                      class="table table-striped snipe-table"
                      data-url="{{ route('api.maintenances.index', ['supplier_id' => $supplier->id])}}"
                      data-export-options='{
                              "fileName": "export-suppliers-{{ str_slug($supplier->name) }}-maintenances-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

              </table>
            </div><!-- /.table-responsive -->
          </div><!-- /.tab-pane -->

        </div><!--/.col-md-9-->
      </div><!--/.col-md-9-->
    </div><!--/.col-md-9-->

      <!-- side address column -->
      <div class="col-md-3">


      @if (($supplier->address!='') && ($supplier->state!='') && ($supplier->country!='') && (config('services.google.maps_api_key')))
              <div class="col-md-12 text-center" style="padding-bottom: 20px;">
                  <img src="https://maps.googleapis.com/maps/api/staticmap?markers={{ urlencode($supplier->address.','.$supplier->city.' '.$supplier->state.' '.$supplier->country.' '.$supplier->zip) }}&size=500x300&maptype=roadmap&key={{ config('services.google.maps_api_key') }}" class="img-responsive img-thumbnail" alt="Map">
              </div>
          @endif


          <ul class="list-unstyled" style="line-height: 25px; padding-bottom: 20px; padding-top: 20px;">
              @if ($supplier->contact!='')
                  <li><x-icon type="user" /> {{ $supplier->contact }}</li>
              @endif
              @if ($supplier->phone!='')
                  <li><i class="fas fa-phone"></i>
                      <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                  </li>
              @endif
              @if ($supplier->fax!='')
                  <li><i class="fas fa-print"></i> {{ $supplier->fax }}</li>
              @endif

              @if ($supplier->email!='')
                  <li>
                      <i class="far fa-envelope"></i>
                      <a href="mailto:{{ $supplier->email }}">
                          {{ $supplier->email }}
                      </a>
                  </li>
              @endif

              @if ($supplier->url!='')
                  <li>
                      <i class="fas fa-globe-americas"></i>
                      <a href="{{ $supplier->url }}" target="_new">{{ $supplier->url }}</a>
                  </li>
              @endif

              @if ($supplier->address!='')
                  <li><br>
                      {{ $supplier->address }}

                      @if ($supplier->address2)
                          <br>
                          {{ $supplier->address2 }}
                      @endif
                      @if (($supplier->city) || ($supplier->state))
                          <br>
                          {{ $supplier->city }} {{ strtoupper($supplier->state) }} {{ $supplier->zip }} {{ strtoupper($supplier->country) }}
                      @endif
                  </li>
              @endif

              @if ($supplier->notes!='')
                  <li><i class="fa fa-comment"></i> {!! nl2br(Helper::parseEscapedMarkedownInline($supplier->notes)) !!}</li>
              @endif

          </ul>
          @if ($supplier->image!='')
              <div class="col-md-12 text-center" style="padding-bottom: 20px;">
                  <img src="{{ Storage::disk('public')->url(app('suppliers_upload_url').e($supplier->image)) }}" class="img-responsive img-thumbnail" alt="{{ $supplier->name }}">
              </div>
          @endif

      </div> <!--/col-md-3-->

  </div>
  </div>

@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table', [
      'exportFile' => 'locations-export',
      'search' => true
   ])

@stop
