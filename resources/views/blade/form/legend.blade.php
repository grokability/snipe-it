@props([
    'help_text' => null,
    'icon' => null,
])
<!-- Form Legend Component -->
<legend class="callout callout-legend">
    <h4>
       {{ $slot }}
    </h4>

    @if ($help_text)
        <x-form.legend-help :icon="$icon">
            {!!  $help_text !!}
        </x-form.legend-help>
    @endif
</legend>