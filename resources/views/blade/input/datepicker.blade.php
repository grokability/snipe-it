@props([
    'value' => '',
    'required' => '',
    'end_date' => null,
    'col_size_class' => null,
])

<!-- Datepicker -->
<div class="input-group date {{ $col_size_class }}" data-provide="datepicker" data-date-today-highlight="true" data-date-language="{{ auth()->user()->locale }}" data-date-locale="{{ auth()->user()->locale }}" data-date-format="yyyy-mm-dd" data-date-autoclose="true" data-date-clear-btn="true"{{ $end_date ? ' data-date-end-date=' . $end_date : '' }}>
    <input type="text" placeholder="{{ trans('general.select_date') }}" value="{{ $value }}" maxlength="10" {{ $attributes->merge(['class' => 'form-control']) }} {{ $required=='1' ? 'required' : '' }}>
    <span class="input-group-addon"><x-icon type="calendar" /></span>

</div>