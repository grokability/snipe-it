@extends('layouts.default')

@section('title')
    Maintenance Activity Log
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">
                        <i class="fas fa-stream"></i> Maintenance Activity Log
                    </h2>
                </div>
            </div>

            <div class="box-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#all" aria-controls="all" role="tab" data-toggle="tab">
                            <i class="fas fa-list"></i> All Activities
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#workorders" aria-controls="workorders" role="tab" data-toggle="tab">
                            <i class="fas fa-clipboard-list"></i> Work Orders
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#schedules" aria-controls="schedules" role="tab" data-toggle="tab">
                            <i class="fas fa-calendar-alt"></i> Schedules
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- All Activities Tab -->
                    <div role="tabpanel" class="tab-pane active" id="all">
                        <div style="margin-top: 20px;">
                            <table
                                data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                                data-cookie-id-table="maintenanceAllActivity"
                                data-id-table="maintenanceAllActivity"
                                data-side-pagination="server"
                                data-advanced-search="false"
                                data-sort-order="desc"
                                data-sort-name="created_at"
                                id="maintenanceAllActivity"
                                data-url="{{ route('api.maintenance.activity.all') }}"
                                class="table table-striped snipe-table"
                                data-export-options='{
                                    "fileName": "maintenance-all-activity-{{ date("Y-m-d") }}",
                                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                }'>
                            </table>
                        </div>
                    </div>

                    <!-- Work Orders Tab -->
                    <div role="tabpanel" class="tab-pane" id="workorders">
                        <div style="margin-top: 20px;">
                            <table
                                data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                                data-cookie-id-table="workOrdersActivity"
                                data-id-table="workOrdersActivity"
                                data-side-pagination="server"
                                data-advanced-search="false"
                                data-sort-order="desc"
                                data-sort-name="created_at"
                                id="workOrdersActivity"
                                data-url="{{ route('api.activity.index') }}?item_type={{ urlencode('App\\Models\\WorkOrder') }}"
                                class="table table-striped snipe-table"
                                data-export-options='{
                                    "fileName": "workorders-activity-{{ date("Y-m-d") }}",
                                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                }'>
                            </table>
                        </div>
                    </div>

                    <!-- Schedules Tab -->
                    <div role="tabpanel" class="tab-pane" id="schedules">
                        <div style="margin-top: 20px;">
                            <table
                                data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                                data-cookie-id-table="schedulesActivity"
                                data-id-table="schedulesActivity"
                                data-side-pagination="server"
                                data-advanced-search="false"
                                data-sort-order="desc"
                                data-sort-name="created_at"
                                id="schedulesActivity"
                                data-url="{{ route('api.activity.index') }}?item_type={{ urlencode('App\\Models\\MaintenanceSchedule') }}"
                                class="table table-striped snipe-table"
                                data-export-options='{
                                    "fileName": "schedules-activity-{{ date("Y-m-d") }}",
                                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                }'>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'maintenance-activity-export', 'search' => true])
<script>
$(function() {
    // Initialize all tables when tabs are shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        $(target + ' table').bootstrapTable('resetView');
    });
});
</script>
@stop
