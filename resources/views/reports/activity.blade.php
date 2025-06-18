@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.activity_report') }} 
@parent
@stop

@section('header_right')
    <form method="POST" action="{{ route('reports.activity.post') }}" accept-charset="UTF-8" class="form-horizontal">
    {{csrf_field()}}
    <button type="submit" class="btn btn-default">
        <x-icon type="download" />
        {{ trans('general.download_all') }}
    </button>
    </form>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-body">

                <table
                        data-advanced-search="true"
                        data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout($serial = true) }}"
                        data-cookie-id-table="activityReport"
                        data-pagination="true"
                        data-id-table="activityReport"
                        data-search="true"
                        data-show-fullscreen="true"
                        data-search-highlight="true"
                        data-show-print="true"
                        data-side-pagination="server"
                        data-show-columns="true"
                        data-show-export="true"
                        data-show-refresh="true"
                        data-sort-order="desc"
                        data-sort-name="created_at"
                        id="activityReport"
                        data-url="{{ route('api.activity.index') }}"
                        data-mobile-responsive="true"
                        class="table table-striped snipe-table"
                        data-export-options='{
                        "fileName": "activity-report-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                </table>
            </div>
        </div>
    </div>
</div>
@stop


@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'activity-export', 'search' => true])
@stop
