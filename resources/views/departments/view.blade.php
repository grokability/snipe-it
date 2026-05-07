@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $department->name }}
    {{ trans('general.department') }}
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
                    <x-tabs.user-tab count="{{ $department->users->count() }}"/>
                    <x-tabs.files-tab :item="$department" count="{{ $department->uploads()->count() }}"/>
                    <x-tabs.upload-tab :item="$department"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>
                    <!-- start users tab pane -->
                    <x-tabs.pane name="users">
                        <x-table.users name="users" :route="route('api.users.index', ['department_id' => $department->id])"/>
                    </x-tabs.pane>
                    <!-- end users tab pane -->

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="departments" :object="$department"/>
                    </x-tabs.pane>
                    <!-- end files tab pane -->

                </x-slot:tabpanes>

            </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$department" img_path="{{ app('departments_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$department" :route="route('departments.edit', $department->id)"/>
                        <x-button.delete :item="$department"/>
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection

@section('moar_scripts')
    @can('files', $department)
        @include ('modals.upload-file', ['item_type' => 'departments', 'item_id' => $department->id])
    @endcan

    @include ('partials.bootstrap-table', ['exportFile' => 'department-' . $department->name . '-export', 'search' => false])
@endsection

