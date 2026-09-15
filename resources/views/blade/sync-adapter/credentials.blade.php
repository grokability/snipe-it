{{-- The settings partial EVERY ConfigurableAdapter uses.
     Iterates the adapter's settingsSchema() and renders each
     declared field as either plain text or a password (with show/hide
     toggle) based on its `secret` flag. Everything ELSE on the form
     (URL, active toggle, heartbeat toggle, per-field mapping) lives in
     the shared form-shell component.

     Field names AND ids are prefixed with the instance slug so each
     adapter's form has fully unique DOM identifiers to avoid
     entered values to visually propagate across tabs. --}}
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

<x-sync-adapter.form :adapter="$adapter">
    @php
        $settingsSections = $adapter->settingsSections();
        // Track section transitions across the loop so we can open
        // and close <fieldset> wrappers around each contiguous run
        // of same-sectioned schema entries. Un-fieldset-sectioned entries
        // (section => null) render outside any fieldset.
        //
        // Starts at baseUrlSection() so if the shell already opened a
        // fieldset around the Base URL row, the schema loop's first
        // same-sectioned entries render into that fieldset instead of
        // opening a duplicate. When the loop transitions to a
        // different section, the close-then-open handles the switch.
        $currentSection = $adapter->baseUrlSection();
    @endphp
    @foreach ($adapter->settingsSchema() as $field)
        @php
            $fieldName = $slug.'_'.$field['key'];
            $fieldType = $field['type'] ?? 'text';
            $isSecret = $field['secret'] ?? false;
            $isRequired = $field['required'] ?? true;
            $storedValue = $adapter->credentialForDisplay($field['key']);
            // Optional conditional visibility. Schema entries declare
            // visible_when: [source_key => value | [values]] and the
            // rendered field gets wrapped in a data-driven div. Inline
            // JS below the loop wires the show/hide logic.
            $visibleWhen = $field['visible_when'] ?? null;
            $section = $field['section'] ?? null;
        @endphp
        @if ($section !== $currentSection)
            @if ($currentSection !== null)
                </fieldset>
    @endif
    @if ($section !== null && isset($settingsSections[$section]))
        @php
            // Hoist the section-help lookup out of the Blade attribute
            // to sidestep an operator-precedence trap: inside a
            // `help_text="{!! ... !!}"` string attribute Blade compiles
            // the `??` fallback as `'' . $arr['help'] ?? ''`, and the
            // outer concat binds tighter than `??`, so PHP evaluates
            // $arr['help'] unconditionally and warns on missing keys.
            // Regular PHP `??` on its own line does the right thing.
            $sectionHelp = $settingsSections[$section]['help'] ?? '';
        @endphp
        <fieldset>
            <x-form.legend icon="tip" help_text="{!! $sectionHelp !!}">
                {{ $settingsSections[$section]['title'] }}
            </x-form.legend>
            @endif
            @php $currentSection = $section; @endphp
            @endif
            @if (is_array($visibleWhen))
                @php
                    $depKey = array_key_first($visibleWhen);
                    $depValues = (array) $visibleWhen[$depKey];
                @endphp
                <div
                    class="adapter-conditional-field"
                    data-visible-when-field="{{ $slug.'_'.$depKey }}"
                    data-visible-when-value="{{ implode(',', $depValues) }}"
                >
                    @endif
        @if ($fieldType === 'checkbox')
            <x-form.checkbox-row
                :name="$fieldName"
                :checked="old($fieldName, $storedValue === '1')"
                :label="$field['label']"
                :help_text="$field['help'] ?? null"
                :disabled="$locked"
            />
        @elseif ($fieldType === 'category')
            {{-- Per-record category selector. Empty means "fall back
                 to the instance-wide default_category_id". Uses the
                 same AJAX-backed select2 component as the shell's
                 default category picker. --}}
            <x-input.category-select
                :name="$fieldName"
                :label="$field['label']"
                :selected="old($fieldName, $storedValue !== '' ? (int) $storedValue : null)"
                :categoryType="$field['categoryType'] ?? 'asset'"
                :help_text="$field['help'] ?? null"
                hideNewButton
            />
                    @elseif ($fieldType === 'field_map')
                        {{-- field_map entries are rendered by the shell's own
                             fieldset below the operational checkboxes, so they
                             sit alongside the standard mapping controls instead
                             of getting lost among the credential inputs. Nothing
                             to render here. --}}
        @else
            <x-form.row
                :label="$field['label']"
                :name="$fieldName"
                input_div_class="col-md-8"
                help_html="{!! $field['help'] ?? '' !!}"
                :required="$isRequired"
            >
                <x-slot:input>
                    @if ($locked)
                        <x-input.text
                            :name="$fieldName"
                            value="XXXXXXXXXXXXXXXXXXXXXXX"
                            disabled
                        />
                    @elseif ($fieldType === 'select')
                        <x-input.select
                            :name="$fieldName"
                            :options="$field['options'] ?? []"
                            :selected="old($fieldName, $storedValue)"
                            :required="$isRequired"
                            style="width: 100%"
                        />
                    @elseif ($fieldType === 'multiselect')
                        @php
                            $selectedValues = old(
                                $fieldName,
                                is_string($storedValue) && $storedValue !== '' ? (json_decode($storedValue, true) ?: []) : []
                            );
                            // First-render fallback: when nothing is
                            // stored yet, adapters can declare a
                            // `default` array to seed the selection so
                            // admins see the intended baseline instead
                            // of an empty widget.
                            if (empty($selectedValues) && ! empty($field['default']) && is_array($field['default'])) {
                                $selectedValues = $field['default'];
                            }
                        @endphp
                        <x-input.select
                            :name="$fieldName . '[]'"
                            :options="$field['options'] ?? []"
                            :selected="$selectedValues"
                            multiple
                            style="width: 100%"
                        />
                    @elseif ($fieldType === 'textarea')
                        <x-input.textarea
                            :name="$fieldName"
                            :value="old($fieldName, $storedValue)"
                            :required="$isRequired"
                            :placeholder="$field['placeholder'] ?? null"
                            rows="8"
                        />
                    @elseif ($isSecret)
                        <x-input.password
                            :name="$fieldName"
                            :value="old($fieldName, $storedValue)"
                            :required="$isRequired"
                            ignoreAutofill
                        />
                    @elseif (! empty($field['url_prefix']))
                        {{-- Input-group that shows the current Base URL
                             as a prefix addon so admins see the full
                             URL that will be assembled at request
                             time. Marker class on the addon lets the
                             live-update JS at the bottom of this
                             partial sync new Base URL keystrokes into
                             this label without a page reload. --}}
                        <div class="input-group">
                            <span
                                class="input-group-addon js-adapter-url-prefix"
                                data-adapter-slug="{{ $slug }}"
                                data-placeholder="{{ trans('admin/settings/sync_adapters.custom_url_prefix_base_url_placeholder') }}"
                            >{{ $adapter->getUrl() ?: trans('admin/settings/sync_adapters.custom_url_prefix_base_url_placeholder') }}</span>
                            <x-input.text
                                :name="$fieldName"
                                :value="old($fieldName, $storedValue)"
                                :required="$isRequired"
                                :placeholder="$field['placeholder'] ?? null"
                            />
                        </div>
                    @else
                        <x-input.text
                            :name="$fieldName"
                            :value="old($fieldName, $storedValue)"
                            :required="$isRequired"
                        />
                    @endif
                </x-slot:input>
            </x-form.row>
                    @endif
                    @if (is_array($visibleWhen))
                </div>
        @endif
    @endforeach
            @if ($currentSection !== null)
                {{-- Close the last section's fieldset when the schema loop exits inside one. --}}
        </fieldset>
    @endif
</x-sync-adapter.form>

<x-sync-adapter.panel :adapter="$adapter" />

{{-- JS handlers for [data-visible-when-field] conditional-visibility
     and .js-adapter-url-prefix live-update live in
     resources/assets/js/snipeit.js. Both are data-attribute driven
     so nothing on this partial needs inline script anymore. --}}
