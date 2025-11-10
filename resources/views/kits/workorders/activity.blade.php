@extends('layouts.default')

@section('title')
    Activity Log - Work Order: {{ $workorder->work_order_number }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">
                        <i class="fas fa-stream"></i> Activity Log - {{ $workorder->work_order_number }}: {{ $workorder->title }}
                    </h2>
                </div>
                <div class="box-tools pull-right">
                    <a href="{{ route('maintenance.workorders.show', $workorder) }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Back to Work Order
                    </a>
                </div>
            </div>

            <div class="box-body">
                <table
                    data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                    data-cookie-id-table="workOrderActivity"
                    data-id-table="workOrderActivity"
                    data-side-pagination="server"
                    data-advanced-search="false"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    id="workOrderActivity"
                    data-url="{{ route('api.activity.index', ['item_id' => $workorder->id, 'item_type' => addslashes(App\Models\WorkOrder::class)]) }}"
                    class="table table-striped snipe-table"
                    data-export-options='{
                        "fileName": "workorder-{{ $workorder->work_order_number }}-activity-{{ date("Y-m-d") }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'workorder-activity-export', 'search' => true])
@stop
