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
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">


            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.checkedout-tab :item="$accessory" count="{{ $accessory->checkouts_count }}" />
                    <x-tabs.files-tab :item="$accessory" count="{{ $accessory->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $accessory->history()->count() }}" :model="$accessory"/>
                    <x-tabs.upload-tab :item="$accessory"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start assigned tab pane -->
                    <x-tabs.pane name="assigned">
                        <x-slot:table_header>
                            {{ trans('general.checked_out') }}
                        </x-slot:table_header>

                        <x-table
                            api_url="{{ route('api.accessories.checkedout', $accessory->id) }}"
                            :presenter="\App\Presenters\AccessoryPresenter::assignedDataTableLayout()"
                            export_filename="export-{{ str_slug($accessory->name) }}-assets-{{ date('Y-m-d') }}"
                        />

                    </x-tabs.pane>
                    <!-- end assigned tab pane -->

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$accessory" :route="route('api.accessories.history', $accessory)"/>
                    </x-tabs.pane>
                    <!-- end history tab pane -->

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="accessories" :object="$accessory"/>
                    </x-tabs.pane>
                    <!-- end files tab pane -->
                </x-slot:tabpanes>

            </x-tabs>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$accessory" img_path="{{ app('accessories_upload_url') }}">
                    <x-slot:buttons>
                        <x-button.edit :item="$accessory" :route="route('accessories.edit', $accessory->id)"/>
                        <x-button.clone :item="$accessory" :route="route('clone/accessories', $accessory->id)"/>
                        <x-button.checkout permission="checkout" :item="$accessory" :route="route('accessories.checkout.show', $accessory->id)" />
                        <x-button.delete :item="$accessory" />
                    </x-slot:buttons>
                </x-info-panel>
            </x-box>

        </x-page-column>
    </x-container>

@endsection



@section('moar_scripts')
    @can('files', $accessory)
        @include ('modals.upload-file', ['item_type' => 'accessories', 'item_id' => $accessory->id])
    @endcan

@include ('partials.bootstrap-table')
@endsection
