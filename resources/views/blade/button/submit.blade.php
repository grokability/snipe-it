{{-- safety_timeout_ms sets the max time the button stays in its "Processing..."
     state before the client restores it automatically. The default fits a
     typical CRUD write, matching most form submits in the app. Long-running
     actions (backups, imports, sync-adapter runs) should override by passing a
     larger value in milliseconds. Fast actions can lower it. --}}
@props([
    'label' => null,
    'icon' => 'checkmark',
    'disabled' => false,
    'prevent_double_submit' => true,
    'safety_timeout_ms' => null,
])

@php
    $label ??= trans('general.save');
@endphp

<button
    type="submit"
    @unless ($prevent_double_submit) data-allow-double-submit="1" @endunless
    data-processing-label="{{ trans('general.processing') }}"
    @if ($safety_timeout_ms !== null) data-safety-timeout-ms="{{ (int) $safety_timeout_ms }}" @endif
    {{ $attributes->merge(['class' => 'btn btn-primary', 'id' => 'submit_button']) }}
    @disabled($disabled)
>
    @if ($icon)
        <x-icon :type="$icon" />
    @endif
    {{ $label }}
</button>
