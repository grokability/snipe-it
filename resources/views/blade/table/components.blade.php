@props([
    'route' => null,
    'name' => 'default',
])

<!-- start components tab pane -->
@can('view', \App\Models\Component::class)
    <x-slot:header>
        {{ trans('general.components') }}
    </x-slot:header>

    <x-slot:content>
        <x-table
                show_column_search="true"
                show_advanced_search="true"
                buttons="componentButtons"
                api_url="{{ $route }}"
                :presenter="\App\Presenters\ComponentPresenter::dataTableLayout()"
                export_filename="export-{{ str_slug($name) }}-components-{{ date('Y-m-d') }}"
        />
    </x-slot:content>
@endcan
<!-- end components tab pane -->