@props([
    'help_text' => null,
])
<!-- Form Legend Component -->
<legend class="callout callout-legend">
    <h4>
       {{ $slot }}
    </h4>

    @if ($help_text)
        <x-form-legend-help>
            {!!  $help_text !!}
        </x-form-legend-help>
    @endif
</legend>