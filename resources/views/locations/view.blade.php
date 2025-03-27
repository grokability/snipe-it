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
                        <li class="active">
                            <a href="#assets" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('admin/locations/message.current_location') }}">
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
                            <a href="#rtd_assets" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('admin/hardware/form.default_location') }}">
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
                            <a href="#assets_assigned" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('admin/locations/message.assigned_assets') }}">
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
                            <a href="#accessories" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('general.accessories') }}">
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
                            <a href="#accessories_assigned" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('general.accessories_assigned') }}">
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
                            <a href="#consumables" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('general.consumables') }}">
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
                            <a href="#components" data-toggle="tab" data-tooltip="true"
                                title="{{ trans('general.components') }}">
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
                @if ($location->children()->count() > 0)
                    <li>
                        <a href="#managed" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <i class="fas fa-map-marker-alt fa-2x"></i></span>
                            <span class="hidden-xs hidden-sm">Lista filijala
                                {!! ($location->children->count() > 0) ? '<badge class="badge badge-secondary">' . number_format($location->children->count()) . '</badge>' : '' !!}
                        </a>
                    </li>
                @endif
                <li>
                    <a href="#files" data-toggle="tab">
                        <span class="hidden-lg hidden-md">
                            <i class="far fa-file fa-2x"></i>
                        </span>
                        <span class="hidden-xs hidden-sm">{{ trans('general.file_uploads') }}
                            {!! ($location->uploads->count() > 0) ? '<badge class="badge badge-secondary">' . number_format($location->uploads->count()) . '</badge>' : '' !!}
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#history" data-toggle="tab" data-toggle="tab" data-tooltip="true"
                        title="{{ trans('general.history') }}">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 17px" aria-hidden="true"></i>
                        <span class="sr-only">
                            {{ trans('general.history') }}
                        </span>
                    </a>
                </li>

            </ul>


            <div class="tab-content">
                <div class="tab-pane" id="users">
                    <h2 class="box-title">{{ trans('general.users') }}</h2>
                    <div class="table table-responsive">
                        @include('partials.users-bulk-actions')
                        <table data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                            data-cookie-id-table="usersTable" data-pagination="true" data-id-table="usersTable"
                            data-search="true" data-side-pagination="server" data-show-columns="true"
                            data-show-export="true" data-show-refresh="true" data-sort-order="asc"
                            data-toolbar="#userBulkEditToolbar" data-bulk-button-id="#bulkUserEditButton"
                            data-bulk-form-id="#usersBulkForm" data-click-to-select="true" id="usersTable"
                            class="table table-striped snipe-table"
                            data-url="{{route('api.users.index', ['location_id' => $location->id])}}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-users-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane active" id="assets">
                    <h2 class="box-title">{{ trans('admin/locations/message.current_location') }}</h2>

                    <div class="table table-responsive">
                        @include('partials.asset-bulk-actions')
                        <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                            data-cookie-id-table="assetsListingTable" data-pagination="true"
                            data-id-table="assetsListingTable" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true"
                            data-sort-order="asc" data-toolbar="#assetsBulkEditToolbar"
                            data-bulk-button-id="#bulkAssetEditButton" data-bulk-form-id="#assetsBulkForm"
                            data-click-to-select="true" id="assetsListingTable" class="table table-striped snipe-table"
                            data-url="{{route('api.assets.index', ['location_id' => $location->id]) }}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                        </table>

                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="assets_assigned">
                    <h2 class="box-title">
                        {{ trans('admin/locations/message.assigned_assets') }}
                    </h2>

                    <div class="table table-responsive">
                        @include('partials.asset-bulk-actions', ['id_divname' => 'AssignedAssetsBulkEditToolbar', 'id_formname' => 'assignedAssetsBulkForm', 'id_button' => 'AssignedbulkAssetEditButton'])
                        <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                            data-cookie-id-table="assetsAssignedListingTable" data-pagination="true"
                            data-id-table="assetsAssignedListingTable" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true"
                            data-sort-order="asc" data-toolbar="#AssignedAssetsBulkEditToolbar"
                            data-bulk-button-id="#AssignedbulkAssetEditButton"
                            data-bulk-form-id="#assignedAssetsBulkForm" data-click-to-select="true"
                            id="assetsListingTable" class="table table-striped snipe-table"
                            data-url="{{route('api.locations.assigned_assets', ['location' => $location]) }}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                        </table>

                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="rtd_assets">
                    <h2 class="box-title">{{ trans('admin/hardware/form.default_location') }}</h2>

                    <div class="table table-responsive">
                        @include('partials.asset-bulk-actions', ['id_divname' => 'RTDassetsBulkEditToolbar', 'id_formname' => 'RTDassets', 'id_button' => 'RTDbulkAssetEditButton'])
                        <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                            data-cookie-id-table="RTDassetsListingTable" data-pagination="true"
                            data-id-table="RTDassetsListingTable" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true"
                            data-sort-order="asc" data-toolbar="#RTDassetsBulkEditToolbar"
                            data-bulk-button-id="#RTDbulkAssetEditButton" data-bulk-form-id="#RTDassetsBulkEditToolbar"
                            data-click-to-select="true" id="RTDassetsListingTable"
                            class="table table-striped snipe-table"
                            data-url="{{route('api.assets.index', ['rtd_location_id' => $location->id]) }}"
                            data-export-options='{
                              "fileName": "export-rtd-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                        </table>

                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->



                <div class="tab-pane" id="accessories">
                    <h2 class="box-title">{{ trans('general.accessories') }}</h2>
                    <div class="table table-responsive">
                        <table data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                            data-cookie-id-table="accessoriesListingTable" data-pagination="true"
                            data-id-table="accessoriesListingTable" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true"
                            data-sort-order="asc" id="accessoriesListingTable" class="table table-striped snipe-table"
                            data-url="{{route('api.accessories.index', ['location_id' => $location->id]) }}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-accessories-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="accessories_assigned">

                    <div class="table table-responsive">

                        <h2 class="box-title" style="float:left">
                            {{ trans('general.accessories_assigned') }}
                        </h2>

                        <table
                            data-columns="{{ \App\Presenters\LocationPresenter::assignedAccessoriesDataTableLayout() }}"
                            data-cookie-id-table="accessoriesAssignedListingTable" data-pagination="true"
                            data-id-table="accessoriesAssignedListingTable" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-show-export="true"
                            data-show-refresh="true" data-sort-order="asc" data-click-to-select="true"
                            id="accessoriesAssignedListingTable" class="table table-striped snipe-table"
                            data-url="{{ route('api.locations.assigned_accessories', ['location' => $location]) }}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-accessories-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                        </table>

                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->


                <div class="tab-pane" id="consumables">
                    <h2 class="box-title">{{ trans('general.consumables') }}</h2>

                    <div class="table table-responsive">
                        <table data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                            data-cookie-id-table="consumablesListingTable" data-pagination="true"
                            data-id-table="consumablesListingTable" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true"
                            data-sort-order="asc" id="consumablesListingTable" class="table table-striped snipe-table"
                            data-url="{{route('api.consumables.index', ['location_id' => $location->id]) }}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-consumables-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                        </table>

                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="components">
                    <h2 class="box-title">{{ trans('general.components') }}</h2>
                    <div class="table table-responsive">

                        <table data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                            data-cookie-id-table="componentsTable" data-pagination="true"
                            data-id-table="componentsTable" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true"
                            data-sort-order="asc" id="componentsTable" class="table table-striped snipe-table"
                            data-url="{{route('api.components.index', ['location_id' => $location->id])}}"
                            data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-components-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="history">
                    <h2 class="box-title">{{ trans('general.history') }}</h2>
                    <!-- checked out assets table -->
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped snipe-table" id="assetHistory" data-pagination="true"
                                data-id-table="assetHistory" data-search="true" data-side-pagination="server"
                                data-show-columns="true" data-show-fullscreen="true" data-show-refresh="true"
                                data-sort-order="desc" data-sort-name="created_at" data-show-export="true"
                                data-export-options='{
                        "fileName": "export-location-asset-{{  $location->id }}-history",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }' data-url="{{ route('api.activity.index', ['target_id' => $location->id, 'target_type' => 'location']) }}"
                                data-cookie-id-table="assetHistory" data-cookie="true">
                                <thead>
                                    <tr>
                                        <th data-visible="true" data-field="icon" style="width: 40px;" class="hidden-xs"
                                            data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                                        <th class="col-sm-2" data-visible="true" data-field="action_date"
                                            data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                        <th class="col-sm-1" data-visible="true" data-field="admin"
                                            data-formatter="usersLinkObjFormatter">{{ trans('general.admin') }}</th>
                                        <th class="col-sm-1" data-visible="true" data-field="action_type">
                                            {{ trans('general.action') }}</th>
                                        <th class="col-sm-2" data-visible="true" data-field="item"
                                            data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                                        <th class="col-sm-2" data-visible="true" data-field="target"
                                            data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                                        <th class="col-sm-2" data-field="note">{{ trans('general.notes') }}</th>
                                        <th class="col-md-3" data-field="signature_file" data-visible="false"
                                            data-formatter="imageFormatter">{{ trans('general.signature') }}</th>
                                        <th class="col-md-3" data-visible="false" data-field="file" data-visible="false"
                                            data-formatter="fileUploadFormatter">{{ trans('general.download') }}</th>
                                        <th class="col-sm-2" data-field="log_meta" data-visible="true"
                                            data-formatter="changeLogFormatter">
                                            {{ trans('admin/hardware/table.changed')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div> <!-- /.row -->
                </div> <!-- /.tab-pane history -->
                <div class="tab-pane" id="files">
                    <div class="row">

                        <div class="col-md-12 col-sm-12">
                            <div class="table-responsive">
                                <table data-cookie-id-table="locationUploadsTable" data-id-table="locationUploadsTable"
                                    id="locationUploadsTable" data-search="true" data-pagination="true"
                                    data-side-pagination="client" data-show-columns="true" data-show-fullscreen="true"
                                    data-show-export="true" data-show-footer="true" data-toolbar="#upload-toolbar"
                                    data-show-refresh="true" data-sort-order="asc" data-sort-name="name"
                                    class="table table-striped snipe-table" data-export-options='{
                    "fileName": "export-license-uploads-{{ str_slug($location->name) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

                                    <thead>
                                        <tr>
                                            <th data-visible="true" data-field="icon" data-sortable="true">
                                                {{trans('general.file_type')}}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="image">{{ trans('general.image') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="filename" data-sortable="true">
                                                {{ trans('general.file_name') }}</th>
                                            <th class="col-md-1" data-searchable="true" data-visible="true"
                                                data-field="filesize">{{ trans('general.filesize') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="notes" data-sortable="true">{{ trans('general.notes') }}
                                            </th>
                                            <th class="col-md-1" data-searchable="true" data-visible="true"
                                                data-field="download">{{ trans('general.download') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="created_at" data-sortable="true">
                                                {{ trans('general.created_at') }}</th>
                                            <th class="col-md-1" data-searchable="true" data-visible="true"
                                                data-field="actions">{{ trans('table.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($location->uploads as $file)
                                            <tr>
                                                <td>
                                                    <i class="{{ Helper::filetype_icon($file->filename) }} icon-med"
                                                        aria-hidden="true"></i>
                                                    <span
                                                        class="sr-only">{{ Helper::filetype_icon($file->filename) }}</span>

                                                </td>
                                                <td>
                                                    @if (($file->filename) && (Storage::exists('private_uploads/locations/' . $file->filename)))
                                                        @if (Helper::checkUploadIsImage($file->get_src('locations')))
                                                            <a href="{{ route('show/locationfile', [$location->id, $file->id, 'inline' => 'true']) }}"
                                                                data-toggle="lightbox" data-type="image"><img
                                                                    src="{{ route('show/locationfile', [$location->id, $file->id, 'inline' => 'true']) }}"
                                                                    class="img-thumbnail" style="max-width: 50px;"></a>
                                                        @else
                                                            {{ trans('general.preview_not_available') }}
                                                        @endif
                                                    @else
                                                        <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                                        {{ trans('general.file_not_found') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $file->filename }}
                                                </td>
                                                <td
                                                    data-value="{{ (Storage::exists('private_uploads/locations/' . $file->filename)) ? Storage::size('private_uploads/locations/' . $file->filename) : '' }}">
                                                    {{ (Storage::exists('private_uploads/locations/' . $file->filename)) ? Helper::formatFilesizeUnits(Storage::size('private_uploads/locations/' . $file->filename)) : '' }}
                                                </td>

                                                <td>
                                                    @if ($file->note)
                                                        {{ $file->note }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($file->filename)
                                                        @if (Storage::exists('private_uploads/locations/' . $file->filename))
                                                            <a href="{{ route('show/locationfile', [$location->id, $file->id]) }}"
                                                                class="btn btn-sm btn-default">
                                                                <i class="fas fa-download" aria-hidden="true"></i>
                                                                <span class="sr-only">{{ trans('general.download') }}</span>
                                                            </a>

                                                            <a href="{{ route('show/locationfile', [$location->id, $file->id, 'inline' => 'true']) }}"
                                                                class="btn btn-sm btn-default" target="_blank">
                                                                <i class="fa fa-external-link" aria-hidden="true"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{ $file->created_at }}</td>

                                                <td>
                                                    <a class="btn delete-asset btn-danger btn-sm hidden-print"
                                                        href="{{ route('locationfile.destroy', [$location->id, $file->id]) }}"
                                                        data-content="Are you sure you wish to delete this file?"
                                                        data-title="Delete {{ $file->filename }}?">
                                                        <i class="fa fa-trash icon-white" aria-hidden="true"></i>
                                                        <span class="sr-only">{{ trans('general.delete') }}</span>
                                                    </a>
                                                </td>



                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!--/ROW-->
                </div><!--/FILES-->
                <!--- /.tab-pane managed -->                
<div class="tab-pane" id="managed">
<table
        data-columns="{{ \App\Presenters\LocationPresenter::dataTableLayout() }}"
        data-cookie-id-table="locationTable"
        data-click-to-select="true"
        data-pagination="true"
        data-id-table="locationTable"
        data-toolbar="#locationsBulkEditToolbar"
        data-bulk-button-id="#bulkLocationsEditButton"
        data-bulk-form-id="#locationsBulkForm"
        data-search="true"
        data-show-footer="true"
        data-side-pagination="server"
        data-show-columns="true"
        data-show-fullscreen="true"
        data-show-export="true"
        data-show-refresh="true"
        data-sort-order="asc"
        id="locationTable"
        class="table table-striped snipe-table"
        data-url="{{ route('api.locations.index', ['parent_id' => $location->id ]) }}"
        data-export-options='{
  "fileName": "export-locations-{{ date('Y-m-d') }}",
  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
  }'>
</table>

</div>  
            </div><!--/.col-md-9-->
        </div><!--/.col-md-9-->
    </div><!--/.col-md-9-->

    <div class="col-md-3">

        @if ($location->image != '')
            <div class="col-md-12 text-center" style="padding-bottom: 17px;">
                <img src="{{ Storage::disk('public')->url('locations/' . e($location->image)) }}"
                    class="img-responsive img-thumbnail" style="width:100%" alt="{{ $location->name }}">
            </div>
        @endif

        @if (($location->state != '') && ($location->country != '') && (config('services.google.maps_api_key')))
            <div class="col-md-12 text-center" style="padding-bottom: 10px;">
                <img src="https://maps.googleapis.com/maps/api/staticmap?markers={{ urlencode($location->address . ',' . $location->city . ' ' . $location->state . ' ' . $location->country . ' ' . $location->zip) }}&size=700x500&maptype=roadmap&key={{ config('services.google.maps_api_key') }}"
                    class="img-thumbnail" style="width:100%" alt="Map">
            </div>
        @endif

        <div class="col-md-12">
            <ul class="list-unstyled" style="line-height: 20px; padding-bottom: 20px;">
                @if ($location->address != '')
                    <li>{{ $location->address }}</li>
                @endif
                @if ($location->address2 != '')
                    <li>{{ $location->address2 }}</li>
                @endif
                @if (($location->city != '') || ($location->state != '') || ($location->zip != ''))
                    <li>{{ $location->city }} {{ $location->state }} {{ $location->zip }}</li>
                @endif
                @if ($location->manager)
                    <li>{{ trans('admin/users/table.manager') }}: {!! $location->manager->present()->nameUrl() !!}</li>
                @endif
                @if ($location->parent)
                    <li>{{ trans('admin/locations/table.parent') }}: {!! $location->parent->present()->nameUrl() !!}</li>
                @endif
                @if ($location->ldap_ou)
                    <li>{{ trans('admin/locations/table.ldap_ou') }}: {{ $location->ldap_ou }}</li>
                @endif

                @if ((($location->address != '') && ($location->city != '')) || ($location->state != '') || ($location->country != ''))
                    <li>
                        <a href="https://maps.google.com/?q={{ urlencode($location->address . ',' . $location->city . ',' . $location->state . ',' . $location->country . ',' . $location->zip) }}"
                            target="_blank">
                            {!! trans('admin/locations/message.open_map', ['map_provider_icon' => '<i class="fa-brands fa-google" aria-hidden="true"></i>']) !!}
                            <x-icon type="external-link" />
                        </a>
                    </li>
                    <li>
                        <a href="https://maps.apple.com/?q={{ urlencode($location->address . ',' . $location->city . ',' . $location->state . ',' . $location->country . ',' . $location->zip) }}"
                            target="_blank">
                            {!! trans('admin/locations/message.open_map', ['map_provider_icon' => '<i class="fa-brands fa-apple" aria-hidden="true" style="font-size: 18px"></i>']) !!}
                            <x-icon type="external-link" /></a>
                    </li>
                @endif

            </ul>
        </div>

        @can('update', $location)
            <div class="col-md-12">
                <a href="{{ route('locations.edit', ['location' => $location->id]) }}" style="width: 100%;"
                    class="btn btn-sm btn-warning btn-social">
                    <x-icon type="edit" />
                    {{ trans('admin/locations/table.update') }}
                </a>
            </div>
        @endcan

        <div class="col-md-12" style="padding-top: 5px;">
            <a href="{{ route('locations.print_assigned', ['locationId' => $location->id]) }}" style="width: 100%;"
                class="btn btn-sm btn-primary btn-social hidden-print">
                <x-icon type="print" />
                {{ trans('admin/locations/table.print_assigned') }}
            </a>
        </div>
        <div class="col-md-12" style="padding-top: 5px;">
            <a href="{{ route('locations.print_all_assigned', ['locationId' => $location->id]) }}" style="width: 100%;"
                class="btn btn-sm btn-primary btn-social hidden-print">
                <x-icon type="print" />
                {{ trans('admin/locations/table.print_all_assigned') }}
            </a>
        </div>
        @can('update', \App\Models\Location::class)
<div class="col-md-12"  style="padding-top: 5px;">
          <a href="{{ route('locations.contract', ['locationId' => $location->id, 'contractType' => '1']) }}" style="width: 100%;color:white;" class="btn btn-sm btn-success pull-left">Ugovor V9 2023</a>
      </div>
      <div class="col-md-12"  style="padding-top: 5px;">
          <a href="{{ route('locations.contract', ['locationId' => $location->id,'contractType' => '2']) }}" style="width: 100%;" class="btn btn-sm btn-warning pull-left">Anex 2022</a>
      </div>
      <div class="col-md-12"  style="padding-top: 5px;">
          <a href="{{ route('locations.contract', ['locationId' => $location->id,'contractType' => '4']) }}" style="width: 100%;" class="btn btn-sm btn-warning pull-left">Anex 2017</a>
      </div>
      <div class="col-md-12"  style="padding-top: 5px;">
          <a href="{{ route('locations.contract', ['locationId' => $location->id, 'contractType' => '3']) }}" style="width: 100%;" class="btn btn-sm btn-danger">Raskid</a>
      </div>
  @endcan
        @can('delete', $location)
            <div class="col-md-12 hidden-print" style="padding-top: 10px;">

                @if ($location->deleted_at == '')

                    @if ($location->isDeletable())
                        <button class="btn btn-sm btn-block btn-danger btn-social delete-location" data-toggle="modal"
                            data-title="{{ trans('general.delete') }}"
                            data-content="{{ trans('general.sure_to_delete_var', ['item' => $location->name]) }}"
                            data-target="#dataConfirmModal">
                            <x-icon type="delete" />
                            {{ trans('general.delete') }}
                        </button>
                    @else
                        <a href="#" class="btn btn-block btn-sm btn-danger btn-social hidden-print disabled" data-tooltip="true"
                            data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}">
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

@can('update', \App\Models\Location::class)
    @include ('modals.upload-file', ['item_type' => 'location', 'item_id' => $location->id])
@endcan
		
  </div>

  </div>
</div>
@stop

@section('moar_scripts')

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
<script nonce="{{ csrf_token() }}">
$(function () {
    //binds to onchange event of your input field
    var uploadedFileSize = 0;
    $('#fileupload').bind('change', function() {
      uploadedFileSize = this.files[0].size;
      $('#progress-container').css('visibility', 'visible');
    });

    $('#fileupload').fileupload({
        //maxChunkSize: 100000,
        dataType: 'json',
        formData:{
        _token:'{{ csrf_token() }}',
        notes: $('#notes').val(),
        },

        progress: function (e, data) {
            //var overallProgress = $('#fileupload').fileupload('progress');
            //var activeUploads = $('#fileupload').fileupload('active');
            var progress = parseInt((data.loaded / uploadedFileSize) * 100, 10);
            $('.progress-bar').addClass('progress-bar-warning').css('width',progress + '%');
            $('#progress-bar-text').html(progress + '%');
            //console.dir(overallProgress);
        },

        done: function (e, data) {
            console.dir(data);
            // We use this instead of the fail option, since our API
            // returns a 200 OK status which always shows as "success"

            if (data && data.jqXHR && data.jqXHR.responseJSON && data.jqXHR.responseJSON.status === "error") {
                var errorMessage = data.jqXHR.responseJSON.messages["file.0"];
                $('#progress-bar-text').html(errorMessage[0]);
                $('.progress-bar').removeClass('progress-bar-warning').addClass('progress-bar-danger').css('width','100%');
                $('.progress-checkmark').fadeIn('fast').html('<i class="fas fa-times fa-3x icon-white" style="color: #d9534f"></i>');
            } else {
                $('.progress-bar').removeClass('progress-bar-warning').addClass('progress-bar-success').css('width','100%');
                $('.progress-checkmark').fadeIn('fast');
                $('#progress-container').delay(950).css('visibility', 'visible');
                $('.progress-bar-text').html('Finished!');
                $('.progress-checkmark').fadeIn('fast').html('<i class="fas fa-check fa-3x icon-white" style="color: green"></i>');
                $.each(data.result, function (index, file) {
                    $('<tr><td>' + file.note + '</td><<td>' + file.filename + '</td></tr>').prependTo("#files-table > tbody");
                });
            }
            $('#progress').removeClass('active');


        }
    });
    $("#optional_info").on("click",function(){
        $('#optional_details').fadeToggle(100);
        $('#optional_info_icon').toggleClass('fa-caret-right fa-caret-down');
        var optional_info_open = $('#optional_info_icon').hasClass('fa-caret-down');
        document.cookie = "optional_info_open="+optional_info_open+'; path=/';
    });
});
</script>

@stop
