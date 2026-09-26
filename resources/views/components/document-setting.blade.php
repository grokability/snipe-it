@props(['item', 'section' => 'header_config', 'field', 'default' => '', 'type' => 'text', 'options' => [], 'min' => null, 'max' => null, 'step' => 1])
@php
    $path = $section.'.'.$field;
    $id = $section.'_'.$field;
    $value = old($path, $item->{$section}[$field] ?? $default);
@endphp
<div class="form-group document-setting">
    <label class="col-md-4 control-label" for="{{ $id }}">{{ trans('documents.custom.'.$field) }}</label>
    <div class="col-md-8">
        @if ($type === 'checkbox')
            <input type="hidden" name="{{ $section }}[{{ $field }}]" value="0">
            <input id="{{ $id }}" type="checkbox" name="{{ $section }}[{{ $field }}]" value="1" @checked($value)>
        @elseif ($type === 'select')
            <select class="form-control" id="{{ $id }}" name="{{ $section }}[{{ $field }}]">
                @foreach ($options as $key => $label)<option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>@endforeach
            </select>
        @elseif ($type === 'textarea')
            <textarea class="form-control placeholder-target" id="{{ $id }}" name="{{ $section }}[{{ $field }}]" rows="3" maxlength="{{ $max ?? 1000 }}">{{ $value }}</textarea>
        @else
            <input class="form-control {{ $type === 'text' ? 'placeholder-target' : '' }}" id="{{ $id }}" name="{{ $section }}[{{ $field }}]" type="{{ $type }}" value="{{ $value }}" @if ($type === 'number') min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" @else maxlength="{{ $max ?? 191 }}" @endif>
        @endif
        <x-form.error :name="$path" />
    </div>
</div>
