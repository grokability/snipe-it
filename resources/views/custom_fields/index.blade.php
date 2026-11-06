@extends('layouts/default', [
    'helpText' => trans('admin/custom_fields/general.about_fieldsets_text'),
    'helpPosition' => 'right',
])


{{-- Page title --}}
@section('title')
    {{ trans('admin/custom_fields/general.manage') }} {{ trans('admin/custom_fields/general.custom_fields') }}
    @parent
@stop

@section('content')
    <x-container>
        <x-tabs>
            <x-slot:tabnav>
                <x-tabs.nav-item
                    name="fields"
                    :label="trans('admin/custom_fields/general.custom_fields')"
                />
                <x-tabs.nav-item
                    name="fieldsets"
                    :label="trans('admin/custom_fields/general.fieldsets')"
                />
            </x-slot:tabnav>

            <x-slot:tabpanes>
                <x-tabs.pane name="fields">
                    <x-slot:table_header>{{ trans('admin/custom_fields/general.custom_fields') }}</x-slot:table_header>

                    <x-slot:bulkactions>
                        <x-table.bulk-customfields />
                    </x-slot:bulkactions>

                    <x-table
                        name="customField"
                        show_column_search="false"
                        show_advanced_search="true"
                        buttons="customFieldButtons"
                        fixed_right_number="1"
                        api_url="{{ route('api.customfields.index') }}"
                        :presenter="\App\Presenters\CustomFieldPresenter::dataTableLayout()"
                        export_filename="export-fields-{{ date('Y-m-d') }}"
                    />
                </x-tabs.pane>

                <x-tabs.pane name="fieldsets">
                    <x-slot:table_header>{{ trans('admin/custom_fields/general.fieldsets') }}</x-slot:table_header>

                    <x-slot:bulkactions>
                        <x-table.bulk-customfieldsets />
                    </x-slot:bulkactions>

                    <x-table
                        name="customFieldset"
                        show_column_search="false"
                        show_advanced_search="true"
                        buttons="customFieldsetButtons"
                        fixed_right_number="1"
                        api_url="{{ route('api.fieldsets.index') }}"
                        :presenter="\App\Presenters\CustomFieldsetPresenter::dataTableLayout()"
                        export_filename="export-fieldsets-{{ date('Y-m-d') }}"
                    />

                    {{-- Pre-render the clone-fieldset livewire modal for every
                         fieldset the user can clone. The AJAX actions column
                         emits a trigger button whose data-target matches the
                         corresponding #cloneFieldsetModal id. --}}
                    @foreach ($cloneable_fieldsets ?? [] as $cloneable)
                        @can('clone', $cloneable)
                            <livewire:clone-fieldset :id="$cloneable->id" triggerless :key="'clone-fieldset-'.$cloneable->id" />
                        @endcan
                    @endforeach
                </x-tabs.pane>
            </x-slot:tabpanes>
        </x-tabs>
        <x-shiftclick />
    </x-container>
@stop

@section('moar_scripts')
    @include('partials.bootstrap-table')

    <x-table.advanced-search
        tableId="customFieldListingTable"
        :model="\App\Models\CustomField::class"
    />

    <x-table.advanced-search
        tableId="customFieldsetListingTable"
        :model="\App\Models\CustomFieldset::class"
    />
@stop
