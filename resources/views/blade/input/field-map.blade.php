@props([
    'name',
    'options' => [],
    'stored' => [],
    'direction_name' => null,
    'direction_options' => [],
    'direction_icons' => [],
    'stored_directions' => [],
])

@php
    $widgetId = 'field-map-'.\Illuminate\Support\Str::random(8);
    $availableOptions = collect($options)->except(array_keys($stored))->all();
    $showDirection = $direction_name !== null && ! empty($direction_options);
@endphp

<div class="field-map-widget" data-field-map-widget="{{ $widgetId }}">
    <table class="table table-condensed" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 30%">{{ trans('admin/settings/sync_adapters.field_map_column_field') }}</th>
                <th>{{ trans('admin/settings/sync_adapters.field_map_column_path') }}</th>
                @if ($showDirection)
                    <th style="width: 20%">{{ trans('admin/settings/sync_adapters.field_map_column_direction') }}</th>
                @endif
                <th style="width: 60px"></th>
            </tr>
        </thead>
        <tbody class="field-map-rows">
            @foreach ($stored as $storedKey => $storedValue)
                <tr data-field-key="{{ $storedKey }}">
                    <td>{{ $options[$storedKey] ?? $storedKey }}</td>
                    <td>
                        <input
                            type="text"
                            name="{{ $name }}[{{ $storedKey }}]"
                            value="{{ $storedValue }}"
                            class="form-control"
                            aria-label="{{ $options[$storedKey] ?? $storedKey }} dot-path"
                        >
                    </td>
                    @if ($showDirection)
                        <td>
                            <x-input.icon-select
                                :name="$direction_name . '[' . $storedKey . ']'"
                                :options="$direction_options"
                                :icons="$direction_icons"
                                :selected="$stored_directions[$storedKey] ?? 'pull'"
                                class="field-map-row-direction"
                                data-minimum-results-for-search="Infinity"
                                :aria-label="($options[$storedKey] ?? $storedKey) . ' direction'"
                            />
                        </td>
                    @endif
                    <td>
                        <button
                            type="button"
                            class="btn btn-danger field-map-remove"
                            aria-label="{{ trans('button.delete') }}"
                        >
                            <x-icon type="x" />
                        </button>
                    </td>
                </tr>
            @endforeach
            <tr class="field-map-empty" @if (count($stored) > 0) hidden @endif>
                <td colspan="{{ $showDirection ? 4 : 3 }}" class="text-muted text-center">
                    <em>{{ trans('admin/settings/sync_adapters.field_map_empty') }}</em>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="field-map-picker-row">
                <td>
                    <label class="sr-only" for="{{ $widgetId }}-picker">{{ trans('admin/settings/sync_adapters.field_map_pick_field') }}</label>
                    <select
                        id="{{ $widgetId }}-picker"
                        name="{{ $name }}_pending[key]"
                        class="select2 field-map-picker"
                        style="width: 100%"
                        data-placeholder="{{ trans('admin/settings/sync_adapters.field_map_pick_field') }}"
                    >
                        <option value=""></option>
                        @foreach ($availableOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <label class="sr-only" for="{{ $widgetId }}-value">{{ trans('admin/settings/sync_adapters.field_map_new_path') }}</label>
                    <input
                        id="{{ $widgetId }}-value"
                        name="{{ $name }}_pending[value]"
                        type="text"
                        class="form-control field-map-new-value"
                        placeholder="{{ trans('admin/settings/sync_adapters.field_map_new_path') }}"
                    >
                </td>
                @if ($showDirection)
                    <td>
                        <x-input.icon-select
                            :id="$widgetId . '-direction'"
                            :name="$direction_name . '_pending[value]'"
                            :options="$direction_options"
                            :icons="$direction_icons"
                            selected="pull"
                            class="field-map-new-direction"
                            data-minimum-results-for-search="Infinity"
                        />
                    </td>
                @endif
                <td>
                    <button
                        type="button"
                        class="btn btn-primary field-map-add"
                        aria-label="{{ trans('button.add') }}"
                        disabled
                    >
                        <x-icon type="create" />
                    </button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<script type="text/template" data-field-map-template="{{ $widgetId }}">
    <tr data-field-key="__KEY__">
        <td>__LABEL__</td>
        <td>
            <input type="text" name="{{ $name }}[__KEY__]" value="__VALUE__" class="form-control" aria-label="__LABEL__ dot-path">
        </td>
        @if ($showDirection)
            <td>
                <select name="{{ $direction_name }}[__KEY__]" class="select2 js-icon-select field-map-row-direction" style="width: 100%" data-minimum-results-for-search="Infinity" aria-label="__LABEL__ direction">
                    __DIRECTION_OPTIONS__
                </select>
            </td>
        @endif
        <td>
            <button type="button" class="btn btn-danger field-map-remove" aria-label="{{ trans('button.delete') }}">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
        </td>
    </tr>
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var widgetId = @json($widgetId);
    var $widget = $('[data-field-map-widget="' + widgetId + '"]');
    if ($widget.length === 0) { return; }

    var $rows = $widget.find('.field-map-rows');
    var $empty = $widget.find('.field-map-empty');
    var $picker = $widget.find('.field-map-picker');
    var $newValue = $widget.find('.field-map-new-value');
    var $newDirection = $widget.find('.field-map-new-direction');
    var $addBtn = $widget.find('.field-map-add');
    var template = $('[data-field-map-template="' + widgetId + '"]').html();
    var directionOptionsMap = @json((object) $direction_options);
    var directionIconsMap = @json((object) $direction_icons);

    function initSelect2($el, opts) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2(opts || {});
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

    function destroySelect2($el) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
    }

    function refreshEmptyState() {
        var hasRows = $rows.find('tr:not(.field-map-empty)').length > 0;
        if (hasRows) { $empty.attr('hidden', ''); } else { $empty.removeAttr('hidden'); }
    }

    function encode(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function optionsHtml(map, selected) {
        var html = '';
        for (var value in map) {
            if (!Object.prototype.hasOwnProperty.call(map, value)) {
                continue;
            }
            var sel = String(value) === String(selected) ? ' selected' : '';
            var icon = directionIconsMap && directionIconsMap[value] ? ' data-icon="' + encode(directionIconsMap[value]) + '"' : '';
            html += '<option value="' + encode(value) + '"' + icon + sel + '>' + encode(map[value]) + '</option>';
        }
        return html;
    }

    function notifyFormOfStructuralChange() {
        $widget[0].dispatchEvent(new Event('input', {bubbles: true}));
    }

    function commitPickerRow() {
        var key = $picker.val();
        if (!key) { return; }
        var value = $newValue.val();
        if (value.trim() === '') {
            return;
        }
        var $selected = $picker.find('option:selected');
        var label = $selected.text();
        var direction = $newDirection.length ? $newDirection.val() : 'pull';

        var html = template
            .split('__KEY__').join(encode(key))
            .split('__LABEL__').join(encode(label)).split('__VALUE__').join(encode(value)).split('__DIRECTION_OPTIONS__').join(optionsHtml(directionOptionsMap, direction));

        var $newRow = $(html);
        $empty.before($newRow);
        applyIconsToNewContent($newRow);
        $selected.remove();
        $picker.val('').trigger('change');
        $newValue.val('');
        if ($newDirection.length) {
            $newDirection.val('pull').trigger('change');
        }
        refreshEmptyState();
        refreshAddEnabled();
        notifyFormOfStructuralChange();
    }

    $addBtn.on('click', commitPickerRow);

    // Auto-commit the picker row into a real table row as soon as
    // both fields have content. Firing on picker change catches the
    // "typed path first, then picked destination" order. Firing on
    // path blur catches the "picked destination first, then typed
    // path" order. Committing produces a visible row so admins
    // abandon by clicking × on that row, same UX as any existing
    // row, instead of hunting for hidden state.
    $picker.on('change.autoCommit', function () {
        if ($picker.val() && $newValue.val().trim() !== '') {
            commitPickerRow();
        }
    });
    $newValue.on('blur.autoCommit', function () {
        if ($picker.val() && $newValue.val().trim() !== '') {
            commitPickerRow();
        }
    });

    $rows.on('click', '.field-map-remove', function () {
        var $row = $(this).closest('tr');
        var key = $row.data('field-key');
        var label = $row.find('td:first').text().trim();
        destroySelect2($row.find('.field-map-row-direction'));
        $row.remove();
        $picker.append($('<option>').val(key).text(label));
        $picker.trigger('change');
        refreshEmptyState();
        notifyFormOfStructuralChange();
    });

    // The field-picker select2 still needs its own init because it is
    // NOT an .js-icon-select. The direction picker + each row's
    // direction select ARE js-icon-select and get picked up by the
    // parent form's shared snipeApplyIconRenderer on page load.
    initSelect2($picker, {
        placeholder: @json((string) trans('admin/settings/sync_adapters.field_map_pick_field')),
        allowClear: true,
    });

    function refreshAddEnabled() {
        var ok = !!$picker.val() && $newValue.val().trim() !== '';
        $addBtn.prop('disabled', !ok);
    }

    $picker.on('change', refreshAddEnabled);
    $newValue.on('input change', refreshAddEnabled);

    refreshEmptyState();
    refreshAddEnabled();
});
</script>
