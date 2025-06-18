@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ trans('general.location') }}:
 {{ $location->name }}
 
@parent
@stop

@section('header_right')
<a href="{{ route('locations.index') }}" class="btn btn-primary" style="margin-right: 10px;">
    {{ trans('general.back') }}</a>
@endsection
{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">

      <div class="nav-tabs-custom">
          <ul class="nav nav-tabs hidden-print">

              @can('view', \App\Models\User::class)
                  @if ($location->users->count() > 0)
                      <li class="active">
                          <a href="#users" data-toggle="tab">
                              <i class="fa-solid fa-house-user" style="font-size: 17px" aria-hidden="true"></i>
                              <span class="sr-only">
                            {{ trans('general.users') }}
                              </span>
                              <span class="badge">
                                {{ number_format($location->users->count()) }}
                              </span>
                          </a>
                      </li>
                  @endif
              @endcan

              @can('view', \App\Models\Asset::class)
                  @if ($location->assets()->AssetsForShow()->count() > 0)
                      <li>
                          <a href="#assets" data-toggle="tab" data-tooltip="true" title="{{ trans('admin/locations/message.current_location') }}">
                              <i class="fa-solid fa-house-laptop" style="font-size: 17px" aria-hidden="true"></i>
                              <span class="badge">
                          {{ number_format($location->assets()->AssetsForShow()->count()) }}
                      </span>
                              <span class="sr-only">
                          {{ trans('admin/locations/message.current_location') }}
                      </span>
                          </a>
                      </li>
                  @endif

                  @if ($location->rtd_assets()->AssetsForShow()->count() > 0)
                      <li>
                          <a href="#rtd_assets" data-toggle="tab" data-tooltip="true" title="{{ trans('admin/hardware/form.default_location') }}">
                              <i class="fa-solid fa-house-flag" style="font-size: 17px" aria-hidden="true"></i>
                              <span class="badge">
                          {{ number_format($location->rtd_assets()->AssetsForShow()->count()) }}
                      </span>
                              <span class="sr-only">
                          {{ trans('admin/hardware/form.default_location') }}
                      </span>
                          </a>
                      </li>
                  @endif

                  @if ($location->assignedAssets()->AssetsForShow()->count() > 0)
                      <li>
                          <a href="#assets_assigned" data-toggle="tab" data-tooltip="true" title="{{ trans('admin/locations/message.assigned_assets') }}">
                              <i class="fas fa-barcode" style="font-size: 17px" aria-hidden="true"></i>
                              <span class="badge">
                          {{ number_format($location->assignedAssets()->AssetsForShow()->count()) }}
                      </span>
                              <span class="sr-only">
                          {{ trans('admin/locations/message.assigned_assets') }}
                      </span>
                          </a>
                      </li>
                  @endif
              @endcan

                  @can('view', \App\Models\Accessory::class)
                      @if ($location->accessories->count() > 0)
                          <li>
                              <a href="#accessories" data-toggle="tab" data-tooltip="true" title="{{ trans('general.accessories') }}">
                                  <i class="far fa-keyboard" style="font-size: 17px" aria-hidden="true"></i>
                                  <span class="badge">
                                    {{ number_format($location->accessories->count()) }}
                                  </span>
                                  <span class="sr-only">
                                    {{ trans('general.accessories') }}
                                  </span>
                              </a>
                          </li>
                      @endif

                      @if ($location->assignedAccessories->count() > 0)
                          <li>
                              <a href="#accessories_assigned" data-toggle="tab" data-tooltip="true" title="{{ trans('general.accessories_assigned') }}">
                                  <i class="fas fa-keyboard" style="font-size: 17px" aria-hidden="true"></i>
                                  <span class="badge">
                                      {{ number_format($location->assignedAccessories->count()) }}
                                  </span>
                                  <span class="sr-only">
                                      {{ trans('general.accessories_assigned') }}
                                  </span>
                              </a>
                          </li>
                      @endif
                  @endcan


              @can('view', \App\Models\Consumable::class)
                      @if ($location->consumables->count() > 0)
                          <li>
                              <a href="#consumables" data-toggle="tab" data-tooltip="true" title="{{ trans('general.consumables') }}">
                                  <i class="fas fa-tint" style="font-size: 17px" aria-hidden="true"></i>
                                  <span class="badge">
                              {{ number_format($location->consumables->count()) }}
                          </span>
                                  <span class="sr-only">
                              {{ trans('general.consumables') }}
                          </span>
                              </a>
                          </li>
                      @endif
                  @endcan

                  @can('view', \App\Models\Component::class)
                      @if ($location->components->count() > 0)
                          <li>
                              <a href="#components" data-toggle="tab" data-tooltip="true" title="{{ trans('general.components') }}">
                                  <i class="fas fa-hdd" style="font-size: 17px" aria-hidden="true"></i>
                                  <span class="badge">
                                    {{ number_format($location->components->count()) }}
                                  </span>
                                  <span class="sr-only">
                                    {{ trans('general.components') }}
                                  </span>
                              </a>
                          </li>
                      @endif
                  @endcan

              @if ($location->uploads->count() > 0 )
              <li>
                  <a href="#files" data-toggle="tab">

                    <span class="hidden-lg hidden-md">
                      <i class="fas fa-barcode fa-2x"></i>
                    </span>
                      <span class="hidden-xs hidden-sm">
                        {{ trans('general.files') }}
                          {!! ($location->uploads->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($location->uploads->count()).'</badge>' : '' !!}
                      </span>
                  </a>
              </li>
              @endif

              <li>
                  <a href="#history" data-toggle="tab" data-toggle="tab" data-tooltip="true" title="{{ trans('general.history') }}">
                      <i class="fa-solid fa-clock-rotate-left" style="font-size: 17px" aria-hidden="true"></i>
                      <span class="sr-only">
                          {{ trans('general.history') }}
                    </span>
                  </a>
              </li>

              @can('update', $location)
              <li class="pull-right">
                  <a href="#" data-toggle="modal" data-target="#uploadFileModal">
                      <x-icon type="paperclip" />
                      {{ trans('button.upload') }}
                  </a>
              </li>
              @endcan
          </ul>


          <div class="tab-content">
              @can('view', \App\Models\User::class)
                    <div id="users" @class(['tab-pane','active' => $location->users->count() > 0 ]) >
              @endcan
                  <h2 class="box-title">{{ trans('general.users') }}</h2>
                      @include('partials.users-bulk-actions')
                      <table
                              data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                              data-cookie-id-table="usersTable"
                              data-pagination="true"
                              data-id-table="usersTable"
                              data-search="true"
                              data-search-highlight="true"
                              data-show-print="true"
                              data-side-pagination="server"
                              data-show-columns="true"
                              data-show-export="true"
                              data-show-refresh="true"
                              data-sort-order="asc"
                              data-show-fullscreen="true"
                              data-toolbar="#userBulkEditToolbar"
                              data-bulk-button-id="#bulkUserEditButton"
                              data-bulk-form-id="#usersBulkForm"
                              data-click-to-select="true"
                              id="usersTable"
                              class="table table-striped snipe-table"
                              data-url="{{route('api.users.index', ['location_id' => $location->id])}}"
                              data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-users-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                      </table>
                    </div><!-- /.tab-pane -->
                <div id="assets" @class(['tab-pane', 'active' => $location->users->count() == 0]) >

                  <h2 class="box-title">{{ trans('admin/locations/message.current_location') }}</h2>

                      @include('partials.asset-bulk-actions')
                      <table
                              data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                              data-cookie-id-table="assetsListingTable"
                              data-pagination="true"
                              data-id-table="assetsListingTable"
                              data-search="true"
                              data-search-highlight="true"
                              data-show-print="true"
                              data-side-pagination="server"
                              data-show-columns="true"
                              data-show-export="true"
                              data-show-refresh="true"
                              data-sort-order="asc"
                              data-show-fullscreen="true"
                              data-toolbar="#assetsBulkEditToolbar"
                              data-bulk-button-id="#bulkAssetEditButton"
                              data-bulk-form-id="#assetsBulkForm"
                              data-click-to-select="true"
                              id="assetsListingTable"
                              class="table table-striped snipe-table"
                              data-url="{{route('api.assets.index', ['location_id' => $location->id]) }}"
                              data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                      </table>
              </div><!-- /.tab-pane -->

              <div class="tab-pane" id="assets_assigned">
                  <h2 class="box-title">
                      {{ trans('admin/locations/message.assigned_assets') }}
                  </h2>

                      @include('partials.asset-bulk-actions', ['id_divname' => 'AssignedAssetsBulkEditToolbar', 'id_formname' => 'assignedAssetsBulkForm', 'id_button' => 'AssignedbulkAssetEditButton'])
                      <table
                              role="table"
                              data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                              data-cookie-id-table="assetsAssignedListingTable"
                              data-pagination="true"
                              data-id-table="assetsAssignedListingTable"
                              data-search="true"
                              data-search-highlight="true"
                              data-show-print="true"
                              data-side-pagination="server"
                              data-show-columns="true"
                              data-show-export="true"
                              data-show-refresh="true"
                              data-sort-order="asc"
                              data-show-fullscreen="true"
                              data-toolbar="#AssignedAssetsBulkEditToolbar"
                              data-bulk-button-id="#AssignedbulkAssetEditButton"
                              data-bulk-form-id="#assignedAssetsBulkForm"
                              data-click-to-select="true"
                              id="assetsAssignedListingTable"
                              class="table table-striped snipe-table"
                              data-url="{{route('api.assets.index', ['assigned_to' => $location->id, 'assigned_type' => 'App\Models\Location']) }}"
                              data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                      </table>
              </div><!-- /.tab-pane -->

              <div class="tab-pane" id="rtd_assets">
                  <h2 class="box-title">{{ trans('admin/hardware/form.default_location') }}</h2>

                      @include('partials.asset-bulk-actions', ['id_divname' => 'RTDassetsBulkEditToolbar', 'id_formname' => 'RTDassets', 'id_button' => 'RTDbulkAssetEditButton'])
                      <table
                              role="table"
                              data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                              data-cookie-id-table="RTDassetsListingTable"
                              data-pagination="true"
                              data-id-table="RTDassetsListingTable"
                              data-search="true"
                              data-search-highlight="true"
                              data-show-print="true"
                              data-side-pagination="server"
                              data-show-columns="true"
                              data-show-export="true"
                              data-show-refresh="true"
                              data-sort-order="asc"
                              data-show-fullscreen="true"
                              data-toolbar="#RTDassetsBulkEditToolbar"
                              data-bulk-button-id="#RTDbulkAssetEditButton"
                              data-bulk-form-id="#RTDassetsBulkEditToolbar"
                              data-click-to-select="true"
                              id="RTDassetsListingTable"
                              class="table table-striped snipe-table"
                              data-url="{{route('api.assets.index', ['rtd_location_id' => $location->id]) }}"
                              data-export-options='{
                              "fileName": "export-rtd-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                      </table>
              </div><!-- /.tab-pane -->
              


              <div class="tab-pane" id="accessories">
                  <h2 class="box-title">{{ trans('general.accessories') }}</h2>
                      <table
                              role="table"
                              data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                              data-cookie-id-table="accessoriesListingTable"
                              data-pagination="true"
                              data-id-table="accessoriesListingTable"
                              data-search="true"
                              data-search-highlight="true"
                              data-show-print="true"
                              data-side-pagination="server"
                              data-show-columns="true"
                              data-show-export="true"
                              data-show-refresh="true"
                              data-sort-order="asc"
                              data-show-fullscreen="true"
                              id="accessoriesListingTable"
                              class="table table-striped snipe-table"
                              data-url="{{route('api.accessories.index', ['location_id' => $location->id]) }}"
                              data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-accessories-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                      </table>
              </div><!-- /.tab-pane -->

              <div class="tab-pane" id="accessories_assigned">
                      <h2 class="box-title" style="float:left">
                          {{ trans('general.accessories_assigned') }}
                      </h2>

                      <table
                              role="table"
                              data-columns="{{ \App\Presenters\LocationPresenter::assignedAccessoriesDataTableLayout() }}"
                              data-cookie-id-table="accessoriesAssignedListingTable"
                              data-pagination="true"
                              data-id-table="accessoriesAssignedListingTable"
                              data-search="true"
                              data-search-highlight="true"
                              data-show-print="true"
                              data-side-pagination="server"
                              data-show-columns="true"
                              data-show-export="true"
                              data-show-refresh="true"
                              data-sort-order="asc"
                              data-show-fullscreen="true"
                              data-click-to-select="true"
                              id="accessoriesAssignedListingTable"
                              class="table table-striped snipe-table"
                              data-url="{{ route('api.locations.assigned_accessories', ['location' => $location]) }}"
                              data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-accessories-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                      </table>
              </div><!-- /.tab-pane -->


              <div class="tab-pane" id="consumables">
                  <h2 class="box-title">{{ trans('general.consumables') }}</h2>
                          <table
                                  role="table"
                                  data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                                  data-cookie-id-table="consumablesListingTable"
                                  data-pagination="true"
                                  data-id-table="consumablesListingTable"
                                  data-search="true"
                                  data-search-highlight="true"
                                  data-show-print="true"
                                  data-side-pagination="server"
                                  data-show-columns="true"
                                  data-show-export="true"
                                  data-show-refresh="true"
                                  data-sort-order="asc"
                                  data-show-fullscreen="true"
                                  id="consumablesListingTable"
                                  class="table table-striped snipe-table"
                                  data-url="{{route('api.consumables.index', ['location_id' => $location->id]) }}"
                                  data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-consumables-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                          </table>
              </div><!-- /.tab-pane -->

              <div class="tab-pane" id="components">
                  <h2 class="box-title">{{ trans('general.components') }}</h2>
                          <table
                                  role="table"
                                  data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                                  data-cookie-id-table="componentsTable"
                                  data-pagination="true"
                                  data-id-table="componentsTable"
                                  data-search="true"
                                  data-search-highlight="true"
                                  data-show-print="true"
                                  data-side-pagination="server"
                                  data-show-columns="true"
                                  data-show-export="true"
                                  data-show-refresh="true"
                                  data-sort-order="asc"
                                  data-show-fullscreen="true"
                                  id="componentsTable"
                                  class="table table-striped snipe-table"
                                  data-url="{{route('api.components.index', ['location_id' => $location->id])}}"
                                  data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-components-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                          </table>
              </div><!-- /.tab-pane -->

                  <div class="tab-pane fade" id="files">

                      <div class="row">
                          <div class="col-md-12">

                              <x-filestable
                                      filepath="private_uploads/locations/"
                                      showfile_routename="show/locationsfile"
                                      deletefile_routename="delete/locationsfile"
                                      :object="$location" />

                          </div> <!-- /.col-md-12 -->
                      </div> <!-- /.row -->

                  </div>

                <div class="tab-pane" id="history">
                    <h2 class="box-title">{{ trans('general.history') }}</h2>
                    <!-- checked out assets table -->
                    <div class="row">
                        <div class="col-md-12">
                            <table
                                    class="table table-striped snipe-table"
                                    id="assetHistory"
                                    data-pagination="true"
                                    data-id-table="assetHistory"
                                    data-search="true"
                                    data-search-highlight="true"
                                    data-show-print="true"
                                    data-side-pagination="server"
                                    data-show-columns="true"
                                    data-show-fullscreen="true"
                                    data-show-refresh="true"
                                    data-sort-order="desc"
                                    data-sort-name="created_at"
                                    data-show-export="true"
                                    data-export-options='{
                        "fileName": "export-location-asset-{{  $location->id }}-history",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'

                    data-url="{{ route('api.activity.index', ['target_id' => $location->id, 'target_type' => 'location']) }}"
                    data-cookie-id-table="assetHistory"
                    data-cookie="true">
                                <thead>
                                    <tr>
                                        <th data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                                        <th class="col-sm-2" data-visible="true" data-field="action_date" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                        <th class="col-sm-1" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                                        <th class="col-sm-1" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                                        <th class="col-sm-2" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                                        <th class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                                        <th class="col-sm-2" data-field="note">{{ trans('general.notes') }}</th>
                                        <th class="col-md-3" data-field="signature_file" data-visible="false"  data-formatter="imageFormatter">{{ trans('general.signature') }}</th>
                                        <th class="col-md-3" data-visible="false" data-field="file" data-visible="false"  data-formatter="fileUploadFormatter">{{ trans('general.download') }}</th>
                                        <th class="col-sm-2" data-field="log_meta" data-visible="true" data-formatter="changeLogFormatter">{{ trans('admin/hardware/table.changed')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div> <!-- /.row -->
                </div> <!-- /.tab-pane history -->

          </div><!--/.col-md-9-->
      </div><!--/.col-md-9-->
  </div><!--/.col-md-9-->

  <div class="col-md-3">

      @if ($location->image!='')
          <div class="col-md-12 text-center" style="padding-bottom: 17px;">
              <img src="{{ Storage::disk('public')->url('locations/'.e($location->image)) }}" class="img-responsive img-thumbnail" style="width:100%" alt="{{ $location->name }}">
          </div>
      @endif

      @if (($location->state!='') && ($location->country!='') && (config('services.google.maps_api_key')))
          <div class="col-md-12 text-center" style="padding-bottom: 10px;">
              <img src="https://maps.googleapis.com/maps/api/staticmap?markers={{ urlencode($location->address.','.$location->city.' '.$location->state.' '.$location->country.' '.$location->zip) }}&size=700x500&maptype=roadmap&key={{ config('services.google.maps_api_key') }}" class="img-thumbnail" style="width:100%" alt="Map">
          </div>
      @endif

      <div class="col-md-12">

          <ul class="list-unstyled" style="line-height: 22px; padding-bottom: 20px;">

              @if ($location->notes)
                  <li>
                      <strong>{{ trans('general.notes') }}</strong>:
                      {!! nl2br(Helper::parseEscapedMarkedownInline($location->notes)) !!}
                  </li>
              @endif

              @if ($location->address!='')
                  <li>{{ $location->address }}</li>
              @endif
              @if ($location->address2!='')
                  <li>{{ $location->address2 }}</li>
              @endif
              @if (($location->city!='') || ($location->state!='') || ($location->zip!=''))
                  <li>{{ $location->city }} {{ $location->state }} {{ $location->zip }}</li>
              @endif
              @if ($location->manager)
                  <li>{{ trans('admin/users/table.manager') }}: {!! $location->manager->present()->nameUrl() !!}</li>
              @endif
              @if ($location->company)
                  <li>{{ trans('admin/companies/table.name') }}: {!! $location->company->present()->nameUrl() !!}</li>
              @endif
              @if ($location->parent)
                  <li>{{ trans('admin/locations/table.parent') }}: {!! $location->parent->present()->nameUrl() !!}</li>
              @endif
              @if ($location->ldap_ou)
                  <li>{{ trans('admin/locations/table.ldap_ou') }}: {{ $location->ldap_ou }}</li>
              @endif


              @if ((($location->address!='') && ($location->city!='')) || ($location->state!='') || ($location->country!=''))
                      <li>
                        <a href="https://maps.google.com/?q={{ urlencode($location->address.','. $location->city.','.$location->state.','.$location->country.','.$location->zip) }}" target="_blank">
                            {!! trans('admin/locations/message.open_map', ['map_provider_icon' => '<i class="fa-brands fa-google" aria-hidden="true"></i>']) !!}
                            <x-icon type="external-link"/>
                        </a>
                      </li>
                      <li>
                        <a href="https://maps.apple.com/?q={{ urlencode($location->address.','. $location->city.','.$location->state.','.$location->country.','.$location->zip) }}" target="_blank">
                            {!! trans('admin/locations/message.open_map', ['map_provider_icon' => '<i class="fa-brands fa-apple" aria-hidden="true" style="font-size: 18px"></i>']) !!}
                            <x-icon type="external-link"/></a>
                  </li>
              @endif

          </ul>
      </div>

      @can('update', $location)
      <div class="col-md-12">
          <a href="{{ route('locations.edit', ['location' => $location->id]) }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social">
              <x-icon type="edit" />
              {{ trans('admin/locations/table.update') }}
          </a>
      </div>
      @endcan

      <div class="col-md-12" style="padding-top: 5px;">
          <a href="{{ route('locations.print_assigned', ['locationId' => $location->id]) }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social hidden-print">
              <x-icon type="print" />
              {{ trans('admin/locations/table.print_assigned') }}
          </a>
      </div>
      <div class="col-md-12" style="padding-top: 5px;">
          <a href="{{ route('locations.print_all_assigned', ['locationId' => $location->id]) }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social hidden-print">
              <x-icon type="print" />
              {{ trans('admin/locations/table.print_all_assigned') }}
          </a>
      </div>

          @can('delete', $location)
              <div class="col-md-12 hidden-print" style="padding-top: 10px;">

            @if ($location->deleted_at=='')

                @if ($location->isDeletable())
                      <button class="btn btn-sm btn-block btn-danger btn-social delete-location" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $location->name]) }}" data-target="#dataConfirmModal">
                          <x-icon type="delete" />
                          {{ trans('general.delete') }}
                      </button>
                @else
                      <a href="#" class="btn btn-block btn-sm btn-danger btn-social hidden-print disabled" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}">
                          <x-icon type="delete" />
                          {{ trans('general.delete') }}
                      </a>
                @endif

            @else
                  <form method="POST" action="{{ route('locations.restore', ['location' => $location->id]) }}">
                      @csrf
                      <button class="btn btn-sm btn-block btn-warning btn-social delete-asset">
                          <x-icon type="restore" />
                          {{ trans('general.restore') }}
                      </button>
                  </form>
            @endif
              </div>
    @endcan



</div>
</div>


@stop

@section('moar_scripts')

    @can('update', Location::class)
        @include ('modals.upload-file', ['item_type' => 'locations', 'item_id' => $location->id])
    @endcan

    <script>
        $('#dataConfirmModal').on('show.bs.modal', function (event) {
            var content = $(event.relatedTarget).data('content');
            var title = $(event.relatedTarget).data('title');
            $(this).find(".modal-body").text(content);
            $(this).find(".modal-header").text(title);
        });
    </script>

@include ('partials.bootstrap-table', [
'exportFile' => 'locations-export',
'search' => true
])

@stop
