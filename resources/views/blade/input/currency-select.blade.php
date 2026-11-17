@use('App\Helpers\CurrencyHelper')
@props([
    'name' => 'currency',
    'selected' => null,
])

@php
    $codes = CurrencyHelper::codes();
@endphp

<select
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'select2', 'style' => 'width: 100%']) }}
    aria-label="{{ $name }}"
    data-placeholder="{{ trans('localizations.select_currency') }}"
    data-allow-clear="true"
>
    <option value=""></option>
    @foreach($codes as $code)
        @php
            $symbol = CurrencyHelper::symbol($code);
            $label = $symbol ? $code.' ('.$symbol.')' : $code;
        @endphp
        <option value="{{ $code }}" {{ $selected === $code ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach

    {{-- Preserve any pre-existing non-ISO value so round-trips don't
         wipe custom entries like a bare symbol or vendor label. --}}
    @if ($selected !== null && $selected !== '' && ! in_array($selected, $codes, true))
        <option value="{{ e($selected) }}" selected>{{ e($selected) }} *</option>
    @endif
</select>
