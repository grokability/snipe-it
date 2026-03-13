@props([
    'presenter' => null,
    'buttons' => null,
    'export_filename' => 'export-'.date('Y-m-d'),
    'api_url' => null,
    'show_column_search' => false,
    'show_advanced_search' => false,
    'fixed_number' => false,
    'fixed_right_number' => false,
    'sort_order' => 'asc',
    'sort_field' => 'name',

])

@aware(['name'])

<table
    role="table"
    class="table table-striped snipe-table"
    data-cookie-id-table="{{ $name }}ListingTable"
    data-id-table="{{ $name }}ListingTable"
    data-sort-order="{{ $sort_order }}"
    data-toolbar="#{{ Illuminate\Support\Str::camel($name) }}Toolbar"
    data-bulk-button-id="#{{ Illuminate\Support\Str::camel($name) }}Button"
    data-bulk-form-id="#{{ Illuminate\Support\Str::camel($name) }}Form"
    id="{{ $name }}ListingTable"
    data-show-columns-search="{{ $show_column_search }}"
    data-show-advanced-search="{{ $show_advanced_search }}"
    data-footer-style="footerStyle"
    data-show-footer="true"

    @if ($presenter)
        data-columns="{{ $presenter }}"
    @endif

    @if ($fixed_number)
        data-fixed-number="{{ $fixed_number }}"
    @endif

    @if ($fixed_right_number)
        data-fixed-right-number="{{ $fixed_right_number }}"
    @endif

    @if ($buttons)
        data-buttons="{{ $buttons }}"
    @endif

    @if ($api_url)
        data-side-pagination="server"
        data-url="{!!  $api_url !!}"
    @endif

    data-export-options='{
        "fileName": "{{ $export_filename }}",
        "ignoreColumn": ["actions","available_actions", "image","change","checkbox","checkincheckout","icon"]
    }'>
</table>
