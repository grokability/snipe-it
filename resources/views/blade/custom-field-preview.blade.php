@props([
    'name' => '',
    'element' => 'text',
    'format' => 'ANY',
    'helpText' => '',
    'fieldValues' => '',
])

@php
    $displayName = trim((string) $name) !== '' ? $name : trans('admin/custom_fields/general.field_name');
    $valuesArray = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $fieldValues)), fn ($v) => $v !== ''));
    $formatIcon = \App\Models\CustomField::iconForFormat($format);
@endphp

{{--
    aria-hidden hides the preview from assistive tech (it's a visual
    representation, not a real form field). Interactive widgets — date
    pickers and select2 listbox/checkbox dropdowns — need pointer events and focus to
    open their popups, so pointer-events is applied per-element instead
    of on the wrapper. onkeydown blocks Enter from triggering the outer
    Livewire form's wire:submit while still allowing typing into the
    interactive widgets themselves (select2 search, picker input).
--}}
<div
    class="form-horizontal js-custom-field-preview"
    aria-hidden="true"
    onkeydown="if (event.key === 'Enter') { event.preventDefault(); event.stopPropagation(); }"
>
    <div class="form-group">
        <label class="col-md-4 control-label">{{ $displayName }}</label>
        <div class="col-md-8">

            @switch($element)
                @case('text')
                    @if ($formatIcon)
                        <div class="input-group" style="pointer-events: none;">
                            <input type="text" class="form-control" tabindex="-1" placeholder="{{ trans('admin/custom_fields/general.types.text') }}">
                            <span class="input-group-addon"><x-icon :type="$formatIcon" /></span>
                        </div>
                    @else
                        <input type="text" class="form-control" tabindex="-1" style="pointer-events: none;" placeholder="{{ trans('admin/custom_fields/general.types.text') }}">
                    @endif
                    @break

                @case('listbox')
                    <select
                        wire:key="preview-listbox"
                        class="select2 form-control js-preview-select2"
                        style="width: 100%;"
                    >
                        <option value=""></option>
                        @foreach ($valuesArray as $value)
                            <option>{{ $value }}</option>
                        @endforeach
                    </select>
                    @break

                @case('textarea')
                @case('markdown-textarea')
                    <textarea class="form-control" rows="3" tabindex="-1" style="pointer-events: none;" placeholder="{{ $element === 'markdown-textarea' ? 'Markdown' : trans('admin/custom_fields/general.types.textarea') }}"></textarea>
                    @break

                @case('checkbox')
                    <select
                        wire:key="preview-checkbox"
                        class="select2 form-control js-preview-select2"
                        style="width: 100%;"
                        multiple
                    >
                        @foreach ($valuesArray as $value)
                            <option>{{ $value }}</option>
                        @endforeach
                    </select>
                    @break

                @case('radio')
                    @forelse ($valuesArray as $value)
                        <label class="form-control" style="pointer-events: none;">
                            <input type="radio" tabindex="-1"> {{ $value }}
                        </label>
                    @empty
                        <label class="form-control" style="pointer-events: none;">
                            <input type="radio" tabindex="-1"> {{ trans('admin/custom_fields/general.field_values') }}
                        </label>
                    @endforelse
                    @break

                @case('date_picker')
                    <div
                        wire:key="preview-date-picker"
                        class="input-group date js-preview-datetimepicker"
                        data-provide="datetimepicker"
                        data-format="YYYY-MM-DD"
                        data-default-now="false"
                    >
                        <input type="text" class="form-control" placeholder="YYYY-MM-DD">
                        <span class="input-group-addon"><x-icon type="calendar" /></span>
                    </div>
                    @break

                @case('datetime_picker')
                    <div
                        wire:key="preview-datetime-picker"
                        class="input-group date js-preview-datetimepicker"
                        data-provide="datetimepicker"
                        data-format="YYYY-MM-DD HH:mm:ss"
                        data-default-now="false"
                    >
                        <input type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM:SS">
                        <span class="input-group-addon"><x-icon type="calendar" /></span>
                    </div>
                    @break

                @default
                    <input type="text" class="form-control" tabindex="-1" style="pointer-events: none;">
            @endswitch

            @if (trim((string) $helpText) !== '')
                <p class="help-block">{{ $helpText }}</p>
            @endif

        </div>
    </div>
</div>
