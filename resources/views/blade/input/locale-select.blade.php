@props([
    'name' => 'locale',
    'selected' => null,
])
<select
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'select2', 'style' => 'width:100%']) }}
    aria-label="{{ $name }}"
    data-placeholder="{{ trans('localizations.select_language') }}"
>
    <option value=""  role="option">{{ trans('localizations.select_language') }}</option>'
    @foreach (trans('localizations.languages') as $abbr => $locale)
        <option
            value="{{ $abbr }}"
            role="option"
            @selected($abbr == $selected)
            aria-selected="{{ $abbr == $selected ? 'true' : 'false' }}"
        >
            {{ $locale }}
        </option>
    @endforeach
</select>
