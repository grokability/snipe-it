@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $department->name }}
    {{ trans('general.department') }}
    @parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-box>

                <x-slot:bulkactions>
                    <x-table.bulk-users />
                </x-slot:bulkactions>

                <x-table
                        show_column_search="true"
                        show_advanced_search="true"
                        fixed_right_number="1"
                        fixed_number="2"
                        buttons="licenseButtons"
                        api_url="{{ route('api.users.index', ['department_id' => $department->id]) }}"
                        :presenter="\App\Presenters\UserPresenter::dataTableLayout()"
                        export_filename="export-{{ str_slug($department->name) }}-users-{{ date('Y-m-d') }}"
                />
            </x-box>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$department" img_path="{{ app('users_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$department" :route="route('departments.edit', $department->id)" />
                        <x-button.delete :item="$department" />
                    </x-slot:buttons>

                </x-box.info-panel>
            </x-box>
        </x-page-column>


    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table',
    ['exportFile' => 'departments-users-export',
    'search' => true,
    'columns' => \App\Presenters\UserPresenter::dataTableLayout()
])

@stop
