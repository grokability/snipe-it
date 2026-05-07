@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $company->name }}
    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.user-tab count="{{ $company->users->count() }}"/>
                    <x-tabs.asset-tab count="{{ $company->assets()->AssetsForShow()->count() }}"/>
                    <x-tabs.license-tab count="{{ $company->licenses->count() }}"/>
                    <x-tabs.accessory-tab count="{{ $company->accessories->count() }}"/>
                    <x-tabs.consumable-tab count="{{ $company->consumables->count() }}"/>
                    <x-tabs.component-tab count="{{ $company->components->count() }}"/>
                    <x-tabs.files-tab :item="$company" count="{{ $company->uploads()->count() }}"/>
                    <x-tabs.upload-tab :item="$company"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>
                    <!-- start users tab pane -->
                    <x-tabs.pane name="users">
                        <x-table.users name="users" :route="route('api.users.index', ['company_id' => $company->id])"/>
                    </x-tabs.pane>
                    <!-- end users tab pane -->

                    <!-- start assets tab pane -->
                    <x-tabs.pane name="assets">
                        <x-table.assets name="assets" :route="route('api.assets.index', ['company_id' => $company->id])"/>
                    </x-tabs.pane>
                    <!-- end assets tab pane -->

                    <!-- start licenses tab pane -->
                    <x-tabs.pane name="licenses">
                        <x-table.licenses name="licenses" :route="route('api.licenses.index', ['company_id' => $company->id])"/>
                    </x-tabs.pane>
                    <!-- end licenses tab pane -->

                    <!-- start accessory tab pane -->
                    <x-tabs.pane name="accessories">
                        <x-table.accessories name="accessories" :route="route('api.accessories.index', ['company_id' => $company->id])"/>
                    </x-tabs.pane>
                    <!-- end accessory tab pane -->

                    <!-- start consumables tab pane -->
                    <x-tabs.pane name="consumables">
                        <x-table.consumables name="consumables" :route="route('api.consumables.index', ['company_id' => $company->id])"/>
                    </x-tabs.pane>
                    <!-- end components tab pane -->

                    <!-- start components tab pane -->
                    <x-tabs.pane name="components">
                        <x-table.components name="components" :route="route('api.components.index', ['company_id' => $company->id])"/>
                    </x-tabs.pane>

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="companies" :object="$company"/>
                    </x-tabs.pane>
                    <!-- end files tab pane -->

                </x-slot:tabpanes>

            </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$company" img_path="{{ app('companies_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$company" :route="route('companies.edit', $company->id)" />
                        <x-button.delete :item="$company" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection

@section('moar_scripts')
    @can('files', $company)
        @include ('modals.upload-file', ['item_type' => 'companies', 'item_id' => $company->id])
    @endcan
    @include ('partials.bootstrap-table')
@endsection

