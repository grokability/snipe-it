@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ trans('general.location') }}:
 {{ $location->name }}
 
@parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection


{{-- Page content --}}
@section('content')
    <x-container columns="2">

        @if ($location->deleted_at!='')
            <div class="col-md-12">
                <div class="callout callout-warning">
                    <x-icon type="warning" />
                    {{ trans('admin/locations/message.deleted_warning') }}
                </div>
            </div>
        @endif


        <x-page-column class="col-md-9 main-panel">
          <x-tabs>

              <x-slot:tabnav>
                  @can('view', \App\Models\User::class)
                      <x-tabs.nav-item
                              class="active"
                              name="users"
                              icon="fa-solid fa-house-user fa-fw"
                              label="{{ trans('general.users') }}"
                              count="{{ $location->users()->count() }}"
                              tooltip="{{ trans('general.users') }}"
                      />
                  @endcan

                  @can('view', \App\Models\Asset::class)

                      <x-tabs.nav-item
                              name="assets"
                              icon="fa-solid fa-house-laptop fa-fw"
                              label="{{ trans('general.assets') }}"
                              count="{{ $location->assets()->AssetsForShow()->count() }}"
                              tooltip="{{ trans('admin/locations/message.current_location') }}"
                      />

                      <x-tabs.nav-item
                              name="rtd_assets"
                              icon="fa-solid fa-house-flag fa-fw"
                              label="{{ trans('admin/hardware/form.default_location') }}"
                              count="{{ $location->rtd_assets()->AssetsForShow()->count() }}"
                              tooltip="{{ trans('admin/hardware/form.default_location') }}"
                      />

                      <x-tabs.nav-item
                              name="assets_assigned"
                              icon="fas fa-barcode fa-fw"
                              label="{{ trans('admin/locations/message.assigned_assets') }}"
                              count="{{ $location->assignedAssets()->AssetsForShow()->count() }}"
                              tooltip="{{ trans('admin/locations/message.assigned_assets') }}"
                      />

                  @endcan

                  @can('view', \App\Models\Accessory::class)

                      <x-tabs.nav-item
                              name="accessories"
                              icon="far fa-keyboard fa-fw"
                              label="{{ trans('general.accessories') }}"
                              count="{{ $location->accessories()->count() }}"
                              tooltip="{{ trans('general.accessories') }}"
                      />

                      <x-tabs.nav-item
                              name="accessories_assigned"
                              icon="fas fa-keyboard fa-fw"
                              label="{{ trans('general.accessories_assigned') }}"
                              count="{{ $location->assignedAccessories()->count() }}"
                              tooltip="{{ trans('general.accessories_assigned') }}"
                      />

                  @endcan


                  @can('view', \App\Models\Consumable::class)

                      <x-tabs.nav-item
                              name="consumables"
                              icon="fas fa-tint fa-fw"
                              label="{{ trans('general.consumables') }}"
                              count="{{ $location->consumables()->count() }}"
                              tooltip="{{ trans('general.consumables') }}"
                      />

                  @endcan

                  @can('view', \App\Models\Component::class)

                      <x-tabs.nav-item
                              name="components"
                              icon="fas fa-hdd fa-fw"
                              label="{{ trans('general.components') }}"
                              count="{{ $location->components->count() }}"
                              tooltip="{{ trans('general.components') }}"
                      />

                  @endcan

                  <x-tabs.nav-item
                          name="child_locations"
                          icon="fa-solid fa-city fa-fw"
                          label="{{ trans('general.child_locations') }}"
                          count="{{ $location->children()->count() }}"
                          tooltip="{{ trans('general.child_locations') }}"
                  />

                  <x-tabs.nav-item
                          name="files"
                          icon="fa-solid fa-file-contract fa-fw"
                          label="{{ trans('general.files') }}"
                          count="{{ $location->uploads()->count() }}"
                          tooltip="{{ trans('general.files') }}"
                  />

                  <x-tabs.nav-item
                          name="history"
                          icon="fa-solid fa-clock-rotate-left fa-fw"
                          label="{{ trans('general.history') }}"
                          tooltip="{{ trans('general.history') }}"
                  />

                  @can('update', $location)
                      <x-tabs.nav-item-upload />
                  @endcan

              </x-slot:tabnav>

              <x-slot:tabpanes>

                  <!-- start users tab pane -->
                  @can('view', \App\Models\User::class)
                  <x-tabs.pane name="users" class="in active">
                      <x-slot:header>
                          {{ trans('general.users') }}
                      </x-slot:header>

                      <x-slot:bulkactions>
                          <x-table.bulk-users />
                      </x-slot:bulkactions>

                      <x-slot:content>
                          <x-table
                            show_column_search="true"
                            show_advanced_search="true"
                            name="users"
                            buttons="userButtons"
                            toolbar_id="userBulkEditToolbar"
                            api_url="{{ route('api.users.index', ['location_id' => $location->id])}}"
                            :presenter="\App\Presenters\UserPresenter::dataTableLayout()"
                            export_filename="export-locations-{{ str_slug($location->name) }}-users-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>

                  </x-tabs.pane>
                  @endcan
                  <!-- end users tab pane -->

                  <!-- start assets tab pane -->
                  @can('view', \App\Models\Asset::class)
                  <x-tabs.pane name="assets">
                      <x-slot:header>
                          {{ trans('admin/locations/message.current_location') }}
                      </x-slot:header>

                      <x-slot:bulkactions>
                         <x-table.bulk-assets />
                      </x-slot:bulkactions>

                      <x-slot:content>
                          <x-table
                              show_column_search="true"
                              show_advanced_search="true"
                              buttons="assetButtons"
                              api_url="{{ route('api.assets.index', ['location_id' => $location->id]) }}"
                              :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                              export_filename="export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>

                  </x-tabs.pane>
                  <!-- end assets tab pane -->


                  <!-- start assigned assets tab pane -->


                  <x-tabs.pane name="assets_assigned">
                      <x-slot:header>
                          {{ trans('admin/locations/message.assigned_assets') }}
                      </x-slot:header>

                      <x-slot:bulkactions>
                          <x-table.bulk-assets />
                      </x-slot:bulkactions>

                      <x-slot:content>
                          <x-table
                                  show_column_search="true"
                                  show_advanced_search="true"
                                  buttons="assetButtons"
                                  :api_url="route('api.assets.index', ['assigned_to' => $location->id, 'assigned_type' => 'App\Models\Location'])"
                                  :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                                  export_filename="export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>
                  </x-tabs.pane>
                 <!-- end assigned assets tab pane -->



                  <!-- start rtd assets tab pane -->
                  <x-tabs.pane name="rtd_assets">
                      <x-slot:header>
                          {{ trans('admin/hardware/form.default_location') }}
                      </x-slot:header>

                      <x-slot:bulkactions>
                          <x-table.bulk-assets />
                      </x-slot:bulkactions>

                      <x-slot:content>
                          <x-table
                            buttons="assetButtons"
                            api_url="{{ route('api.assets.index', ['rtd_location_id' => $location->id]) }}"
                            :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                            export_filename="export-rtd-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>

                  </x-tabs.pane>
                  @endcan
                  <!-- end rtd assets tab pane -->


                  <!-- start accessories tab pane -->
                  @can('view', \App\Models\Accessory::class)
                  <x-tabs.pane name="accessories">
                      <x-slot:header>
                          {{ trans('general.accessories') }}
                      </x-slot:header>

                      <x-slot:content>
                          <x-table
                            name="accessories"
                            buttons="accessoryButtons"
                            api_url="{{ route('api.accessories.index', ['location_id' => $location->id]) }}"
                            :presenter="\App\Presenters\AccessoryPresenter::dataTableLayout()"
                            export_filename="export-locations-{{ str_slug($location->name) }}-accessories-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>

                  </x-tabs.pane>
                  <!-- end accessories tab pane -->

                  <!-- start assigned accessories tab pane -->
                  <x-tabs.pane name="accessories_assigned">
                      <x-slot:header>
                          {{ trans('general.accessories_assigned') }}
                      </x-slot:header>

                      <x-slot:content>
                          <x-table
                                  name="accessoriesAssigned"
                                  buttons="accessoryButtons"
                                  api_url="{{ route('api.locations.assigned_accessories', ['location' => $location]) }}"
                                  :presenter="\App\Presenters\AccessoryPresenter::dataTableLayout()"
                                  export_filename="export-locations-{{ str_slug($location->name) }}-accessories-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>

                  </x-tabs.pane>
                  @endcan
                  <!-- end assigned accessories tab pane -->


                  <!-- start consumables tab pane -->
                  @can('view', \App\Models\Consumable::class)
                  <x-tabs.pane name="consumables">
                      <x-slot:header>
                          {{ trans('general.consumables') }}
                      </x-slot:header>

                      <x-slot:content>
                          <x-table
                                  name="consumables"
                                  buttons="consumableButtons"
                                  api_url="{{ route('api.consumables.index', ['location_id' => $location->id]) }}"
                                  :presenter="\App\Presenters\ConsumablePresenter::dataTableLayout()"
                                  export_filename="export-locations-{{ str_slug($location->name) }}-consumables-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>

                  </x-tabs.pane>
                  @endcan
                  <!-- end consumables tab pane -->

                  <!-- start components tab pane -->
                  @can('view', \App\Models\Component::class)
                  <x-tabs.pane name="components">
                      <x-slot:header>
                          {{ trans('general.components') }}
                      </x-slot:header>
                      <x-slot:content>
                          <x-table
                                  name="components"
                                  buttons="componentButtons"
                                  api_url="{{ route('api.components.index', ['location_id' => $location->id]) }}"
                                  :presenter="\App\Presenters\ComponentPresenter::dataTableLayout()"
                                  export_filename="export-locations-{{ str_slug($location->name) }}-consumables-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>
                  </x-tabs.pane>
                  @endcan
                  <!-- end components tab pane -->

                  <!-- start child locations tab pane -->
                  <x-tabs.pane name="child_locations">
                      <x-slot:header>
                          {{ trans('general.child_locations') }}
                      </x-slot:header>
                      <x-slot:content>
                          <x-table
                                  name="childrenListingTable"
                                  buttons="locationButtons"
                                  api_url="{{ route('api.locations.index', ['parent_id' => $location->id]) }}"
                                  :presenter="\App\Presenters\LocationPresenter::dataTableLayout()"
                                  export_filename="export-children-locations-{{ str_slug($location->name) }}-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>
                  </x-tabs.pane>
                  <!-- end components tab pane -->


                  <!-- start files tab pane -->
                  <x-tabs.pane name="files">
                      <x-slot:header>
                          {{ trans('general.files') }}
                      </x-slot:header>
                      <x-slot:content>
                          <x-filestable object_type="locations" :object="$location" />
                      </x-slot:content>
                  </x-tabs.pane>
                  <!-- end files tab pane -->

                  <!-- start history tab pane -->
                  <x-tabs.pane name="history">
                      <x-slot:header>
                          {{ trans('general.history') }}
                      </x-slot:header>
                      <x-slot:content>
                          <x-table
                                  name="locationHistory"
                                  api_url="{{ route('api.activity.index', ['item_id' => $location->id, 'item_type' => 'location']) }}"
                                  :presenter="\App\Presenters\HistoryPresenter::dataTableLayout()"
                                  export_filename="export-locations-history-{{ str_slug($location->name) }}-{{ date('Y-m-d') }}"
                          />
                      </x-slot:content>
                  </x-tabs.pane>
                  <!-- end history tab pane -->

              </x-slot:tabpanes>
      </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$location" img_path="{{ app('locations_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$location" :route="route('locations.edit', $location->id)" />
                        <x-button.clone :item="$location" :route="route('clone/location', $location->id)" />
                        <x-button.delete :item="$location" />
                        <x-button.restore :item="$location" :route="route('locations.restore', ['location' => $location->id])" />
                    </x-slot:buttons>

                @if ($location->ldap_ou)
                        <x-info-element icon_type="ldap">
                            {{ $location->ldap_ou }}
                        </x-info-element>
                    @endif


                </x-box.info-panel>
            </x-box>

        </x-page-column>
    </x-container>

@stop

@can('update', Location::class)
    @section('moar_scripts')
       @include ('modals.upload-file', ['item_type' => 'locations', 'item_id' => $location->id])
    @endsection
@endcan

@include ('partials.bootstrap-table', [
'exportFile' => 'locations-export',
'search' => true
])


