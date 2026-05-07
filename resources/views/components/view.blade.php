@extends('layouts/default')

{{-- Page title --}}
@section('title')
 {{ $component->name }}
 {{ trans('general.component') }}
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

@section('content')
<x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="assigned"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $snipe_component->numCheckedOut() }}"
                    />

                    <x-tabs.files-tab :item="$snipe_component" count="{{ $snipe_component->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $snipe_component->history()->count() }}" :model="$snipe_component"/>
                    <x-tabs.upload-tab :item="$snipe_component"/>

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assigned">

                        <x-slot:table_header>
                            {{ trans('general.assigned') }}
                        </x-slot:table_header>

                        <x-table
                            :presenter="\App\Presenters\ComponentPresenter::checkedOut()"
                            :api_url="route('api.components.assets', $snipe_component)"
                        />

                    </x-tabs.pane>

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="components" :object="$snipe_component"/>
                    </x-tabs.pane>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$snipe_component" :route="route('api.components.history', $snipe_component)"/>
                    </x-tabs.pane>

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$snipe_component" img_path="{{ app('components_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$snipe_component" :route="route('components.edit', $snipe_component->id)"/>
                        <x-button.clone :item="$snipe_component" :route="route('components.clone.create', $snipe_component->id)"/>
                        <x-button.checkout :item="$snipe_component" :route="route('components.checkout.show', $snipe_component->id)" />
                        <x-button.delete :item="$snipe_component" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection



@section('moar_scripts')
    @can('files', $snipe_component)
        @include ('modals.upload-file', ['item_type' => 'components', 'item_id' => $snipe_component->id])
    @endcan
    @include ('partials.bootstrap-table', ['exportFile' => 'component' . $snipe_component->name . '-export', 'search' => false])
@endsection
