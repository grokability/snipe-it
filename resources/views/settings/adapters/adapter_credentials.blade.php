{{-- The one settings partial every ConfigurableAdapter uses.
     Iterates the adapter's credentialSchema() and renders each
     declared field as either plain text or a password (with show/hide
     toggle) based on its `secret` flag. Everything ELSE on the form
     (URL, active toggle, heartbeat toggle, per-field mapping) lives in
     the shared form-shell component.

     Field names AND ids are prefixed with the instance slug so each
     adapter's form has fully unique DOM identifiers. Without the
     prefix every tab-pane renders the same input id and browsers
     link them, causing typed values to visually propagate across
     tabs. --}}
@php
    $slug = $adapter->name();
    $locked = config('app.lock_passwords') === true;
@endphp

@if ($adapter->supportsGroupScoping() && $adapter->isEnabled())
    {{-- Refresh-groups action lives outside the config form because
         it POSTs to a different route (settings.adapters.refresh_groups)
         and HTML disallows nested form elements. Rendering it above
         the config form makes it visually close to the mapping table
         it populates. --}}
    <form
        method="POST"
        action="{{ route('settings.adapters.refresh_groups', $adapter->name()) }}"
        style="margin-bottom: 15px; text-align: right;"
    >
        @csrf
        <button
            type="submit"
            class="btn btn-default btn-sm"
            @disabled($locked || ! $adapter->isEnabled())
        >
            <x-icon type="sync" />
            {{ trans('admin/settings/sync_adapters.refresh_groups', ['label' => $adapter->vendorGroupLabel()]) }}
        </button>
    </form>
@endif

@if ($adapter->supportsVendorCustomFields() && $adapter->isEnabled())
    {{-- Parallel to the refresh-groups action: pulls the vendor's
         current custom-field list so extraFields() can offer each as
         a mappable target. Same outside-the-config-form rationale. --}}
    <form
        method="POST"
        action="{{ route('settings.adapters.refresh_custom_fields', $adapter->name()) }}"
        style="margin-bottom: 15px; text-align: right;"
    >
        @csrf
        <button
            type="submit"
            class="btn btn-default btn-sm"
            @disabled($locked || ! $adapter->isEnabled())
        >
            <x-icon type="sync" />
            {{ trans('admin/settings/sync_adapters.refresh_custom_fields') }}
        </button>
    </form>
@endif

<x-sync-adapter-form :adapter="$adapter">
    @foreach ($adapter->credentialSchema() as $field)
        @php
            $fieldName = $slug.'_'.$field['key'];
            $isSecret = $field['secret'] ?? false;
            $isRequired = $field['required'] ?? true;
            $storedValue = $adapter->credentialForDisplay($field['key']);
        @endphp
        <x-form.row
            :label="$field['label']"
            :name="$fieldName"
            input_div_class="col-md-8"
            :help_text="$field['help'] ?? null"
            :required="$isRequired"
        >
            <x-slot:input>
                @if ($locked)
                    <x-input.text
                        :name="$fieldName"
                        value="XXXXXXXXXXXXXXXXXXXXXXX"
                        disabled
                    />
                @elseif ($isSecret)
                    <x-input.password
                        :name="$fieldName"
                        :value="old($fieldName, $storedValue)"
                        :required="$isRequired"
                        ignoreAutofill
                    />
                @else
                    <x-input.text
                        :name="$fieldName"
                        :value="old($fieldName, $storedValue)"
                        :required="$isRequired"
                    />
                @endif
            </x-slot:input>
        </x-form.row>
    @endforeach
</x-sync-adapter-form>

<x-sync-adapter-panel :adapter="$adapter" />
