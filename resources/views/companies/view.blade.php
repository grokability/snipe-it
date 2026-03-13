@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $company->name }}
    @parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    @can('view', \App\Models\Asset::class)
                        <x-tabs.nav-item
                                class="active"
                                name="assets"
                                icon_type="asset"
                                label="{{ trans('general.assets') }}"
                                count="{{ $company->assets()->AssetsForShow()->count() }}"
                                tooltip="{{ trans('general.assets') }}"
                        />
                    @endcan

                    @can('view', \App\Models\License::class)
                        <x-tabs.nav-item
                                name="licenses"
                                icon_type="licenses"
                                label="{{ trans('general.licenses') }}"
                                count="{{ $company->licenses()->count() }}"
                                tooltip="{{ trans('general.licenses') }}"
                        />
                    @endcan

                    @can('view', \App\Models\Accessory::class)
                        <x-tabs.nav-item
                                name="accessories"
                                icon_type="accessories"
                                label="{{ trans('general.accessories') }}"
                                count="{{ $company->accessories()->count() }}"
                                tooltip="{{ trans('general.accessories') }}"
                        />
                    @endcan

                    @can('view', \App\Models\Consumable::class)
                        <x-tabs.nav-item
                                name="consumables"
                                icon_type="consumables"
                                label="{{ trans('general.consumables') }}"
                                count="{{ $company->consumables()->count() }}"
                                tooltip="{{ trans('general.consumables') }}"
                        />
                    @endcan

                    @can('view', \App\Models\Component::class)
                        <x-tabs.nav-item
                                name="components"
                                icon_type="components"
                                label="{{ trans('general.components') }}"
                                count="{{ $company->components()->count() }}"
                                tooltip="{{ trans('general.components') }}"
                        />
                    @endcan


                    @can('view', \App\Models\User::class)
                        <x-tabs.nav-item
                                name="users"
                                icon_type="users"
                                label="{{ trans('general.users') }}"
                                count="{{ $company->users()->count() }}"
                                tooltip="{{ trans('general.users') }}"
                        />
                    @endcan



                    @can('update', $company)
                        <x-tabs.nav-item-upload />
                    @endcan


                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    @can('view', \App\Models\Asset::class)
                        <x-tabs.pane name="assets" class="in active">
                            <x-slot:header>
                                {{ trans('general.assets') }}
                            </x-slot:header>

                            <x-slot:bulkactions>
                                <x-table.bulk-assets />
                            </x-slot:bulkactions>

                            <x-slot:content>
                                <x-table
                                        show_column_search="true"
                                        show_advanced_search="true"
                                        buttons="assetButtons"
                                        api_url="{{ route('api.assets.index', ['company_id' => $company->id]) }}"
                                        :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                                        export_filename="export-company-{{ str_slug($company->name) }}-assets-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                        <!-- end assets tab pane -->
                    @endcan


                    <!-- start licenses tab pane -->
                    @can('view', \App\Models\License::class)
                        <x-tabs.pane name="licenses">
                            <x-slot:header>
                                {{ trans('general.licenses') }}
                            </x-slot:header>
                            <x-slot:content>
                                <x-table
                                        name="licenses"
                                        buttons="licenseButtons"
                                        api_url="{{ route('api.licenses.index', ['company_id' => $company->id]) }}"
                                        :presenter="\App\Presenters\LicensePresenter::dataTableLayout()"
                                        export_filename="export-company-{{ str_slug($company->name) }}-licences-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end licenses tab pane -->


                    <!-- start accessory tab pane -->
                    @can('view', \App\Models\Accessory::class)
                        <x-tabs.pane name="accessories">
                            <x-slot:header>
                                {{ trans('general.accessories') }}
                            </x-slot:header>
                            <x-slot:content>
                                <x-table
                                        name="accessories"
                                        buttons="accessoryButtons"
                                        api_url="{{ route('api.accessories.index', ['company_id' => $company->id]) }}"
                                        :presenter="\App\Presenters\AccessoryPresenter::dataTableLayout()"
                                        export_filename="export-company-{{ str_slug($company->name) }}-accessories-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end accessory tab pane -->


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
                                        api_url="{{ route('api.consumables.index', ['company_id' => $company->id]) }}"
                                        :presenter="\App\Presenters\ConsumablePresenter::dataTableLayout()"
                                        export_filename="export-company-{{ str_slug($company->name) }}-consumables-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end components tab pane -->


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
                                        api_url="{{ route('api.components.index', ['company_id' => $company->id]) }}"
                                        :presenter="\App\Presenters\ComponentPresenter::dataTableLayout()"
                                        export_filename="export-company-{{ str_slug($company->name) }}-components-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end components tab pane -->


                    <!-- start user tab pane -->
                    @can('view', \App\Models\User::class)
                        <x-tabs.pane name="users">
                            <x-slot:header>
                                {{ trans('general.users') }}
                            </x-slot:header>

                            <x-slot:bulkactions>
                                <x-table.bulk-users />
                            </x-slot:bulkactions>


                            <x-slot:content>
                                <x-table
                                        name="users"
                                        buttons="userButtons"
                                        api_url="{{ route('api.users.index', ['company_id' => $company->id]) }}"
                                        :presenter="\App\Presenters\UserPresenter::dataTableLayout()"
                                        export_filename="export-company-{{ str_slug($company->name) }}-users-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end accessory tab pane -->

                </x-slot:tabpanes>

            </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$company" img_path="{{ app('companies_upload_url') }}">

                    <x-slot:before_list>

                        <x-button.edit :item="$company" :route="route('companies.edit', $company->id)" />
                        <x-button.delete :item="$company" />

                    </x-slot:before_list>


                </x-box.info-panel>
            </x-box>
        </x-page-column>
    </x-container>



@stop
@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop

