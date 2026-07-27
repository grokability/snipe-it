@props([
    'name',
    'id' => null,
    'value' => '',
    'required' => false,
    'placeholder' => null,
    'ignoreAutofill' => false,
])

@php
    $fieldId = $id ?? $name;
@endphp

{{-- Password input with a show/hide toggle. Toggle state lives in Alpine
     so it survives Livewire re-renders (jQuery-based .toggle-password
     handler used elsewhere fights morphdom on wire:model.live fields).
     Extra $attributes get merged onto the <input> so wire:model,
     autocomplete, aria-* etc. flow through. --}}
<div class="input-group" x-data="{ shown: false }">
    <input
        {{ $attributes->merge(['class' => 'form-control']) }}
        :type="shown ? 'text' : 'password'"
        type="password"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        autocomplete="new-password"
        @if ($ignoreAutofill)
            data-1p-ignore="true"
            data-lpignore="true"
            data-bwignore="true"
            data-form-type="other"
        @endif
        @required($required)
    />
    <span class="input-group-addon" style="padding: 0;">
        {{-- Plain <button> without btn-link — btn-link inherits the
             theme link color (blue), which stands out wrong in an input
             addon. Inline reset styles give the eye the addon's default
             text color (dark gray) while keeping button semantics for
             keyboard focus + screen reader announcement. --}}
        <button
            type="button"
            x-on:click="shown = !shown"
            x-bind:aria-pressed="shown ? 'true' : 'false'"
            style="border: 0; background: transparent; padding: 6px 12px; color: inherit; cursor: pointer;"
        >
            <i class="fa fa-fw" x-bind:class="shown ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
            <span class="sr-only">{{ trans('general.toggle_password_visibility') }}</span>
        </button>
    </span>
</div>
