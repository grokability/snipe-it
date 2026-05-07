@props([
    'label',
    'copy_what' => null,
    'icon_type' => null,
    'hide_if_null' => false,
    'align' => 'left',
])

@if ($hide_if_null!='true')
    <dt>
        @if (isset($icon_type))
            <x-icon type="{{ $icon_type }}" class="fa-fw"/>
        @endif
        {{ $label }}
    </dt>
    <dd {{ $attributes->merge(['style' => 'text-align: '.$align.' !important;']) }}>
        @if ((!$slot->isEmpty()) && ($copy_what!=''))
            <x-copy-to-clipboard copy_what="{{ $copy_what }}">{{ $slot }}</x-copy-to-clipboard>
        @elseif (!$slot->isEmpty())
            {{ $slot }}
        @else
            <span class="text-muted"><em>{{ trans('general.no_value') }}</em></span>
        @endif
    </dd>
@endif