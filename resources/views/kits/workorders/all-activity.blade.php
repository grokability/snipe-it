@extends('layouts.default')

@section('title')
    All Work Orders Activity Log
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">
                        <i class="fas fa-stream"></i> All Work Orders Activity Log
                    </h2>
                </div>
            </div>

            <div class="box-body">
                <table
                    data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                    data-cookie-id-table="workOrdersAllActivity"
                    data-id-table="workOrdersAllActivity"
                    data-side-pagination="server"
                    data-advanced-search="false"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    id="workOrdersAllActivity"
                    data-url="{{ route('api.activity.index', ['item_type' => addslashes(App\Models\WorkOrder::class)]) }}"
                    class="table table-striped snipe-table"
                    data-export-options='{
                        "fileName": "all-workorders-activity-{{ date("Y-m-d") }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'workorders-all-activity-export', 'search' => true])
@stop
