@props([
    'tableId',
    'model' => null,
    'widgets' => [],
])

@php
    $resolvedWidgets = ($model && method_exists($model, 'filterableFields'))
        ? $model::filterableFields()
        : $widgets;
@endphp

@if (! empty($resolvedWidgets))
    <div id="{{ $tableId }}AdvancedFilterStash" style="display:none;">
        @foreach ($resolvedWidgets as $widget)
            @php $type = $widget['type'] ?? 'select2-checkboxes'; @endphp
            <div data-advanced-filter-widget="{{ $widget['field'] }}" data-advanced-filter-type="{{ $type }}">
                @if ($type === 'date-range')
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">{{ $widget['label'] }}</label>
                        <div class="col-sm-6">
                            <x-input.date-range
                                :name_start="$widget['field'].'_adv_start'"
                                :name_end="$widget['field'].'_adv_end'"
                            />
                        </div>
                    </div>
                @elseif ($type === 'boolean')
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">{{ $widget['label'] }}</label>
                        <div class="col-sm-6" style="padding-top: 7px;">
                            <label class="checkbox-inline" style="margin-right:16px;vertical-align:middle;">
                                <input type="checkbox" class="js-advanced-filter-boolean-yes" value="1" style="vertical-align:middle;">&nbsp;&nbsp;<span style="vertical-align:middle;">{{ trans('general.yes') }}</span>
                            </label>
                            <label class="checkbox-inline" style="vertical-align:middle;">
                                <input type="checkbox" class="js-advanced-filter-boolean-no" value="0" style="vertical-align:middle;">&nbsp;&nbsp;<span style="vertical-align:middle;">{{ trans('general.no') }}</span>
                            </label>
                        </div>
                    </div>
                @elseif ($type === 'numeric')
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">{{ $widget['label'] }}</label>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-xs-6" style="padding-right:3px;">
                                    <input type="number" step="any" class="form-control js-advanced-filter-numeric-gt" placeholder="{{ trans('general.greater_than') }}" aria-label="{{ $widget['label'] }} - {{ trans('general.greater_than') }}">
                                </div>
                                <div class="col-xs-6" style="padding-left:3px;">
                                    <input type="number" step="any" class="form-control js-advanced-filter-numeric-lt" placeholder="{{ trans('general.less_than') }}" aria-label="{{ $widget['label'] }} - {{ trans('general.less_than') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($type === 'enum' || $type === 'set')
                    <div class="form-group row">
                        <label class="col-sm-4 control-label" for="{{ $widget['field'] }}_adv">{{ $widget['label'] }}</label>
                        <div class="col-sm-6">
                            {{-- Static select2-checkboxes with no data-endpoint. snipeit.js
                                 falls back to reading option tags from the DOM when the
                                 endpoint attribute is missing, so this becomes a native
                                 multi-select picker without the AJAX pagination path.
                                 Rendering is identical for `enum` and `set` because the
                                 UI shape (multi-select from fixed options) is the same.
                                 Backend path differs: enum does whereIn on a single-value
                                 column, set does per-value LIKE on a comma-joined column. --}}
                            <select
                                id="{{ $widget['field'] }}_adv"
                                name="{{ $widget['field'] }}_adv[]"
                                class="js-select2-checkboxes"
                                multiple
                                aria-label="{{ $widget['label'] }}"
                                style="width: 100%;"
                                data-placeholder="{{ $widget['label'] }}"
                                data-count-label="{{ trans('general.checkbox_multiselect_count') }}"
                            >
                                @foreach ($widget['options'] ?? [] as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <x-input.select2-checkboxes
                        :name="$widget['field'].'_adv'"
                        :label="$widget['label']"
                        :endpoint="$widget['endpoint']"
                        labelClass="col-sm-4"
                        inputClass="col-sm-6"
                        :includeNull="true"
                    />
                @endif
            </div>
        @endforeach
    </div>

    <script nonce="{{ csrf_token() }}">
        (function () {
            var tableId = @json($tableId);
            var $stash = $('#' + tableId + 'AdvancedFilterStash');
            if ($stash.length === 0) {
                return;
            }

            var modalSelector = '#avdSearchModal_' + tableId;

            var widgetFields = $stash.find('[data-advanced-filter-widget]').map(function () {
                return $(this).data('advanced-filter-widget');
            }).get();

            window.snipeAdvancedFilterLabels = window.snipeAdvancedFilterLabels || {};
            window.snipeAdvancedFilterLabels[tableId] = window.snipeAdvancedFilterLabels[tableId] || {};

            // Per-field widget type registry. The pill renderer in
            // partials/bootstrap-table.blade.php reads this to decide
            // whether a filter's raw value should display as a yes/no
            // chip (boolean), a date-range chip, or the default
            // single-value / per-term chip. Populated once at
            // page-load from the stashed widgets' data-advanced-filter-type.
            window.snipeAdvancedFilterFieldTypes = window.snipeAdvancedFilterFieldTypes || {};
            window.snipeAdvancedFilterFieldTypes[tableId] = window.snipeAdvancedFilterFieldTypes[tableId] || {};
            var noValueLabel = @json(trans('general.no_value'));
            $stash.find('[data-advanced-filter-widget]').each(function () {
                var $slot = $(this);
                var field = $slot.data('advanced-filter-widget');
                var type = $slot.data('advanced-filter-type') || 'select2-checkboxes';
                window.snipeAdvancedFilterFieldTypes[tableId][field] = type;
                // FK dropdown: seed the label registry so the pill
                // reads "No value" rather than the raw "null"
                // sentinel. The dropdown row itself is prepended
                // server-side via the includeNull prop on the
                // select2-checkboxes input component, which puts
                // the data-include-null attribute on the select
                // element before snipeit.js runs select2 init.
                if (type === 'select2-checkboxes') {
                    recordLabel(field, 'null', noValueLabel);
                }
                // Static enum / set dropdown: options are rendered
                // as option tags in the stash, so seed the label
                // registry from them directly. No backfill AJAX,
                // no includeNull. Pills resolve value -> text from
                // this seed.
                if (type === 'enum' || type === 'set') {
                    $slot.find('option').each(function () {
                        var val = String($(this).val());
                        if (val !== '') {
                            recordLabel(field, val, $(this).text());
                        }
                    });
                }
            });

            function recordLabel(field, id, name) {
                if (!id) {
                    return;
                }
                var fieldMap = window.snipeAdvancedFilterLabels[tableId][field] = window.snipeAdvancedFilterLabels[tableId][field] || {};
                fieldMap[String(id)] = name || String(id);
            }

            function nudgePillRender() {
                var bsTable = $('#' + tableId).data('bootstrap.table');
                if (bsTable && typeof bsTable.renderAdvancedSearchTags === 'function') {
                    bsTable.renderAdvancedSearchTags();
                }
            }

            function findWidgetSelect($widget, field) {
                return $widget.find('select.js-select2-checkboxes').filter(function () {
                    return $(this).attr('name') === field + '_adv[]' || $(this).data('advanced-filter-field') === field;
                });
            }

            function findCarrier($modal, field) {
                return $modal.find('input[name="' + field + '"]').filter(function () {
                    return $(this).attr('type') === 'hidden' || $(this).attr('type') === 'text';
                }).first();
            }

            function syncWidgetToCarrier(field, $modal) {
                var $widget = $modal.find('[data-advanced-filter-widget="' + field + '"]');
                var $select = findWidgetSelect($widget, field);
                var $carrier = findCarrier($modal, field);
                if ($select.length === 0 || $carrier.length === 0) {
                    return;
                }
                var vals = $select.val() || [];
                var csv = Array.isArray(vals) ? vals.join(',') : String(vals);
                (Array.isArray(vals) ? vals : [vals]).forEach(function (id) {
                    var $option = $select.find('option[value="' + id + '"]');
                    recordLabel(field, id, $option.length ? $option.text() : id);
                });
                $carrier.val(csv);
                nudgePillRender();
            }

            // Pulls the two date inputs under a date-range widget and
            // writes `start..end` to the carrier. Empty ends are kept
            // in the shape (`..end` or `start..`) so the backend's
            // one-sided range path can see them. Fully-empty writes
            // the empty string so the carrier does not carry a stale
            // `..` through serializeArray.
            function syncDateRangeToCarrier(field, $modal) {
                var $widget = $modal.find('[data-advanced-filter-widget="' + field + '"]');
                var $carrier = findCarrier($modal, field);
                if ($widget.length === 0 || $carrier.length === 0) {
                    return;
                }
                var start = ($widget.find('.js-date-range-start input').val() || '').trim();
                var end = ($widget.find('.js-date-range-end input').val() || '').trim();
                var csv = (start === '' && end === '') ? '' : start + '..' + end;
                $carrier.val(csv);
                nudgePillRender();
            }

            // Mirror of snipeit.js's initDateRangeLinking, scoped to
            // a single widget. snipeit.js only runs the global pass
            // once at page-load. After we move a date-range widget
            // into the modal we need to wire the min/max cross-link
            // here so picking a start date caps the end picker and
            // vice versa.
            function linkDateRangePickers($scope) {
                var $start = $scope.find('.js-date-range-start');
                var $end = $scope.find('.js-date-range-end');
                if (!$start.length || !$end.length) {
                    return;
                }
                $start.off('dp.change.advancedFilterLink').on('dp.change.advancedFilterLink', function (e) {
                    var picker = $end.data('DateTimePicker');
                    if (picker) {
                        picker.minDate(e.date);
                    }
                });
                $end.off('dp.change.advancedFilterLink').on('dp.change.advancedFilterLink', function (e) {
                    var picker = $start.data('DateTimePicker');
                    if (picker) {
                        picker.maxDate(e.date);
                    }
                });
            }

            // Reads both checkboxes and writes the carrier value:
            //   Yes only  → "1"  (where col = 1)
            //   No only   → "0"  (where col = 0)
            //   Neither   → ""   (filter dropped)
            //   Both      → ""   ("show both" ≡ don't filter, so
            //                     the filter is dropped from
            //                     filterColumnsPartial rather than
            //                     getting a WHERE 1 AND 0 that never
            //                     matches)
            function syncBooleanToCarrier(field, $modal) {
                var $widget = $modal.find('[data-advanced-filter-widget="' + field + '"]');
                var $carrier = findCarrier($modal, field);
                if ($widget.length === 0 || $carrier.length === 0) {
                    return;
                }
                var yesChecked = $widget.find('input.js-advanced-filter-boolean-yes').is(':checked');
                var noChecked = $widget.find('input.js-advanced-filter-boolean-no').is(':checked');
                var value = '';
                if (yesChecked && !noChecked) {
                    value = '1';
                }
                else if (noChecked && !yesChecked) {
                    value = '0';
                }
                $carrier.val(value);
                nudgePillRender();
            }

            // Reads both numeric inputs and writes a composite
            // `gt:X,lt:Y` carrier value (either side may be absent
            // for a one-sided range). Empty both inputs clear the
            // filter.
            function syncNumericToCarrier(field, $modal) {
                var $widget = $modal.find('[data-advanced-filter-widget="' + field + '"]');
                var $carrier = findCarrier($modal, field);
                if ($widget.length === 0 || $carrier.length === 0) {
                    return;
                }
                var gt = ($widget.find('input.js-advanced-filter-numeric-gt').val() || '').trim();
                var lt = ($widget.find('input.js-advanced-filter-numeric-lt').val() || '').trim();
                var parts = [];
                if (gt !== '') {
                    parts.push('gt:' + gt);
                }
                if (lt !== '') {
                    parts.push('lt:' + lt);
                }
                $carrier.val(parts.join(','));
                nudgePillRender();
            }

            function setupNumericWidget(field, $widget, $carrier, currentCsv, $modal) {
                var $gt = $widget.find('input.js-advanced-filter-numeric-gt');
                var $lt = $widget.find('input.js-advanced-filter-numeric-lt');

                // Reconcile both inputs with the carrier on every
                // open. Parse `gt:X`, `lt:Y`, or `gt:X,lt:Y` back
                // into the two fields. On Cancel the carrier is
                // wiped first, so both inputs end up cleared.
                var gtVal = '';
                var ltVal = '';
                (currentCsv || '').split(',').forEach(function (part) {
                    part = part.trim();
                    var gtMatch = part.match(/^gt:(-?\d+(\.\d+)?)$/);
                    var ltMatch = part.match(/^lt:(-?\d+(\.\d+)?)$/);
                    if (gtMatch) {
                        gtVal = gtMatch[1];
                    }
                    else if (ltMatch) {
                        ltVal = ltMatch[1];
                    }
                });
                $gt.val(gtVal);
                $lt.val(ltVal);

                $gt.add($lt).off('input.advancedFilterSync change.advancedFilterSync').on('input.advancedFilterSync change.advancedFilterSync', function () {
                    syncNumericToCarrier(field, $modal);
                });
            }

            function setupBooleanWidget(field, $widget, $carrier, currentCsv, $modal) {
                var $yes = $widget.find('input.js-advanced-filter-boolean-yes');
                var $no = $widget.find('input.js-advanced-filter-boolean-no');

                // Reconcile checkbox state with the carrier on every
                // open. "1"/"true"/"yes" checks the Yes box.
                // "0"/"false"/"no" checks the No box. Empty / unknown
                // leaves both unchecked. On Cancel the carrier is
                // wiped first, so this ends up clearing both ticks.
                var normalized = (currentCsv || '').toString().trim().toLowerCase();
                $yes.prop('checked', ['1', 'true', 'yes'].indexOf(normalized) !== -1);
                $no.prop('checked', ['0', 'false', 'no'].indexOf(normalized) !== -1);

                $yes.add($no).off('change.advancedFilterSync').on('change.advancedFilterSync', function () {
                    syncBooleanToCarrier(field, $modal);
                });
            }

            function setupSelectCheckboxesWidget(field, $widget, $carrier, currentCsv, $modal) {
                var $select = findWidgetSelect($widget, field);
                $select.attr('data-advanced-filter-field', field).removeAttr('name');
                $widget.find('input[name="' + field + '_adv[]"], input[name="' + field + '_adv"]').removeAttr('name');

                // Reconcile the widget's selection set to match the
                // carrier CSV on every open. Deselect-first handles
                // the Cancel path, bs-table's cancelAdvancedSearch
                // calls native form.reset(), which does NOT deselect
                // <option>s appended at runtime with
                // selected="selected" (their defaultSelected is true).
                // Without this reset-first pass, cleared filters would
                // re-render with the prior session's selections still
                // visible.
                $select.find('option').prop('selected', false).removeAttr('selected');
                var ids = currentCsv === '' ? [] : currentCsv.split(',').filter(Boolean);
                ids.forEach(function (id) {
                    if ($select.find('option[value="' + id + '"]').length === 0) {
                        var cachedName = (window.snipeAdvancedFilterLabels[tableId][field] || {})[String(id)] || id;
                        $select.append($('<option>').val(id).text(cachedName));
                    }
                });
                // Canonical select2 programmatic-selection dance, set
                // the value array (options must exist first), then
                // fire `change` so select2 repaints its
                // templateSelection chip strip. Plain `change` (not
                // `change.select2`) is intentional so our own
                // `change.advancedFilterSync` handler fires and the
                // carrier picks up the restored CSV too.
                $select.val(ids).trigger('change');

                $select.off('change.advancedFilterSync').on('change.advancedFilterSync', function () {
                    syncWidgetToCarrier(field, $modal);
                });
                // select2 fires select2:select when the user ticks a
                // dropdown row. e.params.data carries the display
                // text, which is the most reliable id -> name source.
                // The backing <option> text can read back as the raw
                // id for a short window after a tick depending on how
                // select2 wires its internal state, so recording from
                // the event payload directly guarantees the pill
                // registry has the real name before the user clicks
                // Search.
                $select.off('select2:select.advancedFilterSync').on('select2:select.advancedFilterSync', function (e) {
                    var data = e.params && e.params.data;
                    if (data) {
                        recordLabel(field, data.id, data.text);
                        syncWidgetToCarrier(field, $modal);
                    }
                });
                $select.off('select2:unselect.advancedFilterSync').on('select2:unselect.advancedFilterSync', function () {
                    syncWidgetToCarrier(field, $modal);
                });
                observeSelectOptions($select, field);
            }

            function setupDateRangeWidget(field, $widget, $carrier, currentCsv, $modal) {
                // Strip the `_adv_*` names so neither picker input gets
                // serialized by applyAdvancedSearch. The carrier below
                // is the only input that reaches filterColumnsPartial.
                $widget.find('input[name="' + field + '_adv_start"]').removeAttr('name');
                $widget.find('input[name="' + field + '_adv_end"]').removeAttr('name');

                // Parse `start..end` (either side may be empty) back
                // into the two picker inputs on every open. On Cancel
                // the carrier is wiped first, so this ends up setting
                // both inputs to '' and clearing the pickers.
                var start = '';
                var end = '';
                var m = currentCsv.match(/^(.*)\.\.(.*)$/);
                if (m) {
                    start = m[1];
                    end = m[2];
                }
                var $startInput = $widget.find('.js-date-range-start input').first();
                var $endInput = $widget.find('.js-date-range-end input').first();
                $startInput.val(start);
                $endInput.val(end);

                // The datetimepicker was already initialized on page
                // load (snipeit.js's global pass walks all
                // [data-provide="datetimepicker"] elements, including
                // ones in the stashed widget). Pushing the input value
                // via jQuery does NOT update the picker's internal
                // moment, so we also call .date() on the picker
                // instance to keep both halves in sync.
                var $startWrapper = $widget.find('.js-date-range-start');
                var $endWrapper = $widget.find('.js-date-range-end');
                var startPicker = $startWrapper.data('DateTimePicker');
                var endPicker = $endWrapper.data('DateTimePicker');
                if (startPicker) {
                    startPicker.date(start === '' ? null : start);
                }
                if (endPicker) {
                    endPicker.date(end === '' ? null : end);
                }

                linkDateRangePickers($widget);

                $widget.find('.js-date-range-start, .js-date-range-end').off('dp.change.advancedFilterSync').on('dp.change.advancedFilterSync', function () {
                    syncDateRangeToCarrier(field, $modal);
                });
            }

            // Deeplinked / restored filter state loads with ids but
            // no names in the registry because select2:select never
            // fired this session and the pre-appended <option>
            // text(id) placeholder is only replaced in-place by
            // select2 (not caught by the MutationObserver). Backfill
            // once per field by hitting the same selectlist endpoint
            // the widget uses and recording id->text for every row.
            var backfillInFlight = {};

            function backfillLabelsForFiltered() {
                var bsTable = $('#' + tableId).data('bootstrap.table');
                if (!bsTable || !bsTable.filterColumnsPartial) {
                    return;
                }
                widgetFields.forEach(function (field) {
                    if (backfillInFlight[field]) {
                        return;
                    }
                    var csv = (bsTable.filterColumnsPartial[field] || '').toString();
                    if (!csv) {
                        return;
                    }
                    var map = window.snipeAdvancedFilterLabels[tableId][field] || {};
                    var missing = csv.split(',').map(function (v) {
                        return v.trim();
                    }).filter(function (id) {
                        return id !== '' && !map[id];
                    });
                    if (missing.length === 0) {
                        return;
                    }

                    var $stashSlot = $stash.find('[data-advanced-filter-widget="' + field + '"]');
                    var $select = $stashSlot.find('select.js-select2-checkboxes').first();
                    var endpoint = $select.data('endpoint') || $stashSlot.find('[data-endpoint]').first().data('endpoint');
                    if (!endpoint) {
                        return;
                    }

                    var baseUrl = $('meta[name="baseUrl"]').attr('content') || '/';
                    var requestUrl = baseUrl + 'api/v1/' + endpoint + '/selectlist';
                    backfillInFlight[field] = true;
                    $.ajax({
                        // paginateCollectionOrAll looks for `all=true`
                        // on the request to bypass pagination.
                        url: requestUrl,
                        method: 'GET',
                        dataType: 'json',
                        // X-Requested-With + CSRF match the headers
                        // every other selectlist caller in snipeit.js
                        // sends. The API middleware falls back to
                        // session auth for XHR requests only when
                        // these two are present. Without them the
                        // response is a 401 even for a logged-in admin.
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        data: {all: true},
                    }).done(function (response) {
                        var results = (response && response.results) ? response.results : [];
                        results.forEach(function (item) {
                            if (item && item.id !== undefined && item.text !== undefined) {
                                recordLabel(field, String(item.id), item.text);
                            }
                        });
                        // Pre-populated widget <option>s rendered
                        // with the raw id as text while the AJAX was
                        // in flight. Walk them now and swap text to
                        // the registry name so the modal's select2
                        // chips show names instead of ids.
                        var fieldMap = window.snipeAdvancedFilterLabels[tableId][field] || {};
                        $('[data-advanced-filter-widget="' + field + '"]').find('select.js-select2-checkboxes option').each(function () {
                            var val = String($(this).val());
                            if (fieldMap[val]) {
                                $(this).text(fieldMap[val]);
                            }
                        });
                        var $liveSelect = $('[data-advanced-filter-widget="' + field + '"]').find('select.js-select2-checkboxes').first();
                        if ($liveSelect.length && $liveSelect.data('select2')) {
                            $liveSelect.trigger('change.select2');
                        }
                        nudgePillRender();
                    }).always(function () {
                        backfillInFlight[field] = false;
                    });
                });
            }

            function observeSelectOptions($select, field) {
                if (!$select.length || $select.data('advancedFilterObserver')) {
                    return;
                }
                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        m.addedNodes.forEach(function (node) {
                            if (node.nodeName === 'OPTION') {
                                recordLabel(field, node.value, node.textContent);
                            }
                        });
                    });
                    nudgePillRender();
                });
                observer.observe($select[0], {childList: true});
                $select.data('advancedFilterObserver', observer);
            }

            // Fires every time the table loads, including the
            // initial state-restored load. Backfill any FK ids
            // present in filterColumnsPartial but missing from the
            // label registry so pills render names, not raw ids, on
            // first paint.
            $('#' + tableId).on('load-success.bs.table', function () {
                backfillLabelsForFiltered();
            });

            $(document).on('shown.bs.modal', modalSelector, function () {
                backfillLabelsForFiltered();
                var $modal = $(modalSelector);
                $modal.find('.modal-dialog').addClass('modal-lg');

                // Scrub stray `_adv`-shaped filter keys left over
                // from earlier builds where the select carried its
                // name into the serialized form. The current build
                // strips the `name` on inject, but any state from
                // before that fix still lingers in
                // filterColumnsPartial without this.
                var bsTable = $('#' + tableId).data('bootstrap.table');
                if (bsTable && bsTable.filterColumnsPartial) {
                    Object.keys(bsTable.filterColumnsPartial).forEach(function (k) {
                        if (/_adv(\[])?$/.test(k)) {
                            delete bsTable.filterColumnsPartial[k];
                        }
                    });
                    nudgePillRender();
                }

                widgetFields.forEach(function (field) {
                    var $stashSlot = $stash.find('[data-advanced-filter-widget="' + field + '"]');
                    var type = $stashSlot.data('advanced-filter-type') || 'select2-checkboxes';
                    var $widget = $stashSlot.children().first();
                    if ($widget.length === 0) {
                        return;
                    }

                    // Carrier can come from one of two places:
                    //   (a) bs-table auto-generated a text input for a
                    //       `searchable: true` presenter column with
                    //       this field name. In that case we take over
                    //       the existing input, flip it to hidden, and
                    //       replace the form-group with our widget.
                    //   (b) No presenter column exists for this field
                    //       (filter-only concept, e.g. User's
                    //       admin / superuser derived from the
                    //       permissions JSON). Synthesize a hidden
                    //       input and append the widget to the end of
                    //       the modal form so the filter still rides
                    //       through serializeArray.
                    var $carrier = findCarrier($modal, field);
                    var $bsFormGroup = null;
                    var synthesized = false;

                    if ($carrier.length) {
                        $bsFormGroup = $carrier.closest('.form-group');
                        if ($bsFormGroup.length === 0) {
                            return;
                        }
                    }
                    else {
                        $carrier = $('<input type="hidden">').attr('name', field);
                        synthesized = true;
                    }

                    var currentCsv = ($carrier.val() || '').toString().trim();
                    $widget.attr('data-advanced-filter-widget', field);
                    $widget.attr('data-advanced-filter-type', type);

                    // Make sure the carrier is hidden regardless of
                    // path. For (a), bs-table rendered a visible text
                    // input we are hijacking. For (b), the input we
                    // just synthesized was never visible to begin with.
                    // Belt-and-suspenders style / hide / aria calls
                    // because some browsers reject changing an
                    // input's `type` after creation and leave the
                    // element rendering as text.
                    $carrier.attr('type', 'hidden').hide().css('display', 'none').attr('aria-hidden', 'true');

                    if (type === 'date-range') {
                        setupDateRangeWidget(field, $widget, $carrier, currentCsv, $modal);
                    }
                    else if (type === 'boolean') {
                        setupBooleanWidget(field, $widget, $carrier, currentCsv, $modal);
                    }
                    else if (type === 'numeric') {
                        setupNumericWidget(field, $widget, $carrier, currentCsv, $modal);
                    }
                    else {
                        setupSelectCheckboxesWidget(field, $widget, $carrier, currentCsv, $modal);
                    }

                    $widget.append($carrier);
                    if (synthesized) {
                        // No existing bs-table form-group to replace,
                        // so append the widget as a new row at the
                        // end of the modal form.
                        $modal.find('.toolbar-model-form').append($widget);
                    }
                    else {
                        // Replace bs-table's form-group row with the
                        // widget wrapper, carrying the hidden input
                        // along inside it so the form still owns it.
                        $bsFormGroup.replaceWith($widget);
                    }
                });
            });

            $(document).on('hidden.bs.modal', modalSelector, function () {
                var $modal = $(modalSelector);
                widgetFields.forEach(function (field) {
                    var $widget = $modal.find('[data-advanced-filter-widget="' + field + '"]');
                    if ($widget.length === 0) {
                        return;
                    }
                    $widget.find('select').off('change.advancedFilterSync select2:select.advancedFilterSync select2:unselect.advancedFilterSync');
                    // Date-range widgets listen on the picker wrappers
                    // instead of the inner <select>. Namespaced .off()
                    // clears whichever namespace is attached to this
                    // widget type without caring which type we routed
                    // to on open.
                    $widget.find('.js-date-range-start, .js-date-range-end').off('dp.change.advancedFilterSync dp.change.advancedFilterLink');
                    $widget.find('input.js-advanced-filter-boolean-yes, input.js-advanced-filter-boolean-no').off('change.advancedFilterSync');
                    $widget.find('input.js-advanced-filter-numeric-gt, input.js-advanced-filter-numeric-lt').off('input.advancedFilterSync change.advancedFilterSync');
                    // Drop the carrier input before moving the
                    // widget back to the stash so a fresh bs-table
                    // modal body on the next open gets its own fresh
                    // input.
                    $widget.find('input[type="hidden"]').filter(function () {
                        return $(this).attr('name') === field;
                    }).remove();
                    var $stashSlot = $stash.find('[data-advanced-filter-widget="' + field + '"]');
                    $stashSlot.append($widget);
                });
            });
        })();
    </script>
@endif
