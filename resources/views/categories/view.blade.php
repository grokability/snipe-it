@extends('layouts/default')

{{-- Page title --}}
@section('title')

{{ $category->name }}

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
                    @if ($category->category_type=='asset')
                        <x-tabs.asset-tab count="{{ $category->showableAssets()->count() }}"/>
                        <x-tabs.model-tab count="{{ $category->models->count() }}"/>
                    @elseif ($category->category_type=='accessory')
                        <x-tabs.accessory-tab count="{{ $category->accessories->count() }}"/>
                    @elseif ($category->category_type=='license')
                        <x-tabs.license-tab count="{{ $category->licenses->count() }}"/>
                    @elseif ($category->category_type=='consumable')
                        <x-tabs.consumable-tab count="{{ $category->consumables->count() }}"/>
                    @elseif ($category->category_type=='component')
                        <x-tabs.component-tab count="{{ $category->components->count() }}"/>
                    @endif

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    @if ($category->category_type=='asset')
                        @can('view', \App\Models\Asset::class)
                            <x-tabs.pane name="assets">
                                <x-table.assets :route="route('api.assets.index', ['category_id' => $category->id, 'itemtype' => 'assets'])"/>
                            </x-tabs.pane>
                        @endcan

                            @can('view', \App\Models\AssetModel::class)
                            <x-tabs.pane name="models">
                                <x-table.models :route="route('api.models.index', ['status' => e(request('status')), 'category_id' => $category->id])"/>
                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='license')
                        @can('view', \App\Models\License::class)
                            <x-tabs.pane name="licenses">
                                <x-table.licenses
                                    show_footer="true"
                                    name="licenses"
                                    :route="route('api.licenses.index', ['category_id' => $category->id])"/>
                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='accessory')
                        @can('view', \App\Models\Accessory::class)
                            <x-tabs.pane name="accessories">
                                <x-table.accessories name="accessories" :route="route('api.accessories.index', ['category_id' => $category->id])"/>
                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='consumable')
                        @can('view', \App\Models\Consumable::class)
                            <x-tabs.pane name="consumables">
                                <x-table.consumables :route="route('api.consumables.index', ['category_id' => $category->id])"/>
                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='component')
                        @can('view', \App\Models\Component::class)
                            <x-tabs.pane name="components">
                                <x-table.components :route="route('api.components.index', ['category_id' => $category->id])" />
                            </x-tabs.pane>
                        @endcan
                    @endif
                    <!-- end assets tab pane -->

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
    <x-page-column class="col-md-3">

        <x-box class="side-box expanded">
            <x-info-panel :infoPanelObj="$category" img_path="{{ app('categories_upload_url') }}">

                <x-slot:buttons>
                    <x-button.edit :item="$category" :route="route('categories.edit', $category->id)" />
                    <x-button.delete :item="$category" />
                </x-slot:buttons>

            </x-info-panel>
        </x-box>
    </x-page-column>
    </x-container>

@endsection
@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
