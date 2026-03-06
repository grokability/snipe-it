@aware(['fixed_right_number'])

<!-- start accessories tab pane -->
@can('view', \App\Models\Accessory::class)

        <x-slot:header>
            {{ trans('general.accessories') }}
        </x-slot:header>

        <x-slot:content>
            <x-table
                    show_column_search="true"
                    fixed_right_number="2"
                    fixed_number="1"
                    show_advanced_search="true"
                    buttons="accessoryButtons"
                    api_url="{{ $route }}"
                    :presenter="\App\Presenters\AccessoryPresenter::dataTableLayout()"
                    export_filename="export-{{ str_slug($name) }}-accessories-{{ date('Y-m-d') }}"
            />
        </x-slot:content>

@endcan
<!-- end accessories tab pane -->