{{-- Shared form frame for a sync-adapter settings tab. The
     credentials partial drops its schema-driven credential inputs
     into the default slot. The URL row, active/heartbeat toggles,
     and mapping fieldset stay here because they're identical across
     every adapter regardless of credential shape.

     Called from resources/views/blade/sync-adapter/credentials.blade.php
     which passes the hydrated adapter through the `adapter` prop
     and puts the credential inputs in the default slot. --}}
@props(['adapter'])

@php
    $slug = $adapter->name();
    $urlField = $slug.'_url';
    $activeField = $slug.'_active';
    $locked = config('app.lock_passwords') === true;
    // Memoized per-request via Laravel's Str/once so N adapter tab-panes
    // don't each fire their own companies-count query. The pickers below
    // (both the per-instance company scope and the per-vendor-group
    // mapping picker further down) are pure noise on a single-tenant
    // install with zero company rows.
    $hasCompanies = once(fn () => \App\Models\Company::query()->exists());
@endphp
<x-form
    :id="'adapter-form-' . $slug"
    :route="route('settings.adapters.save', $slug)"
    data-sync-url="{{ route('settings.adapters.sync', $slug) }}"
    data-enabled="{{ $adapter->isEnabled() ? '1' : '0' }}"
    data-adapter-slug="{{ $slug }}"
    data-dirty-guard
    data-dirty-guard-warning="{{ trans('admin/settings/sync_adapters.unsaved_changes_before_sync') }}"
>
    <x-demo-callout/>

    {{-- Adapter type is immutable post-create (it keys the class map)
         so render as a static row rather than an editable control.
         Lives at the top of the form so admins recognize the vendor
         they're configuring at a glance without having to read the
         tab label or the browser URL. --}}
    <x-form.static :label="trans('admin/settings/sync_adapters.add_type_label')" content_class="col-md-8">
        {{ $adapter::typeLabel() }}
    </x-form.static>

    {{-- Rename field. Slug is immutable because SyncAdapterConfig
         keys off it, but label is free to change so admins can rename
         after creation (e.g. "Fleet" -> "Production Fleet" once
         they add a second staging instance). --}}
    <x-form.row
        :label="trans('admin/settings/sync_adapters.add_label_label')"
        name="label"
        input_div_class="col-md-8"
        :help_text="trans('admin/settings/sync_adapters.add_label_help')"
        required
    >
        <x-slot:input>
            <x-input.text
                name="label"
                :value="old('label', $adapter->label())"
                :disabled="$locked"
                required
            />
        </x-slot:input>
    </x-form.row>

    {{-- Company scope. Set at Add-adapter time in the modal. This row
         surfaces it in the per-adapter settings so admins can see and
         change what company the instance is scoped to without having
         to delete and recreate. Blank means shared across every
         company (available to all). The id is slug-scoped because
         every adapter tab-pane renders this same form, and duplicate
         DOM ids silently break select2's init on all but the first
         one. Only rendered when at least one company exists, since
         on a single-tenant install the picker would only ever show
         "no selection" and confuses more than it helps. --}}
    @if ($hasCompanies)
        <x-input.company-select
            name="company_id"
            :id="$slug . '_company_id_select'"
            :label="trans('admin/settings/sync_adapters.company_scope_label')"
            :selected="old('company_id', $adapter->companyId())"
            :help_text="trans('admin/settings/sync_adapters.company_scope_help')"
        />
    @endif

    <x-form.checkbox-row
        :name="$activeField"
        :checked="$adapter->isActive()"
        :label="trans('admin/settings/sync_adapters.active_label')"
        :disabled="$locked"
    />

    <x-form.checkbox-row
        :name="$slug . '_log_heartbeats'"
        :checked="$adapter->logsHeartbeats()"
        :label="trans('admin/settings/sync_adapters.log_heartbeats_label')"
        :help_text="trans('admin/settings/sync_adapters.log_heartbeats_help')"
        :disabled="$locked"
    />

    @if ($adapter->usesConfigurableUrl())
        @php
            // Optional: adapters can pull the Base URL row into a
            // settingsSections() group so it renders inside the
            // relevant fieldset (typically "auth"). The credentials
            // partial's schema loop tracks currentSection starting
            // at this value and won't re-open the fieldset for its
            // first same-sectioned schema entries.
            $urlSection = $adapter->baseUrlSection();
            $urlSectionData = $urlSection
                ? ($adapter->settingsSections()[$urlSection] ?? null)
                : null;
        @endphp
        @if ($urlSectionData)
            <fieldset>
                <x-form.legend icon="tip" help_text="{!! $urlSectionData['help'] ?? '' !!}">
                    {{ $urlSectionData['title'] }}
                </x-form.legend>
        @endif
            <x-form.row
                :label="trans('admin/settings/sync_adapters.base_url')"
                :name="$urlField"
                input_div_class="col-md-8"
                help_html="{!! $adapter->baseUrlHelp() ?? trans('admin/settings/sync_adapters.base_url_help', ['type' => $adapter::typeLabel()]) !!}"
                required
            >
                <x-slot:input>
                    <x-input.text
                        type="url"
                        :name="$urlField"
                        :value="old($urlField, $adapter->getUrl())"
                        :disabled="$locked"
                        :placeholder="$adapter->baseUrlPlaceholder()"
                        input_icon="link"
                        input_group_addon="right"
                        required
                    />
                </x-slot:input>
            </x-form.row>
        {{-- We deliberately do NOT close the fieldset here even when
             we opened one above. The credentials partial's schema
             loop starts with $currentSection = $urlSection, so its
             first section transition (to any section other than
             $urlSection) closes this fieldset. --}}
    @endif

    {{ $slot }}

    {{-- Everything from Model Category down through "Check assets
         in when the vendor reports no assigned user" is grouped in a
         single fieldset so it visually separates from whatever the
         previous section left open (on the Custom HTTP adapter the
         "Custom Extras" section sits directly above this block, and
         without the wrapper these controls read as if they were part
         of Custom Extras). Applies to every adapter for consistency. --}}
    <fieldset>
        <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.defaults_section_intro') }}">
            {{ trans('admin/settings/sync_adapters.defaults_section_title') }}
        </x-form.legend>

    {{-- Default category for AssetModels auto-created from this
         adapter. Vendors don't send Snipe-IT's category concept but
         every AssetModel needs one. --}}
    <x-input.category-select
        :name="$slug . '_default_category_id'"
        :label="trans('admin/settings/sync_adapters.default_category')"
        :selected="old($slug . '_default_category_id', $adapter->defaultCategoryId())"
        categoryType="asset"
        :help_text="trans('admin/settings/sync_adapters.default_category_help')"
    />

        {{-- Default fieldset for AssetModels auto-created --}}
        <x-form.row
            :label="trans('admin/settings/sync_adapters.default_fieldset')"
            :name="$slug . '_default_fieldset_id'"
            input_div_class="col-md-7"
            :help_text="trans('admin/settings/sync_adapters.default_fieldset_help')"
        >
            <x-slot:input>
                <x-input.select
                    :name="$slug . '_default_fieldset_id'"
                    :id="$slug . '_default_fieldset_id'"
                    :options="\App\Models\CustomFieldset::orderBy('name')->pluck('name', 'id')->all()"
                    :selected="old($slug . '_default_fieldset_id', $adapter->defaultFieldsetId())"
                    :includeEmpty="true"
                    style="width: 100%"
                    :disabled="$locked"
                />
            </x-slot:input>
        </x-form.row>

        {{-- Default status label for auto-created assets --}}
    <x-form.row
        :label="trans('admin/settings/sync_adapters.default_status')"
        :name="$slug . '_default_status_id'"
        input_div_class="col-md-7"
        :help_text="trans('admin/settings/sync_adapters.default_status_help')"
        required
    >
        <x-slot:input>
            <x-input.select
                :name="$slug . '_default_status_id'"
                :id="$slug . '_default_status_id'"
                :options="\App\Models\Statuslabel::orderBy('name')->pluck('name', 'id')->all()"
                :selected="old($slug . '_default_status_id', $adapter->defaultStatusId())"
                :includeEmpty="true"
                style="width: 100%"
                :disabled="$locked"
                required
            />
        </x-slot:input>
        <x-slot:after_input>
            @can('create', \App\Models\Statuslabel::class)
                <x-button.modal-add type="statuslabel" :target="$slug.'_default_status_id'"/>
            @endcan
        </x-slot:after_input>
    </x-form.row>

    {{-- Asset tag pattern. Optional. Grouped with the mapping-related
         options below rather than the required identity fields above
         so the required section stays contiguous. --}}
    <x-form.row
        :label="trans('admin/settings/sync_adapters.asset_tag_pattern')"
        :name="$slug . '_asset_tag_pattern'"
        input_div_class="col-md-8"
        help_html="{!! trans('admin/settings/sync_adapters.asset_tag_pattern_help') !!}"
    >
        <x-slot:input>
            <x-input.text
                :name="$slug . '_asset_tag_pattern'"
                :value="old($slug . '_asset_tag_pattern', $adapter->assetTagPattern())"
                :disabled="$locked"
                placeholder="{{ $adapter->name() }}-{serial}"
            />
        </x-slot:input>
    </x-form.row>

    {{-- User assignment via sync. Strategy 'none' disables the
         lookup entirely. When set, the vendor's user email or
         username on each record is matched against Snipe-IT users
         and the asset checks out to the matched user. --}}
    <x-form.row
        :label="trans('admin/settings/sync_adapters.user_match_strategy')"
        :name="$slug . '_user_match_strategy'"
        input_div_class="col-md-8"
        help_html="{!! trans('admin/settings/sync_adapters.user_match_strategy_help') !!}"
    >
        <x-slot:input>
            {{-- Select2 for visual consistency with the other pickers on
                 this page, but the option pool is a fixed 3-value enum
                 so the built-in search box is hidden via
                 data-minimum-results-for-search. --}}
            @php
                // On a validation-error redisplay, old() carries the submitted
                // value so the admin does not have to re-pick. Falls back to
                // the stored value on the first render.
                $userMatchSelected = old($slug.'_user_match_strategy', $adapter->userMatchStrategy());
            @endphp
            <select
                name="{{ $slug }}_user_match_strategy"
                class="select2 form-control"
                style="width: 100%"
                data-minimum-results-for-search="Infinity"
                @disabled($locked)
            >
                @foreach ([
                    'none' => trans('admin/settings/sync_adapters.user_match_none'),
                    'username_then_email' => trans('admin/settings/sync_adapters.user_match_username_then_email'),
                    'username' => trans('admin/settings/sync_adapters.user_match_username'),
                    'email' => trans('admin/settings/sync_adapters.user_match_email'),
                ] as $value => $label)
                    <option value="{{ $value }}" @selected($userMatchSelected === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-slot:input>
    </x-form.row>

    {{-- Suppress the checkout notification email on sync-driven
         assignments. Default is suppress so admins don't inbox-spam
         users every time a scheduled sync re-confirms the same
         assignment. --}}
    <x-form.checkbox-row
        :name="$slug . '_suppress_notifications'"
        :checked="$adapter->suppressesNotifications()"
        :label="trans('admin/settings/sync_adapters.suppress_notifications_label')"
        :disabled="$locked"
    />

    {{-- Opt-in: check the asset in when the vendor reports no
         assigned user. Default off because a single missed sync
         cycle (device offline, empty field on a fresh enrollment)
         would flap the assignment. --}}
    <x-form.checkbox-row
        :name="$slug . '_checkin_on_null_user'"
        :checked="$adapter->checksInOnNullUser()"
        :label="trans('admin/settings/sync_adapters.checkin_on_null_user_label')"
        :help_text="trans('admin/settings/sync_adapters.checkin_on_null_user_help')"
        :disabled="$locked"
    />

        <x-form.checkbox-row
            :name="$slug . '_adopt_by_serial'"
            :checked="$adapter->adoptsBySerial()"
            :label="trans('admin/settings/sync_adapters.adopt_by_serial_label')"
            :help_text="trans('admin/settings/sync_adapters.adopt_by_serial_help')"
            :disabled="$locked"
        />
        
        <x-form.checkbox-row
            :name="$slug . '_create_snipeit_assets_on_pull'"
            :checked="$adapter->createsSnipeitAssetsOnPull()"
            :label="trans('admin/settings/sync_adapters.create_snipeit_assets_on_pull_label')"
            :help_text="trans('admin/settings/sync_adapters.create_snipeit_assets_on_pull_help')"
            :disabled="$locked"
        />

        {{-- Adapter-specific "fetch per-device details" opt-in. Lets
             admins pay the extra per-device cost for fields the
             vendor's LIST endpoint doesn't populate (Intune's
             ethernet MAC + physical memory). --}}
        @php $perDeviceDetailsToggle = $adapter->perDeviceDetailsToggle(); @endphp
        @if ($perDeviceDetailsToggle !== null)
            <x-form.checkbox-row
                :name="$slug . '_fetch_per_device_details'"
                :checked="method_exists($adapter, 'fetchesPerDeviceDetails') && $adapter->fetchesPerDeviceDetails()"
                :label="$perDeviceDetailsToggle['label']"
                :help_text="$perDeviceDetailsToggle['help']"
                :disabled="$locked"
            />
        @endif
    </fieldset>

                {{-- Dry-run checkboxes render inside the panel partial instead of
                     here, so they sit just above the Pull Now / Push Now buttons.
                     They still belong to this form's save POST via the HTML `form`
                     attribute on their inputs (see panel.blade.php). --}}

    @if ($adapter->supportsGroupScoping() && $adapter->isEnabled() && $hasCompanies)
        @php
            $cachedGroups = $adapter->cachedGroups();
            $groupMappings = $adapter->groupMappings();
        @endphp
        <fieldset>
            <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.group_mapping_intro', ['label' => $adapter->vendorGroupLabel()]) }}">
                {{ trans('admin/settings/sync_adapters.group_mapping_title', ['label' => $adapter->vendorGroupLabel()]) }}
            </x-form.legend>

            @if (empty($cachedGroups))
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-3">
                        <p class="help-block">
                            {{ trans('admin/settings/sync_adapters.group_mapping_empty', ['label' => strtolower($adapter->vendorGroupLabel())]) }}
                        </p>
                    </div>
                </div>
            @else
                @foreach ($cachedGroups as $group)
                    <x-input.company-select
                        :name="$slug . '_group_mapping[' . $group['id'] . ']'"
                        :label="$group['label']"
                        :selected="$groupMappings[$group['id']] ?? null"
                        hideNewButton
                    />
                @endforeach
            @endif
        </fieldset>
    @endif

    {{-- Per-field target mapping.  --}}
    @php
        // Show push controls only when the adapter implements
        // PushableSyncAdapter AND canPush() (instance-level runtime check
        // (Fleet Free returns false because manual labels are Premium).
        $supportsPush = $adapter instanceof \App\SyncAdapters\PushableSyncAdapter && $adapter->canPush();
        $directionOptions = [
            'pull' => trans('admin/settings/sync_adapters.direction_pull'),
            'push' => trans('admin/settings/sync_adapters.direction_push'),
            'both' => trans('admin/settings/sync_adapters.direction_both'),
            'skip' => trans('admin/settings/sync_adapters.direction_skip'),
        ];
        // Per-field pushability lookup. For adapters like Custom HTTP
        // whose push path only resolves values for a subset of
        // standard fields (hostname/serial/asset_tag/model), hide the
        // push + both options for the rest. Otherwise the admin picks
        // push on a field we silently drop at build time.
        $pushableFieldsList = $supportsPush ? $adapter->pushableFields() : [];
        $directionOptionsFor = function (string $field) use ($directionOptions, $pushableFieldsList, $supportsPush) {
            if (! $supportsPush) {
                return $directionOptions;
            }
            if (in_array($field, $pushableFieldsList, true)) {
                return $directionOptions;
            }

            // Non-pushable field. Strip push + both so the dropdown
            // only exposes the directions that actually work for it.
            return array_diff_key($directionOptions, array_flip(['push', 'both']));
        };
    @endphp
    <fieldset>
        <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.mapping_section_intro') }}">
            {{ trans('admin/settings/sync_adapters.mapping_section_title', ['type' => $adapter::typeLabel()]) }}
        </x-form.legend>


        @foreach (\App\SyncAdapters\MappingTargets::FIELDS as $mappingField)
            <x-form.row
                :label="trans('admin/settings/sync_adapters.field_' . $mappingField)"
                :name="$slug . '_mapping_' . $mappingField"
                input_div_class="col-md-8"
            >
                <x-slot:input>
                    @if ($supportsPush)
                        {{-- Push-capable adapters get a two-column input:
                             target picker on the left, direction picker
                             on the right. Pull-only adapters render just
                             the target picker at full width. The
                             js-mapping-row / js-mapping-target /
                             js-mapping-direction hooks let the JS below
                             hide the direction column when the admin
                             picks target=Skip (direction is moot in
                             that state). --}}
                        <div class="row js-mapping-row">
                            <div class="col-md-8">
                                <x-input.select
                                    :name="$slug . '_mapping[' . $mappingField . ']'"
                                    :options="\App\SyncAdapters\MappingTargets::options($mappingField)"
                                    :selected="$adapter->mappingFor($mappingField)"
                                    class="js-mapping-target"
                                    style="width: 100%"
                                    :disabled="$locked"
                                />
                            </div>
                            <div class="col-md-4 js-mapping-direction">
                                @php
                                    // Clamp stored direction to the options
                                    // we're actually offering: a legacy
                                    // direction=push on a now-non-pushable
                                    // field falls back to pull in the UI
                                    // so the dropdown doesn't render with
                                    // a value that isn't in its options.
                                    $fieldDirOptions = $directionOptionsFor($mappingField);
                                    $storedDir = $adapter->directionFor($mappingField);
                                    $renderDir = array_key_exists($storedDir, $fieldDirOptions) ? $storedDir : 'pull';
                                @endphp
                                <x-input.select
                                    :name="$slug . '_direction[' . $mappingField . ']'"
                                    :options="$fieldDirOptions"
                                    :selected="$renderDir"
                                    style="width: 100%"
                                    data-minimum-results-for-search="Infinity"
                                    :disabled="$locked"
                                />
                            </div>
                        </div>
                    @else
                        <x-input.select
                            :name="$slug . '_mapping[' . $mappingField . ']'"
                            :options="\App\SyncAdapters\MappingTargets::options($mappingField)"
                            :selected="$adapter->mappingFor($mappingField)"
                            style="width: 100%"
                            :disabled="$locked"
                        />
                    @endif
                </x-slot:input>
            </x-form.row>
        @endforeach

    </fieldset>

    @php
        $extraFields = $adapter->extraFields();
        // Precompute entries for the mapping-picker widget (extras
        // section on adapters that declare adapter-owned extras).
        // Entry shapes supported:
        //   'literal label string'
        //   ['label' => 'literal string', 'type' => 'text|boolean', 'admin_defined' => bool]
        //   ['label_key' => 'admin/settings/sync_adapters.extra_foo', 'type' => ..., 'admin_defined' => ...]
        // label_key entries auto-resolve through trans() with
        // ':vendor' bound to the adapter's typeLabel so per-vendor
        // labels share one translation string.
        $extraEntries = [];
        foreach ($extraFields as $extraKey => $extraEntry) {
            if (is_array($extraEntry) && isset($extraEntry['label_key'])) {
                $extraLabel = trans($extraEntry['label_key'], ['vendor' => $adapter::typeLabel()]);
            } elseif (is_array($extraEntry)) {
                $extraLabel = $extraEntry['label'] ?? $extraKey;
            } else {
                $extraLabel = $extraEntry;
            }
            $extraType = (is_array($extraEntry) && isset($extraEntry['type'])) ? $extraEntry['type'] : 'text';
            $extraAdminDefined = is_array($extraEntry) && ! empty($extraEntry['admin_defined']);
            $extraEntries[$extraKey] = [
                'label' => $extraLabel,
                'target_options' => \App\SyncAdapters\MappingTargets::optionsForExtra($extraType, $extraAdminDefined),
                'stored_target' => $adapter->mappingFor($extraKey),
                'stored_direction' => $adapter->directionFor($extraKey),
            ];
        }
    @endphp

    @foreach ($adapter->settingsSchema() as $fmField)
        @if (($fmField['type'] ?? '') === 'field_map')
            @php
                $fmName = $slug.'_'.$fmField['key'];
                $fmStored = $adapter->credentialForDisplay($fmField['key']);
                $fmMap = is_string($fmStored) && $fmStored !== ''
                    ? (json_decode($fmStored, true) ?: [])
                    : [];
                $fmMap = old($fmName, is_array($fmMap) ? $fmMap : []);
                $fmMap = is_array($fmMap) ? $fmMap : [];
                // Load stored per-row direction values so the widget's
                // dropdowns pre-select the correct value. Framework's
                // direction.<key> storage is the single source of
                // truth, shared with the standard mapping loop above.
                $fmDirections = [];
                foreach (array_keys($fmMap) as $fmKey) {
                    $fmDirections[$fmKey] = $adapter->directionFor($fmKey);
                }
            @endphp
            <fieldset>
                <x-form.legend icon="tip" help_text="{!! $fmField['help'] ?? '' !!}">
                    {{ $fmField['label'] }}
                </x-form.legend>
                <x-input.field-map
                    :name="$fmName"
                    :options="$fmField['options'] ?? []"
                    :stored="$fmMap"
                    :direction_name="$supportsPush ? $slug . '_direction' : null"
                    :direction_options="$supportsPush ? $directionOptions : []"
                    :stored_directions="$fmDirections"
                />
            </fieldset>
        @endif
    @endforeach

                @if (! empty($extraFields) && $adapter->rendersExtrasPicker())
        <fieldset>
            <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.extra_fields_section_intro') }}">
                {{ trans('admin/settings/sync_adapters.extra_fields_section_title', ['type' => $adapter::typeLabel()]) }}
            </x-form.legend>

            <x-input.mapping-picker
                :name="$slug . '_mapping'"
                :direction_name="$supportsPush ? $slug . '_direction' : null"
                :entries="$extraEntries"
                :direction_options="$directionOptions"
                :supports_push="$supportsPush"
                :locked="$locked"
            />
        </fieldset>
    @endif

    {{-- Admin-defined custom extras. Each row pairs a Snipe-IT
         destination (custom:X / native:X) with a vendor-record
         dot-path the adapter extracts at pull time. Opt-in per
         adapter via supportsAdminDefinedExtras(). Rows show up as
         targets in the mapping picker above once saved. --}}
    @if ($adapter->supportsAdminDefinedExtras())
        @php
            $cxName = $slug.'_field_paths';
            $cxStored = $adapter->adminDefinedExtras();
            $cxStored = old($cxName, is_array($cxStored) ? $cxStored : []);
            $cxStored = is_array($cxStored) ? $cxStored : [];
        @endphp
        <fieldset>
            <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.custom_extras_section_intro') }}">
                {{ trans('admin/settings/sync_adapters.custom_extras_section_title') }}
            </x-form.legend>
            <x-input.field-map
                :name="$cxName"
                :options="$adapter->customExtrasDestinationOptions()"
                :stored="$cxStored"
            />
        </fieldset>
    @endif

    {{-- Composed notes fieldset. Renders a multi-field notes blob
         from Snipe-IT (asset_tag + status + assigned user + custom
         fields, etc.) into a single vendor field. Blank template
         means "don't push notes." Target defaults to the adapter's
         suggested field (Kandji notes, Jamf general.notes, Intune
         notes, etc.) via notesFieldTarget(). Admins override to a
         Custom Attribute / Custom Field name when the vendor exposes
         multiple candidates. Positioned after the standard + extras
         mapping so admins finish configuring the per-field targets
         before deciding what goes into the composed notes blob. --}}
                @if ($adapter instanceof \App\SyncAdapters\PushableSyncAdapter && $adapter->canPush() && $adapter->supportsComposedNotesPush())
        <fieldset>
            <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.push_notes_section_intro') }}">
                {{ trans('admin/settings/sync_adapters.push_notes_section_title') }}
            </x-form.legend>

            <x-form.row
                :label="trans('admin/settings/sync_adapters.push_notes_target_label')"
                :name="$slug . '_push_notes_target'"
                input_div_class="col-md-8"
                :help_text="trans('admin/settings/sync_adapters.push_notes_target_help')"
            >
                <x-slot:input>
                    @php
                        $storedNotesConfig = $adapter->storedPushNotesConfig();
                    @endphp
                    <x-input.text
                        :name="$slug . '_push_notes_target'"
                        :value="old($slug . '_push_notes_target', $storedNotesConfig['target_override'] ?? '')"
                        :placeholder="$adapter->notesFieldTarget() ?? trans('admin/settings/sync_adapters.push_notes_target_placeholder_none')"
                        :disabled="$locked"
                    />
                </x-slot:input>
            </x-form.row>

            <x-form.row
                :label="trans('admin/settings/sync_adapters.push_notes_template_label')"
                :name="$slug . '_push_notes_template'"
                input_div_class="col-md-8"
                help_html="{!! trans('admin/settings/sync_adapters.push_notes_template_help') !!}"
            >
                <x-slot:input>
                    <textarea
                        name="{{ $slug }}_push_notes_template"
                        class="form-control"
                        rows="6"
                        @disabled($locked)
                        style="font-family: monospace; white-space: pre;"
                    >{{ old($slug . '_push_notes_template', $storedNotesConfig['template']) }}</textarea>
                </x-slot:input>
            </x-form.row>
        </fieldset>
    @endif

                {{-- Diagnostics: Preview push payload + Test 1 Push + Test 1 Pull.
                     AJAX buttons that pick the first linked (push) or first
                     vendor-returned (pull) record, run the capture, and dump the
                     output into the <pre> below. Scoped to this adapter via the
                     data-adapter-slug attribute on the wrapper. --}}
                <fieldset class="js-sync-diagnostics" data-adapter-slug="{{ $slug }}">
                    <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.diagnostics_section_intro') }}">
                        {{ trans('admin/settings/sync_adapters.diagnostics_section_title') }}
                    </x-form.legend>

                    <div class="form-group">
                        <div class="col-md-12">
                            @if ($adapter instanceof \App\SyncAdapters\PushableSyncAdapter && $adapter->canPush())
                                <button
                                    type="button"
                                    class="btn btn-theme js-sync-diagnostic-button"
                                    data-url="{{ route('settings.adapters.preview_push_payload', $slug) }}"
                                    data-dirty-guarded-by="adapter-form-{{ $slug }}"
                                    title="{{ trans('admin/settings/sync_adapters.preview_payload_help') }}"
                                    @disabled($locked)
                                >
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    {{ trans('admin/settings/sync_adapters.preview_payload_button') }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-theme js-sync-diagnostic-button"
                                    data-url="{{ route('settings.adapters.test_1_push', $slug) }}"
                                    data-dirty-guarded-by="adapter-form-{{ $slug }}"
                                    title="{{ trans('admin/settings/sync_adapters.test_1_push_help') }}"
                                    @disabled($locked)
                                >
                                    <i class="fa-solid fa-vial" aria-hidden="true"></i>
                                    {{ trans('admin/settings/sync_adapters.test_1_push_button') }}
                                </button>
                            @endif
                            <button
                                type="button"
                                class="btn btn-theme js-sync-diagnostic-button"
                                data-url="{{ route('settings.adapters.test_1_pull', $slug) }}"
                                data-dirty-guarded-by="adapter-form-{{ $slug }}"
                                title="{{ trans('admin/settings/sync_adapters.test_1_pull_help') }}"
                                @disabled($locked)
                            >
                                <i class="fa-solid fa-vial" aria-hidden="true"></i>
                                {{ trans('admin/settings/sync_adapters.test_1_pull_button') }}
                            </button>
                        </div>

                        <div class="col-md-12">
                            {{-- Copy-to-clipboard --}}
                            <div style="float: right; margin-bottom: 4px;">
                                <x-copy-to-clipboard copy_what="sync-diagnostic-{{ $slug }}"/>
                            </div>
                            <pre
                                class="js-sync-diagnostic-output js-copy-sync-diagnostic-{{ $slug }}"
                                style="min-height: 60px; max-height: 400px; overflow: auto; margin: 0; clear: both;"
                            >{{ trans('admin/settings/sync_adapters.diagnostics_output_placeholder') }}</pre>
                        </div>
                    </div>
                </fieldset>
</x-form>
