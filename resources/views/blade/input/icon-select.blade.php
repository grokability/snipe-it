{{-- Static-options select2 picker with per-option icons.
     options    : value-to-label map, same shape as the plain select
                  input component.
     icons      : value-to-fa-icon-class map. Keys matching an option
                  value get a data-icon attribute. Options without a
                  matching key render as plain text.
     selected   : value of the pre-selected option, or null.
     disabled   : standard disabled flag.
     required   : standard required flag. --}}
@props([
    'name',
    'options' => [],
    'selected' => null,
    'icons' => [],
    'required' => false,
    'disabled' => false,
])

<select
    {{ $attributes->class(['select2', 'form-control', 'js-icon-select']) }}
    name="{{ $name }}"
    style="width: 100%"
    @required($required)
    @disabled($disabled)
>
    @foreach ($options as $value => $label)
        <option
            value="{{ $value }}"
            @if (isset($icons[$value]) && $icons[$value] !== '') data-icon="{{ $icons[$value] }}" @endif
            @selected((string) $selected === (string) $value)
        >{{ $label }}</option>
    @endforeach
</select>
