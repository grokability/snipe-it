@extends('layouts.default')

@section('title')
    All Maintenance Schedules Activity Log
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">
                        <i class="fas fa-stream"></i> All Maintenance Schedules Activity Log
                    </h2>
                </div>
            </div>

            <div class="box-body">
                <table
                    data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                    data-cookie-id-table="schedulesAllActivity"
                    data-id-table="schedulesAllActivity"
                    data-side-pagination="server"
                    data-advanced-search="false"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    id="schedulesAllActivity"
                    data-url="{{ route('api.activity.index', ['item_type' => addslashes(App\Models\MaintenanceSchedule::class)]) }}"
                    class="table table-striped snipe-table"
                    data-export-options='{
                        "fileName": "all-schedules-activity-{{ date("Y-m-d") }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'schedules-all-activity-export', 'search' => true])
@stop
