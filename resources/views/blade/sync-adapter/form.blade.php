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
                :help_text="trans('admin/settings/sync_adapters.base_url_help', ['type' => $adapter::typeLabel()])"
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
        required
    />

    {{-- Default status label for auto-created assets. Required so
         admins have to consciously pick a workflow bucket for
         discovered devices instead of Snipe-IT defaulting to
         something surprising. --}}
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
                <a
                    href="{{ route('modal.show', ['type' => 'statuslabel']) }}"
                    data-toggle="modal"
                    data-target="#createModal"
                    data-select="{{ $slug . '_default_status_id' }}"
                    class="btn btn-sm btn-theme"
                >{{ trans('button.new') }}</a>
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
    </fieldset>

    {{-- Any type: field_map entries from the adapter's credential
         schema render here in their own fieldset. Keeps them
         grouped alongside the standard mapping controls below and
         out of the credential-input section above, since they
         define WHERE fields live in the vendor payload rather than
         auth credentials. --}}
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
            @endphp
            <fieldset>
                <x-form.legend icon="tip" help_text="{!! $fmField['help'] ?? '' !!}">
                    {{ $fmField['label'] }}
                </x-form.legend>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-3">
                        <x-input.field-map
                            :name="$fmName"
                            :options="$fmField['options'] ?? []"
                            :stored="$fmMap"
                        />
                    </div>
                </div>
            </fieldset>
        @endif
    @endforeach

    {{-- Push dry-run only shows for adapters that actually support
         push. Turning it on makes push() log the payload instead of
         sending, so admins can verify their config end-to-end against
         a real Snipe-IT dataset without any real vendor side effect.
         The composed-notes template + target field live further down
         in their own fieldset under the vendor-specific mapping. --}}
    @if ($adapter instanceof \App\SyncAdapters\PushableAdapter && $adapter->canPush())
        <x-form.checkbox-row
            :name="$slug . '_push_dry_run'"
            :checked="$adapter->isPushDryRun()"
            :label="trans('admin/settings/sync_adapters.push_dry_run_label')"
            :help_text="trans('admin/settings/sync_adapters.push_dry_run_help')"
            :disabled="$locked"
        />
    @endif

    @if ($adapter->supportsGroupScoping() && $adapter->isEnabled())
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
        // PushableAdapter AND canPush() (instance-level runtime check
        // (Fleet Free returns false because manual labels are Premium).
        $supportsPush = $adapter instanceof \App\SyncAdapters\PushableAdapter && $adapter->canPush();
        $directionOptions = [
            'pull' => trans('admin/settings/sync_adapters.direction_pull'),
            'push' => trans('admin/settings/sync_adapters.direction_push'),
            'both' => trans('admin/settings/sync_adapters.direction_both'),
            'skip' => trans('admin/settings/sync_adapters.direction_skip'),
        ];
    @endphp
    <fieldset>
        <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.mapping_section_intro') }}">
            {{ trans('admin/settings/sync_adapters.mapping_section_title', ['type' => $adapter::typeLabel()]) }}
        </x-form.legend>


        @foreach (\App\SyncAdapters\Support\MappingTargets::FIELDS as $mappingField)
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
                             the target picker at full width. --}}
                        <div class="row">
                            <div class="col-md-8">
                                <x-input.select
                                    :name="$slug . '_mapping[' . $mappingField . ']'"
                                    :options="\App\SyncAdapters\Support\MappingTargets::options($mappingField)"
                                    :selected="$adapter->mappingFor($mappingField)"
                                    style="width: 100%"
                                    :disabled="$locked"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input.select
                                    :name="$slug . '_direction[' . $mappingField . ']'"
                                    :options="$directionOptions"
                                    :selected="$adapter->directionFor($mappingField)"
                                    style="width: 100%"
                                    data-minimum-results-for-search="Infinity"
                                    :disabled="$locked"
                                />
                            </div>
                        </div>
                    @else
                        <x-input.select
                            :name="$slug . '_mapping[' . $mappingField . ']'"
                            :options="\App\SyncAdapters\Support\MappingTargets::options($mappingField)"
                            :selected="$adapter->mappingFor($mappingField)"
                            style="width: 100%"
                            :disabled="$locked"
                        />
                    @endif
                </x-slot:input>
            </x-form.row>
        @endforeach

        @php
            $extraFields = $adapter->extraFields();
        @endphp

        @if (! empty($extraFields))
            <x-form.legend icon="tip" help_text="{{ trans('admin/settings/sync_adapters.extra_fields_section_intro') }}">
                {{ trans('admin/settings/sync_adapters.extra_fields_section_title', ['type' => $adapter::typeLabel()]) }}
            </x-form.legend>

            @foreach ($extraFields as $extraKey => $extraEntry)
                @php
                    // Entry shapes accepted:
                    //   'literal label string'
                    //   ['label' => 'literal string', 'type' => 'text|boolean', 'admin_defined' => true|false]
                    //   ['label_key' => 'admin/settings/sync_adapters.extra_foo', 'type' => ..., 'admin_defined' => ...]
                    // label_key entries auto-resolve through trans()
                    // with ':vendor' bound to the adapter's typeLabel
                    // so per-vendor labels ("Fleet Team", "Jamf UDID")
                    // share one translation string.
                    if (is_array($extraEntry) && isset($extraEntry['label_key'])) {
                        $extraLabel = trans($extraEntry['label_key'], ['vendor' => $adapter::typeLabel()]);
                    } elseif (is_array($extraEntry)) {
                        $extraLabel = $extraEntry['label'] ?? $extraKey;
                    } else {
                        $extraLabel = $extraEntry;
                    }
                    $extraType = (is_array($extraEntry) && isset($extraEntry['type'])) ? $extraEntry['type'] : 'text';
                    $extraAdminDefined = is_array($extraEntry) && ! empty($extraEntry['admin_defined']);
                @endphp
                <x-form.row
                    :label="$extraLabel"
                    :name="$slug . '_mapping_' . $extraKey"
                    input_div_class="col-md-8"
                >
                    <x-slot:input>
                        @if ($supportsPush)
                            {{-- Same two-column shape as the standard
                                 fields above so admins can pick a
                                 target column AND a direction per
                                 extra. Framework already respects
                                 directionFor() on extras via
                                 pushDirectedFields(), but the UI was
                                 only exposing the target dropdown. --}}
                            <div class="row">
                                <div class="col-md-8">
                                    <x-input.select
                                        :name="$slug . '_mapping[' . $extraKey . ']'"
                                        :options="\App\SyncAdapters\Support\MappingTargets::optionsForExtra($extraType, $extraAdminDefined)"
                                        :selected="$adapter->mappingFor($extraKey)"
                                        style="width: 100%"
                                        :disabled="$locked"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <x-input.select
                                        :name="$slug . '_direction[' . $extraKey . ']'"
                                        :options="$directionOptions"
                                        :selected="$adapter->directionFor($extraKey)"
                                        style="width: 100%"
                                        data-minimum-results-for-search="Infinity"
                                        :disabled="$locked"
                                    />
                                </div>
                            </div>
                        @else
                            <x-input.select
                                :name="$slug . '_mapping[' . $extraKey . ']'"
                                :options="\App\SyncAdapters\Support\MappingTargets::optionsForExtra($extraType, $extraAdminDefined)"
                                :selected="$adapter->mappingFor($extraKey)"
                                style="width: 100%"
                                :disabled="$locked"
                            />
                        @endif
                    </x-slot:input>
                </x-form.row>
            @endforeach
        @endif
    </fieldset>

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
    @if ($adapter instanceof \App\SyncAdapters\PushableAdapter && $adapter->canPush())
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
                    <x-input.text
                        :name="$slug . '_push_notes_target'"
                        :value="old($slug . '_push_notes_target', $adapter->pushNotesTargetOverride() ?? '')"
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
                    >{{ old($slug . '_push_notes_template', $adapter->pushNotesTemplate()) }}</textarea>
                </x-slot:input>
            </x-form.row>
        </fieldset>
    @endif
</x-form>
