@extends('layouts.default')

@section('title')
    Activity Log - Schedule: {{ $schedule->title }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">
                        <i class="fas fa-stream"></i> Activity Log - {{ $schedule->title }}
                    </h2>
                </div>
                <div class="box-tools pull-right">
                    <a href="{{ route('maintenance.scheduler.show', $schedule) }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Back to Schedule
                    </a>
                </div>
            </div>

            <div class="box-body">
                <table
                    data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                    data-cookie-id-table="scheduleActivity"
                    data-id-table="scheduleActivity"
                    data-side-pagination="server"
                    data-advanced-search="false"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    id="scheduleActivity"
                    data-url="{{ route('api.activity.index', ['item_id' => $schedule->id, 'item_type' => addslashes(App\Models\MaintenanceSchedule::class)]) }}"
                    class="table table-striped snipe-table"
                    data-export-options='{
                        "fileName": "schedule-{{ $schedule->id }}-activity-{{ date("Y-m-d") }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'schedule-activity-export', 'search' => true])
@stop
