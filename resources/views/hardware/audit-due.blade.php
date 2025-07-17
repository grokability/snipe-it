@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans_choice('general.audit_due_days', $settings->audit_warning_days, ['days' => $settings->audit_warning_days]) }}
@stop



{{-- Page content --}}
@section('content')
    {{-- Page content --}}
    <div class="row">
        <div class="col-md-12">

            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs hidden-print">

                    <li class="active">
                        <a href="#due" data-toggle="tab">{{ trans('general.audit_due') }}
                          <span class="hidden-lg hidden-md">
                            <i class="far fa-file fa-2x" aria-hidden="true"></i>
                          </span>
                            <span class="badge">{{ (isset($total_due_for_audit)) ? $total_due_for_audit : '' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#overdue" data-toggle="tab">{{ trans('general.audit_overdue') }}
                            <span class="hidden-lg hidden-md">
                            <i class="far fa-file fa-2x" aria-hidden="true"></i>
                          </span>
                            <span class="badge">{{ (isset($total_overdue_for_audit)) ? $total_overdue_for_audit : '' }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <div class="tab-pane active" id="due">

                        @include('partials.asset-bulk-actions',
                            [
                                'id_divname'  => 'dueAssetEditToolbar',
                                'id_formname' => 'dueAssetEditForm',
                                'id_button'   => 'dueAssetEditButton'])

                    <div class="row">
                            <div class="table table-responsive">
                        <div class="col-md-12">
                            <table

                                    data-columns="{{ \App\Presenters\AssetAuditPresenter::dataTableLayout() }}"
                                    data-cookie-id-table="dueAssetAuditListing"
                                    data-id-table="dueAssetAuditListing"
                                    data-side-pagination="server"
                                    data-show-footer="true"
                                    data-sort-order="asc"
                                    data-sort-name="name"
                                    data-toolbar="#dueAssetEditToolbar"
                                    data-bulk-button-id="#dueAssetEditButton"
                                    data-bulk-form-id="#dueAssetEditForm"
                                    id="#dueAssetAuditListing"
                                    class="table table-striped snipe-table"
                                    data-url="{{ route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due']) }}"
                                    data-export-options='{
                "fileName": "export-assets-due-audit-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                            </table>
                                </div> <!-- end col-md-12 -->
                            </div><!-- end table-responsive -->
                        </div><!-- end row -->
                    </div><!-- end tab-pane -->

                    <div class="tab-pane" id="overdue">

                        @include('partials.asset-bulk-actions',
                                [
                                    'id_divname'  => 'overdueAssetEditToolbar',
                                    'id_formname' => 'overdueAssetEditForm',
                                    'id_button'   => 'overdueAssetEditButton'])

                        <div class="row">
                            <div class="table table-responsive">
                                <div class="col-md-12">
                                    <table
                                        data-columns="{{ \App\Presenters\AssetAuditPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="overdueAssetAuditListing"
                                        data-id-table="overdueAssetAuditListing"
                                        data-side-pagination="server"
                                        data-show-footer="true"
                                        data-sort-order="asc"
                                        data-sort-name="name"
                                        data-toolbar="#overdueAssetEditToolbar"
                                        data-bulk-button-id="#overdueAssetEditButton"
                                        data-bulk-form-id="#overdueAssetEditForm"
                                        id="#overdueAssetAuditListing"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'overdue']) }}"
                                        data-export-options='{
            "fileName": "export-assets-overdue-audit-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>
                                    </table>
                                </div> <!-- end col-md-12 -->
                            </div><!-- end table-responsive -->
                        </div><!-- end row -->
                    </div><!-- end tab-pane -->
                </div><!-- end tab-content -->
            </div><!-- end nav-tabs-custom -->

                        </div><!-- /.col -->
                    </div><!-- /.row -->

@stop

@section('moar_scripts')
    @include('partials.bootstrap-table')
@stop
