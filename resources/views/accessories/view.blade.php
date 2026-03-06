@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ $accessory->name }}
 {{ trans('general.accessory') }}
 @if ($accessory->model_number!='')
     ({{ $accessory->model_number }})
 @endif

@parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button"></i>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">

            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.checkedout-tab :item="$accessory" count="{{ $accessory->checkouts_count }}" />
                    <x-tabs.files-tab count="{{ $accessory->uploads()->count() }}" />
                    <x-tabs.history-tab model="\App\Models\Accessory::class"/>

                    @can('update', $accessory)
                        <x-tabs.nav-item-upload />
                    @endcan

                    <x-slot:tabpanes>

                        <!-- start assigned tab pane -->
                        <x-tabs.pane name="assigned" class="in active">
                            <x-slot:header>
                                {{ trans('general.checked_out') }}
                            </x-slot:header>
                            <x-slot:content>
                                <x-table
                                        api_url="{{ route('api.accessories.checkedout', $accessory->id) }}"
                                        :presenter="\App\Presenters\AccessoryPresenter::assignedDataTableLayout()"
                                        export_filename="export-{{ str_slug($accessory->name) }}-assets-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                        <!-- end assigned tab pane -->

                        <!-- start history tab pane -->
                        <x-tabs.pane name="history">
                            <x-slot:header>
                                {{ trans('general.history') }}
                            </x-slot:header>
                            <x-slot:content>
                                <x-table
                                        name="accessoryHistory"
                                        api_url="{{ route('api.activity.index', ['item_id' => $accessory->id, 'item_type' => 'accessory']) }}"
                                        :presenter="\App\Presenters\HistoryPresenter::dataTableLayout()"
                                        export_filename="export-accessory-{{ str_slug($accessory->name) }}-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                        <!-- end history tab pane -->

                        <!-- start files tab pane -->
                        @can('accessories.files', $accessory)
                            <x-tabs.pane name="files">
                                <x-slot:header>
                                    {{ trans('general.files') }}
                                </x-slot:header>
                                <x-slot:content>
                                    <x-filestable object_type="accessories" :object="$accessory" />
                                </x-slot:content>
                            </x-tabs.pane>
                        @endcan
                        <!-- end files tab pane -->



                    </x-slot:tabpanes>

                </x-slot:tabnav>
            </x-tabs>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$accessory" img_path="{{ app('accessories_upload_url') }}">
                    <x-slot:buttons>
                        <x-button.checkout permission="checkout" :item="$accessory" :route="route('accessories.checkout.show', $accessory->id)" />
                        <x-button.edit :item="$accessory" :route="route('accessories.edit', $accessory->id)" />
                        <x-button.clone :item="$accessory" :route="route('clone/accessories', $accessory->id)" />
                        <x-button.delete :item="$accessory" />
                    </x-slot:buttons>
                </x-box.info-panel>
            </x-box>

        </x-page-column>
    </x-container>



@can('accessories.files', Accessory::class)
    @include ('modals.upload-file', ['item_type' => 'accessory', 'item_id' => $accessory->id])
@endcan
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
