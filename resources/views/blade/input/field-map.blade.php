{{-- Repeater-style picker for building a {snipe_field: vendor_path}
     mapping. Renders one row per already-configured entry (label +
     editable dot-path input + remove button), plus a picker row
     below (dropdown of remaining options + input + Add button).
     Values submit as $name[$snipeFieldKey]=$path so the server sees
     an associative array on save.

     Widget is self-contained: inline jQuery IIFE, no snipeit.js
     changes required. Multiple instances on one page each get a
     unique data-field-map-widget id so the handlers don't cross-
     wire. --}}
@props([
    'name',
    'options' => [],
    'stored' => [],
])

@php
    $widgetId = 'field-map-'.\Illuminate\Support\Str::random(8);
    $availableOptions = collect($options)->except(array_keys($stored))->all();
@endphp

<div class="field-map-widget" data-field-map-widget="{{ $widgetId }}">
    <table class="table table-condensed" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 35%">{{ trans('admin/settings/sync_adapters.field_map_column_field') }}</th>
                <th>{{ trans('admin/settings/sync_adapters.field_map_column_path') }}</th>
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
                <td colspan="3" class="text-muted text-center">
                    <em>{{ trans('admin/settings/sync_adapters.field_map_empty') }}</em>
                </td>
            </tr>
        </tbody>
        <tfoot>
            {{-- Picker row lives inside the same table so its three
                 cells inherit the header's column widths (35% / auto
                 / 60px). Puts the select where the "Snipe-IT Field"
                 label is, the new-value input where the "Vendor Dot-
                 Path" input is, and the Add button in the same slot
                 as the row remove buttons. --}}
            <tr class="field-map-picker-row">
                <td>
                    <label class="sr-only" for="{{ $widgetId }}-picker">{{ trans('admin/settings/sync_adapters.field_map_pick_field') }}</label>
                    <select
                        id="{{ $widgetId }}-picker"
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
                        type="text"
                        class="form-control field-map-new-value"
                        placeholder="{{ trans('admin/settings/sync_adapters.field_map_new_path') }}"
                    >
                </td>
                <td>
                    <button
                        type="button"
                        class="btn btn-primary field-map-add"
                        aria-label="{{ trans('button.add') }}"
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
        <td>
            <button type="button" class="btn btn-danger field-map-remove" aria-label="{{ trans('button.delete') }}">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
        </td>
    </tr>
</script>

<script>
// Defer to DOMContentLoaded so jQuery ($) is defined. Inline scripts
// mid-body run before the tail-of-body <script src="jquery"> loads,
// so referencing $ at parse time throws ReferenceError. Vanilla JS
// wrapper here, jQuery inside.
document.addEventListener('DOMContentLoaded', function () {
    var widgetId = @json($widgetId);
    var $widget = $('[data-field-map-widget="' + widgetId + '"]');
    if ($widget.length === 0) { return; }

    var $rows = $widget.find('.field-map-rows');
    var $empty = $widget.find('.field-map-empty');
    var $picker = $widget.find('.field-map-picker');
    var $newValue = $widget.find('.field-map-new-value');
    var $addBtn = $widget.find('.field-map-add');
    var template = $('[data-field-map-template="' + widgetId + '"]').html();

    function refreshEmptyState() {
        var hasRows = $rows.find('tr:not(.field-map-empty)').length > 0;
        $empty.attr('hidden', hasRows ? '' : null);
        if (hasRows) { $empty.attr('hidden', ''); } else { $empty.removeAttr('hidden'); }
    }

    function encode(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    $addBtn.on('click', function () {
        var key = $picker.val();
        if (!key) { return; }
        var $selected = $picker.find('option:selected');
        var label = $selected.text();
        var value = $newValue.val();

        var html = template
            .split('__KEY__').join(encode(key))
            .split('__LABEL__').join(encode(label))
            .split('__VALUE__').join(encode(value));

        $empty.before(html);
        $selected.remove();
        $picker.val('').trigger('change'); // trigger('change') refreshes the select2 display after the option was pulled
        $newValue.val('');
        refreshEmptyState();
    });

    $rows.on('click', '.field-map-remove', function () {
        var $row = $(this).closest('tr');
        var key = $row.data('field-key');
        var label = $row.find('td:first').text().trim();
        $row.remove();
        $picker.append($('<option>').val(key).text(label));
        $picker.trigger('change'); // re-render select2 with the restored option
        refreshEmptyState();
    });

    refreshEmptyState();
});
</script>
