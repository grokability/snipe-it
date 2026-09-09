@extends('layouts/default')

@section('title')
    {{ trans('general.Expiring_Items_Report') }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs hidden-print">
                    <li class="active">
                        <a href="#asset" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <x-icon type="assets" class="fa-2x" />
                            </span>
                            <span class="hidden-xs hidden-sm">
                                {{ trans('general.assets') }}
                                {!! ($assets_count > 0 ) ? '<span class="badge badge-secondary">'.number_format($assets_count).'</span>' : '' !!}
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="#licenses" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <x-icon type="licenses" class="fa-2x" />
                            </span>
                            <span class="hidden-xs hidden-sm">
                                {{ trans('general.licenses') }}
                                {!! ($licenses_count > 0 ) ? '<span class="badge badge-secondary">'.number_format($licenses_count).'</span>' : '' !!}
                            </span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="asset">
                        <div class="box box-default">
                            <div class="box-body">
                                <table
                                        data-show-columns-search="true"
                                        data-cookie-id-table="expiringAssetsReport"
                                        data-id-table="expiringAssetsReport"
                                        data-side-pagination="server"
                                        data-sort-order="asc"
                                        data-sort-name="asset_eol_date"
                                        data-advanced-search="false"
                                        data-url="{{ route('api.expiring-assets') }}"
                                        id="expiringAssetsReport"
                                        data-fixed-number="false"
                                        data-fixed-right-number="false"
                                        class="table table-striped snipe-table"
                                        data-columns='{!! \App\Presenters\ExpiringItemsPresenter::assetsDataTableLayout() !!}'
                                        data-toolbar="#expiring-assets-toolbar"
                                        data-export-options='{
                                        "fileName": "expiring-assets-report-{{ date('Y-m-d') }}",
                                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                        }'>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="licenses">
                        <table
                                data-cookie-id-table="expiringLicensesReport"
                                data-id-table="expiringLicensesReport"
                                data-side-pagination="server"
                                data-sort-order="asc"
                                data-sort-name="expiration"
                                data-advanced-search="false"
                                data-url="{{ route('api.expiring-licenses') }}"
                                id="expiringLicensesReport"
                                data-fixed-number="false"
                                data-fixed-right-number="false"
                                class="table table-striped snipe-table"
                                data-columns='{!! \App\Presenters\ExpiringItemsPresenter::licensesDataTableLayout() !!}'
                                data-toolbar="#expiring-licenses-toolbar"
                                data-export-options='{
                                "fileName": "expiring-licenses-report-{{ date('Y-m-d') }}",
                                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                            }'>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['search' => true, 'show-export' => false,])
@endsection