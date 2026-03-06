@extends('layouts/default')

{{-- Page title --}}
@section('title')
 {{ $component->name }}
 {{ trans('general.component') }}
@parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

@section('content')
<x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="assigned"
                            class="active"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $snipe_component->numCheckedOut() }}"
                    />

                    <x-tabs.files-tab count="{{ $snipe_component->uploads()->count() }}" />

                    <x-tabs.history-tab model="\App\Models\Component::class"/>

                    @can('update', $snipe_component)
                        <x-tabs.nav-item-upload />
                    @endcan

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assigned" class="in active">

                        <x-slot:content>
                            <x-table
                                    :presenter="\App\Presenters\ComponentPresenter::checkedOut()"
                                    :api_url="route('api.components.assets', $snipe_component)"
                            />
                        </x-slot:content>

                    </x-tabs.pane>

                    <x-tabs.pane name="files">
                        <x-slot:header>
                            {{ trans('general.files') }}
                        </x-slot:header>
                        <x-slot:content>
                            <x-filestable object_type="components" :object="$snipe_component" />
                        </x-slot:content>
                    </x-tabs.pane>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-slot:header>
                            {{ trans('general.history') }}
                        </x-slot:header>
                        <x-slot:content>
                            <x-table
                                    name="componentHistory"
                                    api_url="{{ route('api.activity.index', ['item_id' => $snipe_component->id, 'item_type' => 'component']) }}"
                                    :presenter="\App\Presenters\HistoryPresenter::dataTableLayout()"
                                    export_filename="export-licenses-{{ str_slug($snipe_component->name) }}-{{ date('Y-m-d') }}"
                            />
                        </x-slot:content>
                    </x-tabs.pane>
                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$snipe_component" img_path="{{ app('components_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.checkout :item="$snipe_component" :route="route('components.checkout.show', $snipe_component->id)" />
                        <x-button.edit :item="$snipe_component" :route="route('components.edit', $snipe_component->id)" />
                        <x-button.clone :item="$snipe_component" :route="route('components.clone.create', $snipe_component->id)" />
                        <x-button.delete :item="$snipe_component" />
                    </x-slot:buttons>

                </x-box.info-panel>
            </x-box>
        </x-page-column>
    </x-container>


@can('components.files', Component::class)
  @include ('modals.upload-file', ['item_type' => 'component', 'item_id' => $component->id])
@endcan
@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'component' . $component->name . '-export', 'search' => false])
@stop
