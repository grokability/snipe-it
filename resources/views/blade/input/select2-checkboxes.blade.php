{{-- AJAX-paginated select2 skinned as a select2-checkboxes.

     Props:
       name         : form field name, submitted as name[].
       label        : control label.
       endpoint     : selectlist endpoint slug (companies, locations, users...).
       selected     : array of pre-selected ids (optional).
       modelClass   : Eloquent model class for pre-rendering option text and
                      tag_color on initial page load. Optional. When omitted
                      pre-selected ids render as id-only text until the user
                      interacts.
       required     : HTML required on the underlying select (optional).
       companyId    : forwarded as data-company-id so the endpoint can scope.
       excludeId    : forwarded as data-exclude-id.
       onlyTopLevel : forwarded as data-only-top-level (companies hierarchy).
       help_text    : optional help text under the control.
       countLabel   : "{n} selected" by default, overrideable per locale.
       placeholder  : placeholder for the control + dropdown search field.
       selectAll    : enable the "Select all / Clear" row in the dropdown (default true).
       maxSelectAll : optional per-widget ceiling for ?all=1 (clamped by MAX_UNPAGINATED).
       labelClass   : grid class for the label (default col-md-3).
       inputClass   : grid class for the input column (default col-md-7).
       includeNull  : opt-in sentinel "No value" row (id='null') prepended to the first
                      page of selectlist results. Used by the advanced-filter dropdown
                      widgets to let admins filter rows where the FK is NULL. Default false.
--}}
@props([
    'name',
    'label',
    'endpoint',
    'selected' => [],
    'modelClass' => null,
    'required' => false,
    'companyId' => null,
    'excludeId' => null,
    'onlyTopLevel' => false,
    'help_text' => null,
    'countLabel' => trans('general.checkbox_multiselect_count'),
    'placeholder' => '',
    'selectAll' => true,
    'maxSelectAll' => null,
    'labelClass' => 'col-md-3',
    'inputClass' => 'col-md-7',
    'includeNull' => false,
])

@php
    $selectId = $name.'_select';
    $selectedIds = collect($selected ?? [])->filter()->values();
    // Pre-fetch the selected models (name + tag_color when available) so
    // the chip strip renders colored squares and real text on initial
    // page load, not just the raw id. The tag_color column is optional
    // per model and read via ?-> so models without one fall to null.
    $selectedModels = collect();
    if ($modelClass && $selectedIds->isNotEmpty()) {
        $selectedModels = $modelClass::whereIn('id', $selectedIds->all())->get()->keyBy('id');
    }
@endphp

<div @class(['form-group', 'has-error' => $errors->has($name)])>
    <label for="{{ $selectId }}" class="{{ $labelClass }} control-label">{{ $label }}</label>
    <div class="{{ $inputClass }}">
        <select
            id="{{ $selectId }}"
            name="{{ $name }}[]"
            class="js-select2-checkboxes"
            data-endpoint="{{ $endpoint }}"
            data-placeholder="{{ $placeholder }}"
            data-count-label="{{ $countLabel }}"
            @if ($selectAll)
                data-select-all="1"
                data-select-all-label="{{ trans('general.select_all') }}"
                data-clear-all-label="{{ trans('general.deselect_all') }}"
                @if ($maxSelectAll) data-max-select-all="{{ (int) $maxSelectAll }}" @endif
            @endif
            @if ($companyId) data-company-id="{{ $companyId }}" @endif
            @if ($excludeId) data-exclude-id="{{ $excludeId }}" @endif
            @if ($onlyTopLevel) data-only-top-level="1" @endif
            @if ($includeNull) data-include-null="1" @endif
            style="width: 100%"
            aria-label="{{ $label }}"
            multiple
            @required($required)
        >
            @foreach ($selectedIds as $id)
                @php $model = $selectedModels->get($id); @endphp
                <option
                    value="{{ $id }}"
                    selected="selected"
                    @if ($model?->tag_color) data-tag-color="{{ $model->tag_color }}" @endif
                >{{ $model?->name ?? $id }}</option>
            @endforeach
        </select>
    </div>

    @if ($help_text)
        <div class="{{ $inputClass }} col-md-offset-3">
            <x-form.help :name="$selectId">{!! $help_text !!}</x-form.help>
        </div>
    @endif

    <div class="col-md-8 col-md-offset-3"><x-form.error :name="$name" /></div>
</div>
