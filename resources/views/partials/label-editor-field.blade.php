@php
    $name = "{$sectionKey}[{$fieldKey}]";
    $value = data_get($config, "{$sectionKey}.{$fieldKey}");
@endphp

<div class="form-group">
    <label class="col-md-4 control-label">
        {{ $field['label'] }}
    </label>

    <div class="col-md-4">
        @if ($field['type'] === 'checkbox')
            <input type="hidden" name="{{ $name }}" value="0">

            <input
                    type="checkbox"
                    name="{{ $name }}"
                    value="1"
                    @checked((bool) $value)
            >

        @elseif ($field['type'] === 'select')
            <select name="{{ $name }}" class="form-control">
                @foreach ($field['options'] as $optionValue => $optionLabel)
                    <option
                            value="{{ $optionValue }}"
                            @selected($value == $optionValue)
                    >
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>

        @else
            <input
                    type="{{ $field['type'] }}"
                    name="{{ $name }}"
                    value="{{ $value }}"
                    class="form-control"
            >
        @endif
    </div>
</div>