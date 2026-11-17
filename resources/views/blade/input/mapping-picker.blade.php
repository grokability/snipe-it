@props([
    'name',
    'direction_name' => null,
    'entries' => [],
    'direction_options' => [],
    'direction_icons' => [],
    'target_icons' => [],
    'supports_push' => false,
    'locked' => false,
    'add_label' => null,
    'empty_label' => null,
    'pick_extra_label' => null,
    'pick_target_label' => null,
])

@php
    $widgetId = 'mapping-picker-'.\Illuminate\Support\Str::random(8);
    $mapped = collect($entries)->filter(fn ($e) => ! empty($e['stored_target']) && $e['stored_target'] !== 'skip');
    $unmapped = collect($entries)->reject(fn ($e) => ! empty($e['stored_target']) && $e['stored_target'] !== 'skip');
    $showDirection = $supports_push && $direction_name !== null;
    $addLabel = $add_label ?: trans('button.add');
    $emptyLabel = $empty_label ?: trans('admin/settings/sync_adapters.mapping_picker_empty');
    $pickExtraLabel = $pick_extra_label ?: trans('admin/settings/sync_adapters.mapping_picker_pick_extra');
    $pickTargetLabel = $pick_target_label ?: trans('admin/settings/sync_adapters.mapping_picker_pick_target');
@endphp

<div class="mapping-picker-widget" data-mapping-picker-widget="{{ $widgetId }}">
    <table class="table table-condensed" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 25%">{{ trans('admin/settings/sync_adapters.mapping_picker_col_field') }}</th>
                <th>{{ trans('admin/settings/sync_adapters.mapping_picker_col_target') }}</th>
                @if ($showDirection)
                    <th style="width: 20%">{{ trans('admin/settings/sync_adapters.mapping_picker_col_direction') }}</th>
                @endif
                <th style="width: 60px"></th>
            </tr>
        </thead>
        <tbody class="mapping-picker-rows">
            @foreach ($mapped as $key => $entry)
                <tr data-field-key="{{ $key }}">
                    <td>{{ $entry['label'] }}</td>
                    <td>
                        <x-input.icon-select
                            :name="$name . '[' . $key . ']'"
                            :options="$entry['target_options']"
                            :icons="$target_icons"
                            :selected="$entry['stored_target']"
                            class="mapping-picker-row-target"
                            :disabled="$locked"
                            :aria-label="$entry['label'] . ' ' . trans('admin/settings/sync_adapters.mapping_picker_col_target')"
                        />
                    </td>
                    @if ($showDirection)
                        <td>
                            <x-input.icon-select
                                :name="$direction_name . '[' . $key . ']'"
                                :options="$direction_options"
                                :icons="$direction_icons"
                                :selected="$entry['stored_direction'] ?? 'pull'"
                                class="mapping-picker-row-direction"
                                data-minimum-results-for-search="Infinity"
                                :disabled="$locked"
                                :aria-label="$entry['label'] . ' ' . trans('admin/settings/sync_adapters.mapping_picker_col_direction')"
                            />
                        </td>
                    @endif
                    <td>
                        <button
                            type="button"
                            class="btn btn-danger mapping-picker-remove"
                            aria-label="{{ trans('button.delete') }}"
                            @if ($locked) disabled @endif
                        >
                            <x-icon type="x" />
                        </button>
                    </td>
                </tr>
            @endforeach
            <tr class="mapping-picker-empty" @if ($mapped->count() > 0) hidden @endif>
                <td colspan="{{ $showDirection ? 4 : 3 }}" class="text-muted text-center">
                    <em>{{ $emptyLabel }}</em>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="mapping-picker-picker-row">
                <td>
                    <label class="sr-only" for="{{ $widgetId }}-picker">{{ $pickExtraLabel }}</label>
                    <select
                        id="{{ $widgetId }}-picker"
                        name="{{ $name }}_pending[key]"
                        class="select2 mapping-picker-picker"
                        style="width: 100%"
                        data-placeholder="{{ $pickExtraLabel }}"
                        @if ($locked) disabled @endif
                    >
                        <option value=""></option>
                        @foreach ($unmapped as $key => $entry)
                            <option
                                value="{{ $key }}"
                                data-label="{{ $entry['label'] }}"
                                data-target-options="{{ json_encode($entry['target_options']) }}"
                            >{{ $entry['label'] }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <label class="sr-only" for="{{ $widgetId }}-target">{{ $pickTargetLabel }}</label>
                    <select
                        id="{{ $widgetId }}-target"
                        name="{{ $name }}_pending[value]"
                        class="select2 mapping-picker-new-target"
                        style="width: 100%"
                        data-placeholder="{{ $pickTargetLabel }}"
                        disabled
                    >
                        <option value=""></option>
                    </select>
                </td>
                @if ($showDirection)
                    <td>
                        <x-input.icon-select
                            :id="$widgetId . '-direction'"
                            :name="$direction_name . '_pending[value]'"
                            :options="$direction_options"
                            :icons="$direction_icons"
                            selected="pull"
                            class="mapping-picker-new-direction"
                            data-minimum-results-for-search="Infinity"
                            :disabled="$locked"
                        />
                    </td>
                @endif
                <td>
                    <button
                        type="button"
                        class="btn btn-primary mapping-picker-add"
                        aria-label="{{ $addLabel }}"
                        disabled
                    >
                        <x-icon type="create" />
                    </button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<script type="text/template" data-mapping-picker-template="{{ $widgetId }}">
    <tr data-field-key="__KEY__">
        <td>__LABEL__</td>
        <td>
            <select name="{{ $name }}[__KEY__]" class="select2 js-icon-select mapping-picker-row-target" style="width: 100%" aria-label="__LABEL__ target">
                __TARGET_OPTIONS__
            </select>
        </td>
        @if ($showDirection)
            <td>
                <select name="{{ $direction_name }}[__KEY__]" class="select2 js-icon-select mapping-picker-row-direction" style="width: 100%" data-minimum-results-for-search="Infinity" aria-label="__LABEL__ direction">
                    __DIRECTION_OPTIONS__
                </select>
            </td>
        @endif
        <td>
            <button type="button" class="btn btn-danger mapping-picker-remove" aria-label="{{ trans('button.delete') }}">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
        </td>
    </tr>
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var widgetId = @json($widgetId);
    var $widget = $('[data-mapping-picker-widget="' + widgetId + '"]');
    if ($widget.length === 0) { return; }

    var $rows = $widget.find('.mapping-picker-rows');
    var $empty = $widget.find('.mapping-picker-empty');
    var $picker = $widget.find('.mapping-picker-picker');
    var $newTarget = $widget.find('.mapping-picker-new-target');
    var $newDirection = $widget.find('.mapping-picker-new-direction');
    var $addBtn = $widget.find('.mapping-picker-add');
    var template = $('[data-mapping-picker-template="' + widgetId + '"]').html();

    // snipeit.js auto-inits .select2 elements at DOMContentLoaded, but
    // dynamic add/remove of rows means the selects inside the template
    // need explicit init/destroy so the widget doesn't leave hidden
    // shadow selects behind after removals.
    function initSelect2($el, extraOpts) {
        var opts = extraOpts || {};
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2(opts);
    }

    function destroySelect2($el) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
    }

    function encode(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function refreshEmptyState() {
        var hasRows = $rows.find('tr:not(.mapping-picker-empty)').length > 0;
        if (hasRows) { $empty.attr('hidden', ''); } else { $empty.removeAttr('hidden'); }
    }

    var directionIconsMap = @json((object) $direction_icons);

    function optionsHtml(map, selected) {
        var html = '';
        for (var value in map) {
            if (!Object.prototype.hasOwnProperty.call(map, value)) { continue; }
            var sel = String(value) === String(selected) ? ' selected' : '';
            var icon = directionIconsMap && directionIconsMap[value] ? ' data-icon="' + encode(directionIconsMap[value]) + '"' : '';
            html += '<option value="' + encode(value) + '"' + icon + sel + '>' + encode(map[value]) + '</option>';
        }
        return html;
    }

    // Direction selects carry .js-icon-select from the icon-select component
    // (server-rendered rows + picker row) and from the template string
    // emitted below (dynamic rows). The sync-adapter form's shared
    // snipeApplyIconRenderer handles them: page-load for anything
    // present at render, this helper for anything added later.
    function applyIconsToNewContent($scope) {
        if (typeof window.snipeApplyIconRenderer === 'function') {
            window.snipeApplyIconRenderer($scope.find('.js-icon-select'));
        }
    }

    function notifyFormOfStructuralChange() {
        $widget[0].dispatchEvent(new Event('input', {bubbles: true}));
    }

    function refreshAddButtonState() {
        var key = $picker.val();
        var target = $newTarget.val();
        if (key && target) {
            $addBtn.removeAttr('disabled');
        } else {
            $addBtn.attr('disabled', 'disabled');
        }
    }

    function readTargetOptionsFromPicker() {
        var $selected = $picker.find('option:selected');
        var raw = $selected.attr('data-target-options');
        if (!raw) { return null; }
        try { return JSON.parse(raw); } catch (e) { return null; }
    }

    $picker.on('change', function () {
        var options = readTargetOptionsFromPicker();
        destroySelect2($newTarget);
        $newTarget.empty();
        if (options === null) {
            $newTarget.append('<option value=""></option>').attr('disabled', 'disabled');
        } else {
            $newTarget.append('<option value=""></option>');
            for (var value in options) {
                if (value === 'skip') { continue; } // skip is not a valid mapped target
                if (!Object.prototype.hasOwnProperty.call(options, value)) { continue; }
                $newTarget.append('<option value="' + encode(value) + '">' + encode(options[value]) + '</option>');
            }
            $newTarget.removeAttr('disabled');
        }
        initSelect2($newTarget, {placeholder: @json((string) $pickTargetLabel), allowClear: true});
        refreshAddButtonState();
    });

    function commitPickerRow() {
        var key = $picker.val();
        var target = $newTarget.val();
        var direction = $newDirection.length ? $newDirection.val() : 'pull';
        if (!key || !target) { return; }

        var $selected = $picker.find('option:selected');
        var label = $selected.attr('data-label') || $selected.text();
        var optionsMap = readTargetOptionsFromPicker() || {};

        var targetOptions = '';
        for (var v in optionsMap) {
            if (!Object.prototype.hasOwnProperty.call(optionsMap, v)) { continue; }
            if (v === 'skip') { continue; }
            var sel = String(v) === String(target) ? ' selected' : '';
            targetOptions += '<option value="' + encode(v) + '"' + sel + '>' + encode(optionsMap[v]) + '</option>';
        }

        var directionOptions = @json((object) $direction_options);
        var directionOptionsHtml = optionsHtml(directionOptions, direction);

        var html = template
            .split('__KEY__').join(encode(key))
            .split('__LABEL__').join(encode(label))
            .split('__TARGET_OPTIONS__').join(targetOptions)
            .split('__DIRECTION_OPTIONS__').join(directionOptionsHtml);

        var $newRow = $(html);
        $empty.before($newRow);
        // Init select2 on the freshly inserted row's target and
        // direction selects so their styling matches the initially-
        // server-rendered rows.
        $newRow.find('.mapping-picker-row-target').each(function () {
            initSelect2($(this));
        });
        applyIconsToNewContent($newRow);
        $selected.remove();
        $picker.val('').trigger('change');
        destroySelect2($newTarget);
        $newTarget.empty().append('<option value=""></option>').attr('disabled', 'disabled');
        initSelect2($newTarget, {placeholder: @json((string) $pickTargetLabel), allowClear: true});
        if ($newDirection.length) { $newDirection.val('pull').trigger('change'); }
        refreshEmptyState();
        refreshAddButtonState();
        notifyFormOfStructuralChange();
    }

    // Auto-commit the picker row into a real table row as soon as
    // both fields have content. Committing produces a visible row so
    // admins abandon by clicking × on that row, same UX as any
    // existing row.
    $newTarget.on('change', function () {
        refreshAddButtonState();
        if ($picker.val() && $newTarget.val()) {
            commitPickerRow();
        }
    });

    $addBtn.on('click', commitPickerRow);

    $rows.on('click', '.mapping-picker-remove', function () {
        var $row = $(this).closest('tr');
        var key = $row.data('field-key');
        var label = $row.find('td:first').text().trim();

        // Destroy select2 on the row's selects before removing the DOM
        // node so we don't leak select2's hidden shadow elements.
        destroySelect2($row.find('.mapping-picker-row-target'));
        destroySelect2($row.find('.mapping-picker-row-direction'));

        // Reconstruct the target-options data attribute so re-adding
        // the extra rebuilds the target dropdown with the right options.
        var optionsMap = {};
        $row.find('.mapping-picker-row-target option').each(function () {
            optionsMap[$(this).val()] = $(this).text();
        });
        // Preserve the "skip" option in the data-target-options set
        // even though it isn't rendered on the row-side target select.
        // The picker JS strips it out when repopulating the new-row
        // dropdown, so keeping it here keeps the shape consistent
        // with the server-rendered picker options.
        if (!optionsMap.hasOwnProperty('skip')) {
            optionsMap = Object.assign({skip: 'Skip'}, optionsMap);
        }

        $row.remove();
        $picker.append(
            $('<option>')
                .val(key)
                .text(label)
                .attr('data-label', label)
                .attr('data-target-options', JSON.stringify(optionsMap))
        );
        $picker.trigger('change');
        refreshEmptyState();
        notifyFormOfStructuralChange();
    });

    // Explicit init of the picker-row target + direction and every
    // server-rendered row's selects. snipeit.js does auto-init on
    // .select2, but running it here means the widget stays correct
    // when snipeit.js's timing isn't perfectly ordered against this
    // widget's inline script.
    initSelect2($picker, {placeholder: @json((string) $pickExtraLabel), allowClear: true});
    initSelect2($newTarget, {placeholder: @json((string) $pickTargetLabel), allowClear: true});
    if ($newDirection.length) {
        initSelect2($newDirection, {minimumResultsForSearch: Infinity});
    }
    $rows.find('.mapping-picker-row-target').each(function () {
        initSelect2($(this));
    });
    // Direction selects skip the per-widget initSelect2 because they
    // carry js-icon-select and get picked up by the sync-adapter
    // form's shared snipeApplyIconRenderer at page-load time.

    refreshEmptyState();
    refreshAddButtonState();
});
</script>
