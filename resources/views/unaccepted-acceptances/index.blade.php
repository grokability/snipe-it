@extends('layouts/default')

@section('title')
    {{ trans('general.unaccepted_asset_report') }}
    @parent
@stop

@section('content')
    <x-container>
        <x-box name="unacceptedAcceptances" sr_only_title>
            <x-slot:table_header>
                {{ trans('general.unaccepted_asset_report') }}
            </x-slot:table_header>

            @unless ($showDeleted)
                <x-slot:bulkactions>
                    <x-table.bulk-acceptances/>
                </x-slot:bulkactions>
            @endunless

            {{-- Table is handrolled rather than using <x-table> because
                 the shared component locks data-side-pagination to
                 "server" whenever a data-url is set. Pending acceptances
                 are typically a small set, so the API returns the full
                 list in one shot and bs-table paginates / searches /
                 sorts in the browser. --}}
            <table
                role="table"
                aria-labelledby="unacceptedAcceptances-title"
                class="table table-striped snipe-table"
                data-cookie-id-table="unacceptedAcceptancesListingTable"
                data-id-table="unacceptedAcceptancesListingTable"
                data-sort-order="desc"
                data-sort-name="created_at"
                data-toolbar="#unacceptedAcceptancesToolbar"
                data-bulk-button-id="#unacceptedAcceptancesButton"
                data-bulk-form-id="#unacceptedAcceptancesForm"
                data-selected-count-id="#unacceptedAcceptancesSelectedCount"
                id="unacceptedAcceptancesListingTable"
                data-side-pagination="client"
                data-url="{{ route('api.unaccepted-acceptances.index', $showDeleted ? ['deleted' => 1] : []) }}"
                data-search="true"
                data-show-footer="true"
                data-show-export="true"
                data-show-columns="true"
                data-buttons="acceptanceButtons"
                data-columns="{{ \App\Presenters\CheckoutAcceptancePresenter::dataTableLayout() }}"
                data-export-options='{
                    "fileName": "export-unaccepted-acceptances-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","available_actions","checkbox"]
                }'>
            </table>
        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
