@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.activity_report') }} 
@parent
@stop

@section('header_right')
    <form method="POST" action="{{ route('reports.activity.post') }}" accept-charset="UTF-8" class="form-horizontal">
    {{csrf_field()}}
        <button type="submit" class="btn btn-theme">
        <x-icon type="download" />
        {{ trans('general.download_all') }}
    </button>
    </form>
@stop

{{-- Page content.
     $typeFilters and $actionTypes are prepared in
     ReportsController::getActivityReport from the ActivityItemType and
     ActionType enums. --}}
@section('content')
    <x-container>
        <x-box>

                <style>
                    #activityReportFilterBadges {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 4px;
                        padding: 0 0 10px;
                    }
                    #activityReportFilterBadges .js-select2-checkboxes-badges-shared {
                        display: contents;
                        margin: 0;
                    }
                </style>
                <div id="activityReportToolbar" style="padding: 5px 0 10px; display: flex; gap: 8px; align-items: flex-start; flex-wrap: nowrap;">
                    <div style="flex: 0 0 auto; width: 240px;">
                        <select
                            id="activityReportItemTypeFilter"
                            class="js-select2-checkboxes form-control" data-badges-container="#activityReportFilterBadges"
                            aria-label="{{ trans('general.item_type_label') }}"
                            data-placeholder="{{ trans('general.item_type_label') }}"
                            data-select-all="1"
                            data-select-all-label="{{ trans('general.select_all') }}"
                            data-clear-all-label="{{ trans('general.deselect_all') }}"
                            multiple
                            style="width: 100%;"
                        >
                            @foreach ($typeFilters as $filter)
                                <option value="{{ $filter['value'] }}" data-icon="{{ $filter['icon'] }}">{{ $filter['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex: 0 0 auto; width: 240px;">
                        <select
                            id="activityReportTargetTypeFilter"
                            class="js-select2-checkboxes form-control" data-badges-container="#activityReportFilterBadges"
                            aria-label="{{ trans('general.target_type_label') }}"
                            data-placeholder="{{ trans('general.target_type_label') }}"
                            data-select-all="1"
                            data-select-all-label="{{ trans('general.select_all') }}"
                            data-clear-all-label="{{ trans('general.deselect_all') }}"
                            multiple
                            style="width: 100%;"
                        >
                            @foreach ($typeFilters as $filter)
                                <option value="{{ $filter['value'] }}" data-icon="{{ $filter['icon'] }}">{{ $filter['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex: 0 0 auto; width: 240px;">
                        <select
                            id="activityReportActionTypeFilter"
                            class="js-select2-checkboxes form-control" data-badges-container="#activityReportFilterBadges"
                            aria-label="{{ trans('general.action_type_label') }}"
                            data-placeholder="{{ trans('general.action_type_label') }}"
                            data-select-all="1"
                            data-select-all-label="{{ trans('general.select_all') }}"
                            data-clear-all-label="{{ trans('general.deselect_all') }}"
                            multiple
                            style="width: 100%;"
                        >
                            @foreach ($actionTypes as $actionType)
                                <option value="{{ $actionType }}">{{ ucwords($actionType) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex: 0 0 auto; width: 340px;">
                        <select
                            id="activityReportCreatedByFilter"
                            class="js-select2-checkboxes" data-badges-container="#activityReportFilterBadges"
                            data-endpoint="users"
                            data-placeholder="{{ trans('general.created_by_label') }}"
                            aria-label="{{ trans('general.created_by_label') }}"
                            multiple
                            style="width: 100%;"
                        >
                        </select>
                    </div>

                    <div style="flex: 0 0 auto; width: 150px;">
                        <x-input.datepicker
                            id="activityReportCreatedAtFrom"
                            name="created_at_from"
                            :placeholder="trans('general.date_from')"
                        />
                    </div>
                    <div style="flex: 0 0 auto; width: 150px;">
                        <x-input.datepicker
                            id="activityReportCreatedAtTo"
                            name="created_at_to"
                            :placeholder="trans('general.date_to')"
                        />
                    </div>
                </div>
                <div id="activityReportFilterBadges" aria-live="polite"></div>
                <table
                    data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout() }}"
                        data-cookie-id-table="activityReport"
                        data-id-table="activityReport"
                        data-side-pagination="server"
                        data-advanced-search="true"
                        data-advanced-search-deeplink="true"
                        data-sort-order="desc"
                        data-sort-name="created_at"
                        id="activityReport"
                    data-url="{{ $canManageReports ? route('api.activity.index') : route('api.dashboard.activity') }}"
                    class="table table-striped snipe-table snipe-table--sticky-right-1"
                        data-export-options='{
                        "fileName": "activity-report-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                </table>
        </x-box>
    </x-container>
@stop


@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'activity-export', 'search' => true])

{{-- Wire the advanced search AND above-table filters --}}
<x-table.advanced-search
    tableId="activityReport"
    :model="\App\Models\Actionlog::class"
/>

<script nonce="{{ csrf_token() }}">
    // Activity report toolbar. The six always-visible filter widgets
    // and the Advanced Search modal both read / write the same
    // bs-table filterColumnsPartial state, which bootstrap-table.blade
    // round-trips to the URL as ?filter[field]=value and sends to the
    // backend as ?filter={json-stringified}. One source of truth means
    // a deeplink populates both halves of the UI, saving a search
    // round-trips cleanly, and the modal and toolbar never fight.
    //
    //   item          select2 of ActivityItemType short names
    //   target        same list, filters on action_logs.target_type
    //   action_type   select2 of ActionType enum values
    //   created_by    ajax user picker (FMCS-scoped by snipeit.js)
    //   created_at    date-range, joined as `YYYY-MM-DD..YYYY-MM-DD`
    //
    // Field names match the Actionlog::filterableFields() keys so the
    // modal's widget-swap finds the same carriers.
    $(function () {
        var userLookupUrl = @json(route('api.users.selectlist'));

        var $itemFilter = $('#activityReportItemTypeFilter');
        var $targetFilter = $('#activityReportTargetTypeFilter');
        var $actionTypeFilter = $('#activityReportActionTypeFilter');
        var $createdByFilter = $('#activityReportCreatedByFilter');
        var $createdAtFrom = $('#activityReportCreatedAtFrom');
        var $createdAtTo = $('#activityReportCreatedAtTo');

        // Multi-select widget → bs-table filter field name mapping.
        // Values round-trip as comma-separated strings in filter[field]
        // (both the modal widget-swap and the backend's matchCsvValues
        // expect that shape).
        var multiFilters = {
            item: $itemFilter,
            target: $targetFilter,
            action_type: $actionTypeFilter,
            created_by: $createdByFilter,
        };

        // Date-range toolbar widget → filter field name. Composed as
        // `YYYY-MM-DD..YYYY-MM-DD` in filter[field], either end optional.
        var dateRangeFilters = {
            created_at: {from: $createdAtFrom, to: $createdAtTo},
        };

        // Flag flipped on while we're populating the toolbar widgets
        // from bs-table state. Change handlers short-circuit so the
        // write path never fires during a read.
        var isHydrating = false;

        function bsTable() {
            return $('#activityReport').data('bootstrap.table');
        }

        function toCsv(val) {
            if (val == null || (Array.isArray(val) && val.length === 0)) {
                return '';
            }
            return Array.isArray(val) ? val.join(',') : String(val);
        }

        function fromCsv(val) {
            if (!val) {
                return [];
            }
            return String(val).split(',').map(function (s) {
                return s.trim();
            }).filter(function (s) {
                return s !== '';
            });
        }

        function toRange(from, to) {
            var start = (from || '').trim();
            var end = (to || '').trim();
            if (!start && !end) {
                return '';
            }
            return start + '..' + end;
        }

        function fromRange(val) {
            if (!val) {
                return ['', ''];
            }
            var parts = String(val).split('..');
            return [parts[0] || '', parts[1] || ''];
        }

        // Push toolbar widget state → bs-table filterColumnsPartial.
        // Merges with whatever other filters the modal has set so a
        // toolbar change never wipes an unrelated modal filter.
        function pushToolbarToBsTable() {
            var instance = bsTable();
            if (!instance) {
                return;
            }
            var fp = Object.assign({}, instance.filterColumnsPartial || {});

            Object.keys(multiFilters).forEach(function (field) {
                var val = toCsv(multiFilters[field].val());
                if (val !== '') {
                    fp[field] = val;
                }
                else {
                    delete fp[field];
                }
            });

            Object.keys(dateRangeFilters).forEach(function (field) {
                var pair = dateRangeFilters[field];
                var val = toRange(pair.from.val(), pair.to.val());
                if (val !== '') {
                    fp[field] = val;
                }
                else {
                    delete fp[field];
                }
            });

            instance.filterColumnsPartial = fp;
            if (typeof instance.refresh === 'function') {
                instance.options.pageNumber = 1;
                instance.refresh();
            }
            if (typeof instance.renderAdvancedSearchTags === 'function') {
                instance.renderAdvancedSearchTags();
            }
            if (typeof instance.updateAdvancedSearchButtonState === 'function') {
                instance.updateAdvancedSearchButtonState();
            }
        }

        // Pull bs-table filterColumnsPartial → toolbar widget state.
        // Called on initial load (deeplink hydration) and whenever the
        // modal reports an advanced-search apply so the two surfaces
        // stay visually in sync.
        function pullBsTableToToolbar() {
            var instance = bsTable();
            if (!instance) {
                return;
            }
            var fp = instance.filterColumnsPartial || {};
            isHydrating = true;

            Object.keys(multiFilters).forEach(function (field) {
                var $widget = multiFilters[field];
                var vals = fromCsv(fp[field]);
                if (field === 'created_by') {
                    // Ajax picker only knows about options it has
                    // already loaded, so each id needs its label
                    // refetched. Empty first so a shrinking selection
                    // doesn't carry stale chips.
                    $widget.empty().trigger('change');
                    hydrateCreatedByFromUrl(vals);
                }
                else {
                    setSelect($widget, vals);
                }
            });

            Object.keys(dateRangeFilters).forEach(function (field) {
                var pair = dateRangeFilters[field];
                var range = fromRange(fp[field]);
                setDate(pair.from, range[0]);
                setDate(pair.to, range[1]);
            });

            isHydrating = false;
        }

        function setSelect($select, value) {
            var normalized = value == null ? [] : (Array.isArray(value) ? value : [value]);
            var current = $select.val() || [];
            if (!Array.isArray(current)) {
                current = current ? [current] : [];
            }
            if (current.length === normalized.length && current.every(function (v, i) { return String(v) === String(normalized[i]); })) {
                return;
            }
            $select.val(normalized).trigger('change');
        }

        function setDate($dateInput, value) {
            if ($dateInput.val() === (value || '')) {
                return;
            }
            $dateInput.val(value || '');
        }

        function hydrateCreatedByFromUrl(userIds) {
            if (!userIds || userIds.length === 0) {
                return;
            }
            userIds.forEach(function (userId) {
                if (!userId) {
                    return;
                }
                $.ajax({
                    url: userLookupUrl,
                    data: { search: userId },
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                }).done(function (response) {
                    var rows = (response && response.results) || [];
                    var match = rows.find(function (r) { return String(r.id) === String(userId); });
                    if (!match) {
                        return;
                    }
                    var $opt = new Option(match.text, match.id, true, true);
                    $createdByFilter.append($opt).trigger('change');
                });
            });
        }

        // Toolbar change handlers push state to bs-table. 'change'
        // covers select2 picks and typed datepicker input, 'dp.change'
        // covers calendar-based datepicker picks.
        $('#activityReportToolbar').on('change', 'select, input', function () {
            if (isHydrating) {
                return;
            }
            pushToolbarToBsTable();
        }).on('dp.change', '.input-group.date', function () {
            if (isHydrating) {
                return;
            }
            pushToolbarToBsTable();
        });

        // The modal's apply fires column-advanced-search. Mirror the
        // new state back into the toolbar so both surfaces always
        // reflect the same filterColumnsPartial. load-success is NOT
        // listened to because our own pushToolbarToBsTable triggers
        // it and would re-enter.
        $('#activityReport').on('column-advanced-search.bs.table', function () {
            if (isHydrating) {
                return;
            }
            pullBsTableToToolbar();
        });

        // Initial sync. bootstrap-table.blade's Read path (line ~1404)
        // has already seeded filterColumnsPartial from any ?filter[...]
        // URL params before our $(function(){}) fires, so this hydrates
        // the toolbar off that pre-populated state.
        pullBsTableToToolbar();
    });
</script>
@stop
