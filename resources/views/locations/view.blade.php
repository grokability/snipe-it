@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ trans('general.location') }}:
 {{ $location->name }}
 
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
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


                  <x-tabs.asset-tab count="{{ $location->assets()->AssetsForShow()->count() }}"/>

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


                  <x-tabs.consumable-tab count="{{ $location->consumables()->count() }}"/>
                  <x-tabs.component-tab count="{{ $location->components()->count() }}"/>

                  <x-tabs.nav-item
                          name="child_locations"
                          icon="fa-solid fa-city fa-fw"
                          label="{{ trans('general.child_locations') }}"
                          count="{{ $location->children()->count() }}"
                          tooltip="{{ trans('general.child_locations') }}"
                  />

                      <x-tabs.files-tab :item="$location" count="{{ $location->uploads()->count() }}"/>
                      <x-tabs.history-tab count="{{ $location->history()->count() }}" :model="$location"/>
                      <x-tabs.upload-tab :item="$location"/>

              </x-slot:tabnav>

              <x-slot:tabpanes>

                  <!-- start users tab pane -->
                  @can('view', \App\Models\User::class)
                      <x-tabs.pane name="users">
                      <x-table.users :route="route('api.users.index',
                        [
                            'status' => e(request('status')),
                            'deleted'=> (request('status')=='deleted') ? 'true' : 'false',
                            'location_id' => $location->id,
                            'manager_id' => e(request('manager_id')),
                            'admins' => e(request('admins')),
                            'superadmins' => e(request('superadmins')),
                            'activated' => e(request('activated')),
                       ])"/>

                  </x-tabs.pane>
                  @endcan
                  <!-- end users tab pane -->

                  <!-- start assets tab pane -->
                  @can('view', \App\Models\Asset::class)
                      <x-tabs.pane name="assets">
                          <x-table.assets :table_header="trans('admin/locations/message.current_location')" :route="route('api.assets.index', ['location_id' => $location->id])"/>
                      </x-tabs.pane>
                      <!-- end assets tab pane -->

                      <!-- start assigned assets tab pane -->
                      <x-tabs.pane name="assets_assigned">
                          <x-table.assets :table_header="trans('admin/locations/message.assigned_assets')" :route="route('api.assets.index', ['assigned_to' => $location->id, 'assigned_type' => 'App\Models\Location'])"/>
                      </x-tabs.pane>
                      <!-- end assigned assets tab pane -->

                      <!-- start rtd assets tab pane -->
                      <x-tabs.pane name="rtd_assets">
                          <x-table.assets :table_header="trans('admin/hardware/form.default_location')" :route="route('api.assets.index', ['rtd_location_id' => $location->id]) "/>
                      </x-tabs.pane>
                  @endcan
                  <!-- end rtd assets tab pane -->


                  <!-- start accessories tab pane -->
                  @can('view', \App\Models\Accessory::class)
                  <x-tabs.pane name="accessories">
                      <x-table.accessories :route="route('api.accessories.index', ['location_id' => $location->id]) "/>
                  </x-tabs.pane>
                  <!-- end accessories tab pane -->

                  <!-- start assigned accessories tab pane -->
                  <x-tabs.pane name="accessories_assigned">

                      <x-table.accessories
                          :table_header="trans('general.accessories_assigned')"
                          :presenter="\App\Presenters\AccessoryPresenter::assignedDataTableLayoutForObject()"
                          :route="route('api.locations.assigned_accessories', ['location' => $location])  "/>

                  </x-tabs.pane>
                  @endcan
                  <!-- end assigned accessories tab pane -->


                  <!-- start consumables tab pane -->
                  @can('view', \App\Models\Consumable::class)
                  <x-tabs.pane name="consumables">
                      <x-table.consumables :route="route('api.consumables.index', ['location_id' => $location->id])  "/>
                  </x-tabs.pane>
                  @endcan
                  <!-- end consumables tab pane -->

                  <!-- start components tab pane -->
                  @can('view', \App\Models\Component::class)
                  <x-tabs.pane name="components">
                      <x-table.components :route="route('api.components.index', ['location_id' => $location->id]) "/>
                  </x-tabs.pane>
                  @endcan
                  <!-- end components tab pane -->

                  <!-- start child locations tab pane -->
                  <x-tabs.pane name="child_locations">
                      <x-table.locations :table_header="trans('general.child_locations')" :route="route('api.locations.index', ['parent_id' => $location->id]) "/>
                  </x-tabs.pane>
                  <!-- end components tab pane -->


                  <!-- start files tab pane -->
                  <x-tabs.pane name="files">
                      <x-table.files object_type="locations" :object="$location"/>
                  </x-tabs.pane>
                  <!-- end files tab pane -->

                  <!-- start history tab pane -->
                  <x-tabs.pane name="history">
                      <x-table.history :model="$location" :route="route('api.locations.history', $location)"/>
                  </x-tabs.pane>
                  <!-- end history tab pane -->

              </x-slot:tabpanes>
      </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$location" img_path="{{ app('locations_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$location" :route="route('locations.edit', $location->id)" />
                        <x-button.clone :item="$location" :route="route('clone/location', $location->id)" />
                        <x-button.restore :item="$location" :route="route('locations.restore', ['location' => $location->id])" />
                        <x-button.print :count="$location->countAllTheThings()" :tooltip="trans('admin/locations/table.print_inventory')" :item="$location" :route="route('locations.print_assigned', ['locationId' => $location->id])"/>
                        <x-button.print :count="$location->assignedAssets()->AssetsForShow()->count()" :item="$location" :route="route('locations.print_all_assigned', ['locationId' => $location->id])"/>
                        <x-button.delete :item="$location"/>
                    </x-slot:buttons>

                    @if ($location->ldap_ou)
                        <x-info-element icon_type="ldap">
                            {{ $location->ldap_ou }}
                        </x-info-element>
                    @endif


                </x-info-panel>
            </x-box>

        </x-page-column>
    </x-container>

@endsection


@section('moar_scripts')
    @can('files', $location)
        @include ('modals.upload-file', ['item_type' => 'locations', 'item_id' => $location->id])
    @endcan

    @include ('partials.bootstrap-table')
@endsection

