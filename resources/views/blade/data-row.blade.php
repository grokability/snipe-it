@props([
    'label',
    'copy_what' => null,
    'icon_type' => null,
    'hide_if_null' => false,
    'align' => 'left',
])

@if ($hide_if_null!='true')
    @php
        // Render the slot once. ComponentSlot::isEmpty() re-renders
        // the slot each call, which can double DB queries for data-row
        // contents that walk a relation. Also used to tag empty rows
        // for print-mode suppression.
        $hasValue = ! $slot->isEmpty();
        $printHideClass = $hasValue ? '' : 'hidden-print-empty-row';
    @endphp
    <dt class="{{ $printHideClass }}">
        @if (isset($icon_type))
            <x-icon type="{{ $icon_type }}" class="fa-fw"/>
        @endif
        {{ $label }}
    </dt>
    <dd {{ $attributes->merge(['class' => $printHideClass, 'style' => 'text-align: '.$align.' !important;']) }}>
        @if ($hasValue && ($copy_what!=''))
            <x-copy-to-clipboard copy_what="{{ $copy_what }}">{{ $slot }}</x-copy-to-clipboard>
        @elseif ($hasValue)
            {{ $slot }}
        @else
            <span class="text-muted"><em>{{ trans('general.no_value') }}</em></span>
        @endif
    </dd>
@endif