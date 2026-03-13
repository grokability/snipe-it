@props([
    'copy_what' => null,
])

<!-- copy to clipboard -->
<i {{ $attributes->merge(['class' => 'fa-regular fa-clipboard js-copy-link hidden-print fa-fw']) }} style="font-size: 16px;" data-clipboard-target=".js-copy-{{ $copy_what }}" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}">
    <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
</i>

{{--There must not be any spaces or line breaks between the js-copy span and the slot, or it will add a space before the copied value in Firefox :( --}}
@if (!$slot->isEmpty())
<span class="js-copy-{{ $copy_what }}">{{ $slot }}</span>
@endif

