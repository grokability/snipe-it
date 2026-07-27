@props([
    'route' => null,
    'name' => 'maintenances',
    'fixed_right_number' => 1,
    'fixed_number' => null,
    'table_header' => trans('general.maintenances'),
    'export_filename' => 'export-maintenances-'.date('Y-m-d'),
])

@aware(['name'])

@can('view', \App\Models\Asset::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-table
        :fixed_right_number="$fixed_right_number"
        :fixed_number="$fixed_number"
        buttons="maintenanceButtons"
        api_url="{{ $route ?? route('api.maintenances.index') }}"
        :presenter="\App\Presenters\MaintenancesPresenter::dataTableLayout()"
        :export_filename="$export_filename"
    />

@endcan
