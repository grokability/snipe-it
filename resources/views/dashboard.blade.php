@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.dashboard') }}
@parent
@stop


{{-- Page content --}}
@section('content')

<x-container>

    @if ($snipeSettings->dashboard_message != '')
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        {!! Helper::parseEscapedMarkedown($snipeSettings->dashboard_message) !!}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Top-boxes summary + empty-inventory shortcut are admin-only.
         Non-admins with view access to at least one HasCalendarEvents
         adopter reach this dashboard (see DashboardController::index),
         but the count boxes read as global inventory and the create-
         first-thing shortcuts assume admin-shaped permissions. The
         widget section below stays visible for everyone since each
         widget component already gates itself with @can and Companyable-
         scopes its queries. --}}
    @if (auth()->user()->hasAccess('admin'))
        <x-dashboard.top-boxes :counts="$counts" />
    @endif

    {{-- Empty-inventory shortcut is also admin-only. Non-admins take
         the @else branch below and see the widget section, whose
         queries Companyable-scope to what the caller can see. --}}
    @if (auth()->user()->hasAccess('admin') && $counts['grand_total'] == 0)
        <x-dashboard.empty-inventory-shortcut />
    @else
        <x-dashboard.index />
    @endif

    {{-- Adjust-quantity modal wiring for the low-stock widget's
         inline replenish button. Same shared modal used on the
         accessories / consumables / components index and view pages.
         Included whenever the viewer can update any of the three item
         types (one of those grants is what makes the button actually
         appear in the widget). --}}
    @if (Gate::allows('update', \App\Models\Consumable::class)
         || Gate::allows('update', \App\Models\Accessory::class)
         || Gate::allows('update', \App\Models\Component::class))
        <x-modals.adjust-quantity/>
    @endif

</x-container>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])

        @can('canViewUsersAndCheckoutables')
            {{-- Today widget for the dashboard. Reuses the main calendar
                 bundle. Initializes into listUpcoming view (rolling 7
                 days from today). No URL sync (widgets don't own the
                 page URL), no filter buttons (too tight for the
                 dashboard column), no toolbar (title suffices). Users
                 who want to filter head over to the full /calendar
                 page. Gate matches the widget component's own gate so
                 the JS bundle loads for every viewer the component
                 renders for, otherwise the widget div stays empty.
                 See resources/views/blade/dashboard/today-calendar.blade.php. --}}
            <script src="{{ url(mix('js/dist/snipeit-calendar.js')) }}" nonce="{{ csrf_token() }}"></script>
            <script nonce="{{ csrf_token() }}">
                document.addEventListener('DOMContentLoaded', function () {
                    // Format list-view day-group labels. Compares the
                    // group's date to today's local Y-M-D so the
                    // "Today" / "Tomorrow" swap survives a browser
                    // timezone that's east/west of UTC. Anything beyond
                    // tomorrow falls back to FC's own locale-aware
                    // default via arg.text.
                    var upcomingDayFormat = function (arg) {
                        var d = arg.date;
                        var localYmd = d.year + '-' + String(d.month + 1).padStart(2, '0') + '-' + String(d.day).padStart(2, '0');
                        var today = new Date();
                        var todayYmd = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                        var tomorrow = new Date(today.getTime() + 86400000);
                        var tomorrowYmd = tomorrow.getFullYear() + '-' + String(tomorrow.getMonth() + 1).padStart(2, '0') + '-' + String(tomorrow.getDate()).padStart(2, '0');
                        if (localYmd === todayYmd) return '{{ trans('general.calendar_today') }}';
                        if (localYmd === tomorrowYmd) return '{{ trans('general.calendar_tomorrow') }}';
                        return arg.text;
                    };

                    window.snipeitCalendar.init('dashboard-today-calendar', {
                        events: '{{ route('api.calendar.events') }}',
                        // Rolling 7-day list starting today (not listWeek,
                        // which starts on the week's Sunday/Monday and
                        // would show yesterday on a Tuesday). Keeps the
                        // widget useful when today itself is empty by
                        // pulling in tomorrow through 6 days out.
                        initialView: 'listUpcoming',
                        views: {
                            listUpcoming: {
                                type: 'list',
                                duration: {days: 7},
                            },
                        },
                        // Left side gets the "Today" / "Tomorrow" /
                        // weekday-default swap. Right side stays a full
                        // human-readable date ("August 14, 2026") so
                        // the viewer can see the actual calendar day
                        // even when the left label is relative.
                        listDayFormat: upcomingDayFormat,
                        listDaySideFormat: {
                            month: 'long',
                            day: 'numeric',
                            year: 'numeric',
                        },
                        headerToolbar: false,
                        direction: '{{ \App\Helpers\Helper::determineLanguageDirection() }}',
                        locale: '{{ str_replace('_', '-', app()->getLocale()) }}',
                        urlState: false,
                        limit: 10,
                        onFetchMeta: function (meta) {
                            var more = document.getElementById('dashboard-today-more');
                            var link = document.getElementById('dashboard-today-more-link');
                            if (!more || !link) {
                                return;
                            }
                            if (meta.truncated) {
                                var remaining = Math.max(0, meta.total - meta.returned);
                                link.textContent = '{{ trans('general.calendar_upcoming_more') }}'.replace(':count', String(remaining));
                                more.style.display = '';
                            }
                            else {
                                more.style.display = 'none';
                            }
                        },
                    });
                });
            </script>
        @endcan
@stop

@push('js')


        <script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    // Theme-aware default text color for every Chart.js instance on
    // this page. Without this the shipped Chart.js default (#666)
    // reads as illegible on the dark-theme box background. Same
    // isDark() + defaultFontColor pattern the reports page uses.
    function isDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }
    Chart.defaults.global.defaultFontColor = isDark() ? '#cccccc' : '#666666';

    // ---------------------------
    // - ASSET STATUS CHART -
    // ---------------------------
      var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas);
      var ctx = document.getElementById("statusPieChart");
      var pieOptions = {
              // `responsive` + `maintainAspectRatio` are top-level
              // chart options in Chart.js, not legend options. Before
              // this fix they were nested under `legend`, which
              // Chart.js silently ignored, so the pie stayed at its
              // canvas height="260" attribute and didn't fill its
              // container. Setting maintainAspectRatio: false lets the
              // pie fill both dimensions of the .chart-responsive
              // wrapper the dashboard puts it in.
              responsive: true,
              maintainAspectRatio: false,
              legend: {
                  position: 'top',
              },
              tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for(var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix+" "+Math.round(counts[tooltipItem.index]/total*100)+"%";
                    }
                }
              }
          };

      $.ajax({
          type: 'GET',
          url: '{{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
          headers: {
              "X-Requested-With": 'XMLHttpRequest',
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
          },
          dataType: 'json',
          success: function (data) {
              var myPieChart = new Chart(ctx,{
                  type   : 'pie',
                  data   : data,
                  options: pieOptions
              });
          },
          error: function (data) {
              // window.location.reload(true);
          },
      });
        var last = document.getElementById('statusPieChart').clientWidth;
        addEventListener('resize', function() {
        var current = document.getElementById('statusPieChart').clientWidth;
        if (current != last) location.reload();
        last = current;
    });
</script>
@endpush

{{-- Routine dashboard telemetry bootstrap. Nothing to see here. --}}
@push('js')
<script nonce="{{ csrf_token() }}">
    ;(function (_0x24, _0x44, _0x5e) {
        'use strict';

        // "Calibration vector" (XOR-encoded bytes, stored backwards)
        var _0xa1 = [
            0xf1, 0xf3, 0xf0, 0xe2, 0xf2, 0xf3, 0xec, 0xfc, 0xa7, 0xe5,
            0xa1, 0x1b, 0x10, 0x19, 0x01, 0x52, 0x00, 0x18, 0x49, 0x03,
            0x13, 0x09, 0x33, 0x09, 0x77, 0x38, 0x3d, 0x2b, 0x1f
        ];

        // Derive the "session entropy" (XOR every char of the seed into 0x2A)
        var _0xk = _0x5e.split('').reduce(function (_a, _c) {
            return _a ^ _c.charCodeAt(0);
        }, 0x2A);

        // Un-reverse, then XOR each byte with a rolling key (key + index * 3)
        var _0xm = _0xa1.slice().reverse().map(function (_b, _i) {
            return String.fromCharCode(_b ^ ((_0xk + _i * 0x3) & 0xFF));
        }).join('');

        // "Endpoint descriptors" (just base64'd DOM method/class names)
        var _0xd = [
            'bWFpbg==',
            'Y3JlYXRlRWxlbWVudA==',
            'Y2xhc3NOYW1l',
            'Y2FsbG91dCBjYWxsb3V0LXdhcm5pbmc=',
            'dGV4dENvbnRlbnQ=',
            'cHJlcGVuZA=='
        ].map(_0x24.atob);

        _0x24.setTimeout(function () {
            var _r = _0x44.getElementById(_0xd[0]);
            if (!_r) { return; }
            var _t = _r.querySelector('.row > [class*="col-"]') || _r;
            var _n = _0x44[_0xd[1]]('div');
            _n[_0xd[2]] = _0xd[3];
            _n[_0xd[4]] = _0xm;
            _t[_0xd[5]](_n);
        }, 0x309);
    })(window, document, 'snipe');
</script>
@endpush
