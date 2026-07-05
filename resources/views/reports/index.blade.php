@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.reports') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    {{-- Row: Report Links --}}
    <div class="row" style="padding-bottom: 10px;">

        <div class="col-md-3 col-sm-6">
            <a href="{{ route('reports.activity') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="reports"/> {{ trans('general.activity_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ url('reports/custom') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="reports"/> {{ trans('general.custom_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ route('reports.custom.component') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="reports"/> {{ trans('general.custom_component_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ route('reports.audit') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="audit"/> {{ trans('general.audit_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ url('reports/depreciation') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="reports"/> {{ trans('general.depreciation_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ url('reports/licenses') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="licenses"/> {{ trans('general.license_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ route('ui.reports.maintenances') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="maintenances"/> {{ trans('general.asset_maintenance_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ url('reports/unaccepted_assets') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="assets"/> {{ trans('general.unaccepted_asset_report') }}
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{ url('reports/accessories') }}" class="btn btn-theme btn-block" style="margin-bottom: 10px; white-space: normal;">
                <x-icon type="accessories"/> {{ trans('general.accessory_report') }}
            </a>
        </div>

    </div>


    {{-- Date Range Control + Stat Alert Cards --}}
    <div class="row">
        <div class="col-md-12">
            <div class="well well-sm" style="margin-bottom:15px;">
                <form class="form-inline" style="margin-bottom: 10px; border-bottom: var(--box-header-bottom-border)">
                    <label style="font-weight:bold; white-space:nowrap;">{{ trans('general.time_range') }}:</label>
                    <select id="chartTimeRange" class="form-control input-sm" style="width:auto;">
                        <option value="7">{{ trans('general.last_n_days', ['days' => 7]) }}</option>
                        <option value="14">{{ trans('general.last_n_days', ['days' => 14]) }}</option>
                        <option value="30" selected>{{ trans('general.last_n_days', ['days' => 30]) }}</option>
                        <option value="60">{{ trans('general.last_n_days', ['days' => 60]) }}</option>
                        <option value="90">{{ trans('general.last_n_days', ['days' => 90]) }}</option>
                        <option value="180">{{ trans('general.last_n_days', ['days' => 180]) }}</option>
                        <option value="365">{{ trans('general.last_n_days', ['days' => 365]) }}</option>
                        <option value="custom">{{ trans('general.custom_range') }}…</option>
                    </select>
                    <div id="customRangePicker" class="input-daterange input-group" style="display:none; width:auto;">
                        <input type="text" id="chartStartDate" class="form-control input-sm" placeholder="{{ trans('general.select_date') }}" style="width:110px;" autocomplete="off">
                        <span class="input-group-addon">–</span>
                        <input type="text" id="chartEndDate" class="form-control input-sm" placeholder="{{ trans('general.select_date') }}" style="width:110px;" autocomplete="off">
                    </div>
                </form>

                <div class="row" style="margin-bottom:-15px;">
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ route('reports.audit') }}">
                            <div class="info-box {{ $audit_alert_count > 0 ? 'bg-red' : 'bg-green' }}">
                                    <span class="info-box-icon" aria-hidden="true">
                                        <x-icon type="audit"/>
                                    </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('general.audit_due') }} / {{ trans('general.audit_overdue') }}</span>
                                    <span class="info-box-number">{{ number_format($audit_alert_count) }}
                                            <span class="info-box-more" id="progress-audit-label">&nbsp;</span>
                                     </span>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" id="progress-audit" data-count="{{ $audit_alert_count }}" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>

                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ url('reports/custom') }}">
                            <div class="info-box {{ $checkin_alert_count > 0 ? 'bg-red' : 'bg-green' }}">
                                    <span class="info-box-icon " aria-hidden="true">
                                        <x-icon type="assets"/>
                                    </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('general.checkin_due') }} / {{ trans('general.checkin_overdue') }}</span>
                                    <span class="info-box-number">{{ number_format($checkin_alert_count) }}
                                        <span class="info-box-more" id="progress-checkin-label">&nbsp;</span>
                                    </span>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" id="progress-checkin" data-count="{{ $checkin_alert_count }}" style="width: 0%"></div>
                                    </div>

                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ route('reports/unaccepted_assets') }}">
                            <div class="info-box {{ $pending_acceptance_count > 0 ? 'bg-yellow' : 'bg-green' }}">
                                    <span class="info-box-icon" aria-hidden="true">
                                        <x-icon type="assets"/>
                                    </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('general.unaccepted_asset_report') }}</span>
                                    <span class="info-box-number">{{ number_format($pending_acceptance_count) }}
                                    <span class="info-box-more" id="progress-acceptance-label">&nbsp;</span>
                                    </span>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" id="progress-acceptance" data-count="{{ $pending_acceptance_count }}" style="width: 0%"></div>
                                    </div>

                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ url('reports/licenses') }}">
                            <div class="info-box {{ $licenses_low_count > 0 ? 'bg-red' : 'bg-green' }}">
                                    <span class="info-box-icon" aria-hidden="true">
                                        <x-icon type="licenses"/>
                                    </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('general.licenses_with_no_seats') }}</span>
                                    <span class="info-box-number">
                                        {{ number_format($licenses_low_count) }}
                                        <span class="info-box-more" id="progress-licenses-label">&nbsp;</span>
                                    </span>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" id="progress-licenses" data-count="{{ $licenses_low_count }}" style="width: 0%"></div>
                                    </div>

                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Row 1: Users + Assets --}}
    <div class="row">

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"><x-icon type="users" /> {{ trans('general.users') }}</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool chart-dl-btn" data-target="chart-users" title="{{ trans('general.download_chart') }}">
                            <i class="fa fa-download fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool chart-fs-btn" data-target="chart-users" title="{{ trans('general.fullscreen') }}">
                            <i class="fa fa-expand fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true"><x-icon type="minus" /></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-scroll">
                        <div class="chart-inner" id="wrap-chart-users" style="position:relative; height:184px; min-width:100%;">
                            <canvas id="chart-users"></canvas>
                        </div>
                    </div>
                    <p class="chart-prev-note" id="note-chart-users"></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"><x-icon type="assets" /> {{ trans('general.assets') }}</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool chart-dl-btn" data-target="chart-assets" title="{{ trans('general.download_chart') }}">
                            <i class="fa fa-download fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool chart-fs-btn" data-target="chart-assets" title="{{ trans('general.fullscreen') }}">
                            <i class="fa fa-expand fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true"><x-icon type="minus" /></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-scroll">
                        <div class="chart-inner" id="wrap-chart-assets" style="position:relative; height:184px; min-width:100%;">
                            <canvas id="chart-assets"></canvas>
                        </div>
                    </div>
                    <p class="chart-prev-note" id="note-chart-assets"></p>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 2: Components + Consumables --}}
    <div class="row">

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"><x-icon type="components" /> {{ trans('general.components') }}</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool chart-dl-btn" data-target="chart-components" title="{{ trans('general.download_chart') }}">
                            <i class="fa fa-download fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool chart-fs-btn" data-target="chart-components" title="{{ trans('general.fullscreen') }}">
                            <i class="fa fa-expand fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true"><x-icon type="minus" /></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-scroll">
                        <div class="chart-inner" id="wrap-chart-components" style="position:relative; height:184px; min-width:100%;">
                            <canvas id="chart-components"></canvas>
                        </div>
                    </div>
                    <p class="chart-prev-note" id="note-chart-components"></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"><x-icon type="consumables" /> {{ trans('general.consumables') }}</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool chart-dl-btn" data-target="chart-consumables" title="{{ trans('general.download_chart') }}">
                            <i class="fa fa-download fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool chart-fs-btn" data-target="chart-consumables" title="{{ trans('general.fullscreen') }}">
                            <i class="fa fa-expand fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true"><x-icon type="minus" /></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-scroll">
                        <div class="chart-inner" id="wrap-chart-consumables" style="position:relative; height:184px; min-width:100%;">
                            <canvas id="chart-consumables"></canvas>
                        </div>
                    </div>
                    <p class="chart-prev-note" id="note-chart-consumables"></p>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 3: Licenses + Accessories --}}
    <div class="row">

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"><x-icon type="licenses" /> {{ trans('general.licenses') }}</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool chart-dl-btn" data-target="chart-licenses" title="{{ trans('general.download_chart') }}">
                            <i class="fa fa-download fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool chart-fs-btn" data-target="chart-licenses" title="{{ trans('general.fullscreen') }}">
                            <i class="fa fa-expand fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true"><x-icon type="minus" /></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-scroll">
                        <div class="chart-inner" id="wrap-chart-licenses" style="position:relative; height:184px; min-width:100%;">
                            <canvas id="chart-licenses"></canvas>
                        </div>
                    </div>
                    <p class="chart-prev-note" id="note-chart-licenses"></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"><x-icon type="accessories" /> {{ trans('general.accessories') }}</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool chart-dl-btn" data-target="chart-accessories" title="{{ trans('general.download_chart') }}">
                            <i class="fa fa-download fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool chart-fs-btn" data-target="chart-accessories" title="{{ trans('general.fullscreen') }}">
                            <i class="fa fa-expand fa-fw"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true"><x-icon type="minus" /></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-scroll">
                        <div class="chart-inner" id="wrap-chart-accessories" style="position:relative; height:184px; min-width:100%;">
                            <canvas id="chart-accessories"></canvas>
                        </div>
                    </div>
                    <p class="chart-prev-note" id="note-chart-accessories"></p>
                </div>
            </div>
        </div>

    </div>

@stop


@push('css')
<style>
.info-box .info-box-text { text-transform: none; }

.info-box-more {
    display: inline;
    font-size: 70%;
    font-weight: normal;
    filter: brightness(95%);

}

/*[data-theme="dark"] .info-box { background: var(--box-bg); color: #d2d6de; }*/
/*[data-theme="dark"] .info-box .info-box-number,*/
/*[data-theme="dark"] .info-box .info-box-text,*/
/*[data-theme="dark"] .info-box .info-box-more { color: #d2d6de; }*/
.chart-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.chart-prev-note { font-size: 11px; font-style: italic; text-align: center; color: #999; margin: 4px 0 0; }

/* Fullscreen: expand the chart-inner div to fill the screen */
.chart-inner:fullscreen          { height: 100vh !important; width: 100vw !important; }
.chart-inner:-webkit-full-screen { height: 100vh !important; width: 100vw !important; }
.chart-inner:-moz-full-screen    { height: 100vh !important; width: 100vw !important; }
</style>
@endpush


@push('js')
<script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">

var charts = {};
var lastParams = { days: 30 };

function isDark() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

function applyChartTheme() {
    Chart.defaults.global.defaultFontColor = isDark() ? '#cccccc' : '#666666';
}

// Fill canvas with the box-body background so lines are visible in dark mode.
Chart.pluginService.register({
    beforeDraw: function (chart) {
        var el = chart.canvas.parentElement;
        while (el && !(el.classList && el.classList.contains('box-body'))) {
            el = el.parentElement;
        }
        var bg = el ? window.getComputedStyle(el).backgroundColor : null;
        if (!bg || bg === 'rgba(0, 0, 0, 0)' || bg === 'transparent') return;
        var ctx = chart.chart.ctx;
        ctx.save();
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, chart.chart.width, chart.chart.height);
        ctx.restore();
    },
});

function getLineOptions() {
    var dark = isDark();
    var fontColor = dark ? '#cccccc' : '#666666';
    var gridColor = dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
    return {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 12,
                fontColor: fontColor,
                // Hide the previous-period (dashed) series from the legend —
                // they're visible on the chart but would double the legend entries.
                filter: function (item, data) {
                    return !data.datasets[item.datasetIndex].isPrev;
                },
            },
        },
        scales: {
            xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 10, fontColor: fontColor } }],
            yAxes: [{ gridLines: { color: gridColor }, ticks: { beginAtZero: true, suggestedMin: 0, precision: 0, fontColor: fontColor } }]
        }
    };
}

function ds(label, data, color, isPrev) {
    return {
        label:            label,
        data:             data,
        borderColor:      color,
        backgroundColor:  color,
        borderWidth:      isPrev ? 1.5 : 2,
        borderDash:       isPrev ? [5, 4] : [],
        pointRadius:      isPrev ? 0 : 3,
        pointHoverRadius: isPrev ? 3 : 5,
        fill:             false,
        tension:          0.3,
        isPrev: isPrev,
    };
}

// series = [{ label, current, previous, color }, ...]
function makeChartMulti(id, labels, series, prevPeriod) {
    if (charts[id]) { charts[id].destroy(); }
    var wrap = document.getElementById('wrap-' + id);
    if (wrap) wrap.style.width = labels.length > 60 ? (labels.length * 8) + 'px' : '';
    var datasets = [];
    series.forEach(function (s) {
        datasets.push(ds(s.label, s.current, s.color, false));
        datasets.push(ds(s.label + ' (' + prevPeriod + ')', s.previous, hexToRgba(s.color, 0.5), true));
    });
    charts[id] = new Chart(document.getElementById(id), {
        type: 'line',
        data: {labels: labels, datasets: datasets},
        options: getLineOptions()
    });
    var note = document.getElementById('note-' + id);
    if (note) note.textContent = '- - - - = ' + prevPeriod;
}

function hexToRgba(hex, alpha) {
    var r = parseInt(hex.slice(1,3), 16);
    var g = parseInt(hex.slice(3,5), 16);
    var b = parseInt(hex.slice(5,7), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
}

function arrSum(arr) {
    return arr.reduce(function(a, b) { return a + b; }, 0);
}

function trendPct(cur, prev) {
    var c = arrSum(cur), p = arrSum(prev);
    return (c + p) === 0 ? 0 : Math.round(c / (c + p) * 100);
}

function setInfoBar(id, pct, prevLabel) {
    var bar = document.getElementById('progress-' + id);
    var count = bar ? parseInt(bar.getAttribute('data-count'), 10) : -1;
    var show = (count === 0) ? 0 : pct;
    $('#progress-' + id).css('width', show + '%');
    $('#progress-' + id + '-label').text('- ' + show + '% {!! trans('general.vs_prior_period') !!} (' + prevLabel + ')');
}

var palette = {
    light: {
        checkout: '#3c8dbc', checkin: '#00a65a', asset: '#f39c12',
        maintenance: '#dd4b39', audit: '#605ca8', component: '#39cccc',
        consumable: '#ff851b', license: '#d81b60', accessory: '#00c0ef',
    },
    dark: {
        checkout: '#60b0e0', checkin: '#2ecc71', asset: '#f5b942',
        maintenance: '#e74c3c', audit: '#9b97e0', component: '#4de8e8',
        consumable: '#ffa03a', license: '#f06292', accessory: '#29d8ff',
    },
};

function c(key) {
    return isDark() ? palette.dark[key] : palette.light[key];
}

function loadCharts(params) {
    lastParams = params;
    applyChartTheme();
    $.ajax({
        type: 'GET',
        url: '{{ route('api.reports.activity.chart') }}',
        data: params,
        headers: { "X-Requested-With": 'XMLHttpRequest', "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') },
        dataType: 'json',
        success: function(d) {
            var p = d.prev_label;

            // Info-box progress bars
            setInfoBar('audit',      trendPct(d.asset_checkouts,   d.prev_asset_checkouts),   p);
            setInfoBar('checkin',    trendPct(d.asset_checkins,    d.prev_asset_checkins),    p);
            setInfoBar('acceptance', trendPct(d.asset_checkouts,   d.prev_asset_checkouts),   p);
            setInfoBar('licenses',   trendPct(d.license_checkouts, d.prev_license_checkouts), p);

            makeChartMulti('chart-users', d.labels, [
                {
                    label: '{!! trans('general.created_plain') !!}',
                    current: d.new_users,
                    previous: d.prev_new_users,
                    color: c('checkin'),
                },
                {
                    label: '{!! trans('general.deleted_users') !!}',
                    current: d.deleted_users,
                    previous: d.prev_deleted_users,
                    color: c('maintenance'),
                },
            ], p);

            makeChartMulti('chart-assets', d.labels, [
                {
                    label: '{!! trans('general.checkouts') !!}',
                    current: d.asset_checkouts,
                    previous: d.prev_asset_checkouts,
                    color: c('checkout'),
                },
                {
                    label: '{!! trans('general.checkins') !!}',
                    current: d.asset_checkins,
                    previous: d.prev_asset_checkins,
                    color: c('checkin'),
                },
                {
                    label: '{!! trans('general.created_plain') !!}',
                    current: d.new_assets,
                    previous: d.prev_new_assets,
                    color: c('asset'),
                },
                {
                    label: '{!! trans('general.maintenances') !!}',
                    current: d.new_maintenances,
                    previous: d.prev_new_maintenances,
                    color: c('maintenance'),
                },
                {
                    label: '{!! trans('general.audits') !!}',
                    current: d.new_audits,
                    previous: d.prev_new_audits,
                    color: c('audit'),
                },
            ], p);

            makeChartMulti('chart-components', d.labels, [
                {
                    label: '{!! trans('general.checkouts') !!}',
                    current: d.component_checkouts,
                    previous: d.prev_component_checkouts,
                    color: c('checkout'),
                },
                {
                    label: '{!! trans('general.checkins') !!}',
                    current: d.component_checkins,
                    previous: d.prev_component_checkins,
                    color: c('checkin'),
                },
                {
                    label: '{!! trans('general.created_plain') !!}',
                    current: d.new_components,
                    previous: d.prev_new_components,
                    color: c('component'),
                },
            ], p);

            makeChartMulti('chart-consumables', d.labels, [
                {
                    label: '{!! trans('general.checkouts') !!}',
                    current: d.consumable_checkouts,
                    previous: d.prev_consumable_checkouts,
                    color: c('checkout'),
                },
                {
                    label: '{!! trans('general.checkins') !!}',
                    current: d.consumable_checkins,
                    previous: d.prev_consumable_checkins,
                    color: c('checkin'),
                },
                {
                    label: '{!! trans('general.created_plain') !!}',
                    current: d.new_consumables,
                    previous: d.prev_new_consumables,
                    color: c('consumable'),
                },
            ], p);

            makeChartMulti('chart-licenses', d.labels, [
                {
                    label: '{!! trans('general.checkouts') !!}',
                    current: d.license_checkouts,
                    previous: d.prev_license_checkouts,
                    color: c('checkout'),
                },
                {
                    label: '{!! trans('general.checkins') !!}',
                    current: d.license_checkins,
                    previous: d.prev_license_checkins,
                    color: c('checkin'),
                },
                {
                    label: '{!! trans('general.created_plain') !!}',
                    current: d.new_licenses,
                    previous: d.prev_new_licenses,
                    color: c('license'),
                },
            ], p);

            makeChartMulti('chart-accessories', d.labels, [
                {
                    label: '{!! trans('general.checkouts') !!}',
                    current: d.accessory_checkouts,
                    previous: d.prev_accessory_checkouts,
                    color: c('checkout'),
                },
                {
                    label: '{!! trans('general.checkins') !!}',
                    current: d.accessory_checkins,
                    previous: d.prev_accessory_checkins,
                    color: c('checkin'),
                },
                {
                    label: '{!! trans('general.created_plain') !!}',
                    current: d.new_accessories,
                    previous: d.prev_new_accessories,
                    color: c('accessory'),
                },
            ], p);
        }
    });
}

$('#chartTimeRange').on('change', function() {
    if ($(this).val() === 'custom') {
        $('#customRangePicker').css('display', 'flex');
    } else {
        $('#customRangePicker').hide();
        loadCharts({ days: $(this).val() });
    }
});

$('#customRangePicker').datepicker({
    clearBtn: true,
    todayHighlight: true,
    endDate: '0d',
    format: 'yyyy-mm-dd',
    keepEmptyValues: true,
});

$('#customRangePicker').on('changeDate', function() {
    var start = $('#chartStartDate').val();
    var end   = $('#chartEndDate').val();
    if (start && end && start <= end) {
        loadCharts({ start_date: start, end_date: end });
    }
});

$(document).on('click', '.chart-dl-btn', function() {
    var id    = $(this).data('target');
    var chart = charts[id];
    if (!chart) return;
    var a = document.createElement('a');
    a.href     = chart.toBase64Image();
    a.download = id + '.png';
    a.click();
});

$(document).on('click', '.chart-fs-btn', function() {
    var wrap = document.getElementById('wrap-' + $(this).data('target'));
    if (!wrap) return;
    var req = wrap.requestFullscreen || wrap.webkitRequestFullscreen || wrap.mozRequestFullScreen;
    if (req) req.call(wrap);
});

document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement) {
        $.each(charts, function(id, chart) { if (chart) chart.resize(); });
    }
});
document.addEventListener('webkitfullscreenchange', function() {
    if (!document.webkitFullscreenElement) {
        $.each(charts, function(id, chart) { if (chart) chart.resize(); });
    }
});

loadCharts({ days: 30 });

new MutationObserver(function () {
    loadCharts(lastParams);
}).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

</script>
@endpush
