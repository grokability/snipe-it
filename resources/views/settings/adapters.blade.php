@extends('layouts/default')

@section('title')
    {{ trans('admin/settings/sync_adapters.title') }}
    @parent
@stop

@section('content')

    <x-container class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">

        {{-- Company filter. Reloads the page with ?company=blah so only
             adapters scoped to the picked company show in the tab list.
             'shared' filters to adapters with no company_id (available
             to all). Placed above the box and right-aligned so it lines
             up with the box's right edge and reads as a view filter
             rather than a per-adapter setting, without stealing space
             from either the box's title or the tab strip beneath it.
             Only rendered when at least one company exists, since on
             single-tenant installs the filter would only ever show
             "All" and "Shared" and is pure noise. --}}
        @if ($hasCompanies)
            <div class="clearfix sync-adapters-company-filter" style="margin-bottom: 8px;">
                <form
                    method="GET"
                    action="{{ route('settings.adapters.index') }}"
                    class="form-inline pull-right"
                >
                    <label for="adapter-company-filter" style="margin-right: 8px; font-weight: normal;">
                        {{ trans('admin/settings/sync_adapters.company_filter_prefix') }}
                    </label>
                    <select
                        id="adapter-company-filter"
                        name="company"
                        class="select2"
                        style="width: 220px;"
                        onchange="this.form.submit()"
                    >
                        <option value="">{{ trans('admin/settings/sync_adapters.company_filter_all') }}</option>
                        <option value="shared" @selected($selectedCompany === 'shared')>{{ trans('admin/settings/sync_adapters.company_filter_shared') }}</option>
                        @foreach ($companies as $companyId => $companyName)
                            <option value="{{ $companyId }}" @selected((string) $selectedCompany === (string) $companyId)>{{ $companyName }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        <x-box>
            <x-slot:header>
                <i class="fa-solid fa-network-wired" aria-hidden="true"></i>
                {{ trans('admin/settings/sync_adapters.title') }} <span class="label label-warning">beta</span>
            </x-slot:header>

            <div class="side-tabs">
                <div class="side-tabs-list">
                    <ul class="nav nav-pills nav-stacked" role="tablist">
                        @foreach ($adapters as $adapter)
                            @php
                                $adapterSyncedCount = $syncedCounts[$adapter->name()] ?? 0;
                                $adapterDeleteConfirm = trans_choice(
                                    'admin/settings/sync_adapters.delete_confirm_count',
                                    $adapterSyncedCount,
                                    ['count' => $adapterSyncedCount],
                                );
                                $adapterCloneLabelDefault = trans('admin/settings/sync_adapters.clone_label_default', ['label' => $adapter->label()]);
                            @endphp
                            <li role="presentation" @class(['active' => $selected?->name() === $adapter->name()])>
                                <a
                                    href="#adapter-pane-{{ $adapter->name() }}"
                                    role="tab"
                                    data-toggle="tab"
                                    aria-controls="adapter-pane-{{ $adapter->name() }}"
                                    aria-selected="{{ $selected?->name() === $adapter->name() ? 'true' : 'false' }}"
                                    data-adapter-name="{{ $adapter->name() }}"
                                    data-adapter-destroy-url="{{ route('settings.adapters.destroy', $adapter->name()) }}"
                                    data-adapter-delete-confirm="{{ $adapterDeleteConfirm }}"
                                    data-adapter-clone-url="{{ route('settings.adapters.clone', $adapter->name()) }}"
                                    data-adapter-clone-label-default="{{ $adapterCloneLabelDefault }}"
                                    data-breadcrumb-label="{{ $adapter->label() }}"
                                >
                                    @php
                                        $readiness = $adapter->readinessStatus();
                                        $readinessColor = match ($readiness) {
                                            'active' => 'var(--text-success)',
                                            'partial' => 'var(--text-warning)',
                                            'inactive' => 'var(--text-danger)',
                                        };
                                    @endphp
                                    <x-icon
                                        type="circle-solid"
                                        class="fa-fw"
                                        style="color: {{ $readinessColor }};"
                                        :title="trans('admin/settings/sync_adapters.readiness_'.$readiness)"
                                        aria-hidden="true"
                                    />
                                    <span class="sr-only">{{ trans('admin/settings/sync_adapters.readiness_'.$readiness) }}</span>
                                    {{ $adapter->label() }}
                                </a>
                            </li>
                        @endforeach

                        {{-- Help tab. Real tab-pane below. Always available so
                             admins can revisit the getting-started copy and
                             the list of shipped adapter types after their
                             first setup. Default-active when no adapters
                             are configured. Positioned above Add adapter so
                             empty installs don't render a lone "Add adapter"
                             tab active-looking above a lonely Help entry. --}}
                        <li role="presentation" @class(['active' => $selected === null])>
                            <a
                                href="#adapter-pane-help"
                                role="tab"
                                data-toggle="tab"
                                aria-controls="adapter-pane-help"
                                aria-selected="{{ $selected === null ? 'true' : 'false' }}"
                            >
                                <x-icon type="tip" class="fa-fw text-info"/>
                                {{ trans('admin/settings/sync_adapters.help_tab_label') }}
                            </a>
                        </li>

                        {{-- Add-adapter tab. Not a real tab-pane. Clicking it opens the modal instead. --}}
                        <li role="presentation">
                            <a
                                href="#"
                                data-toggle="modal"
                                data-target="#add-adapter-modal"
                            >
                                <x-icon type="create" class="fa-fw"/>
                                {{ trans('admin/settings/sync_adapters.add_button') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="side-tabs-content">
                    <div class="tab-content">
                        @foreach ($adapters as $adapter)
                            <div
                                role="tabpanel"
                                id="adapter-pane-{{ $adapter->name() }}"
                                @class(['tab-pane fade', 'active in' => $selected?->name() === $adapter->name()])
                            >
                                <x-sync-adapter.credentials :adapter="$adapter" />
                            </div>
                        @endforeach

                            <div
                                role="tabpanel"
                                id="adapter-pane-help"
                                @class(['tab-pane fade', 'active in' => $selected === null])
                            >
                                <div class="sync-adapters-empty-state">

                                    <x-alert
                                        type="warning"
                                        icon="dungeon"
                                        :title="trans('admin/settings/sync_adapters.experimental_banner_title')"
                                        style="margin-bottom: 20px;"
                                    >
                                        {{ trans('admin/settings/sync_adapters.experimental_banner_body') }}
                                    </x-alert>

                                    <h3>
                                        <x-icon type="tip" class="text-info"/> {{ trans('admin/settings/sync_adapters.empty_state_title') }}
                                    </h3>
                                    <p>{{ trans('admin/settings/sync_adapters.empty_state_intro') }}</p>
                                    <p>{{ trans('admin/settings/sync_adapters.empty_state_supported_intro', ['count' => count($adapterCatalog)]) }}</p>
                                    <ul class="list-unstyled adapter-catalog" aria-label="{{ trans('admin/settings/sync_adapters.catalog_caption') }}">
                                        @foreach ($adapterCatalog as $slug => $entry)
                                            <li class="adapter-catalog-item">
                                                @php
                                                    $directionLabel = trans('admin/settings/sync_adapters.catalog_direction_'.$entry['direction']);
                                                    $directionIcon = $entry['direction'] === 'both' ? 'fa-solid fa-arrow-right-arrow-left fa-rotate-90' : 'fa-solid fa-arrow-down-long';
                                                @endphp
                                                <span class="adapter-catalog-direction tooltip-base" data-placement="top" title="{{ trans('admin/settings/sync_adapters.catalog_direction_tooltip', ['direction' => $directionLabel]) }}">
                                                    <i class="{{ $directionIcon }} fa-fw" aria-hidden="true"></i>
                                                    <span class="sr-only">{{ $directionLabel }}</span>
                                                </span>
                                                <span class="adapter-catalog-docs">
                                                    @if ($entry['docs_url'])
                                                        @php
                                                            $docsLinkLabel = trans('admin/settings/sync_adapters.catalog_docs_link_label', ['adapter' => $entry['label']]);
                                                        @endphp
                                                        <a
                                                            href="{{ $entry['docs_url'] }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="tooltip-base"
                                                            data-placement="right"
                                                            title="{{ $docsLinkLabel }}"
                                                            aria-label="{{ $docsLinkLabel }}"
                                                        >
                                                            <x-icon type="external-link" class="fa-fw"/>
                                                        </a>
                                                    @else
                                                        <span class="sr-only">{{ trans('admin/settings/sync_adapters.catalog_docs_none') }}</span>
                                                    @endif
                                                </span>
                                                <span class="adapter-catalog-name">{{ $entry['label'] }}</span>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success tooltip-base adapter-catalog-add"
                                                    data-toggle="modal"
                                                    data-target="#add-adapter-modal"
                                                    data-adapter-type="{{ $slug }}"
                                                    data-placement="left"
                                                    title="{{ trans('admin/settings/sync_adapters.catalog_add_tooltip') }}"
                                                    aria-label="{{ trans('admin/settings/sync_adapters.catalog_add_aria', ['label' => $entry['label']]) }}"
                                                >
                                                    <x-icon type="create" class="fa-fw"/>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>


                                    <h4>{{ trans('admin/settings/sync_adapters.empty_state_company_title') }}</h4>
                                    <p>{{ trans('admin/settings/sync_adapters.empty_state_company_intro') }}</p>
                                    <p>{{ trans('admin/settings/sync_adapters.empty_state_company_clone_note') }}</p>
                                </div>
                            </div>
                    </div>
                </div>
            </div>

            <x-slot:customfooter>
                {{-- The whole footer collapses when Help (or any non-adapter
                     tab) is active, since Save / Delete / Clone are all
                     adapter-scoped actions and leaving the padded strip
                     visible reads as a blank UI dead-zone. Tab-switch JS
                     below flips the display back on when an adapter tab
                     becomes active. --}}
                <div id="adapter-footer" class="box-footer" style="{{ $selected ? '' : 'display: none;' }}">
                    <div class="row">
                        <div class="text-left col-md-6">
                            @php
                                $selectedSyncedCount = $selected ? ($syncedCounts[$selected->name()] ?? 0) : 0;
                                $selectedDeleteConfirm = trans_choice(
                                    'admin/settings/sync_adapters.delete_confirm_count',
                                    $selectedSyncedCount,
                                    ['count' => $selectedSyncedCount],
                                );
                            @endphp
                            <button
                                id="adapter-delete-trigger"
                                type="button"
                                class="btn btn-danger delete-asset"
                                data-toggle="modal"
                                data-target="#dataConfirmModal"
                                data-href="{{ $selected ? route('settings.adapters.destroy', $selected->name()) : '' }}"
                                data-title="{{ trans('admin/settings/sync_adapters.delete_button') }}"
                                data-content="{{ $selectedDeleteConfirm }}"
                                data-icon="fa fa-trash"
                                onclick="return false;"
                                @disabled(config('app.lock_passwords') === true)
                            >
                                <x-icon type="delete"/>
                                {{ trans('admin/settings/sync_adapters.delete_button') }}
                            </button>

                            <button
                                id="adapter-clone-trigger"
                                type="button"
                                class="btn btn-info"
                                data-toggle="modal"
                                data-target="#clone-adapter-modal"
                                @disabled(config('app.lock_passwords') === true)
                            >
                                <x-icon type="clone"/>
                                {{ trans('admin/settings/sync_adapters.clone_button') }}
                            </button>
                        </div>
                        <div class="text-right col-md-6">
                            <x-button.submit
                                id="adapter-save-button"
                                class="btn-success"
                                form="adapter-form-{{ $selected?->name() }}"
                                :disabled="config('app.lock_passwords') === true"
                            />
                        </div>
                    </div>
                </div>
            </x-slot:customfooter>
        </x-box>

    </x-container>

    {{-- Add-adapter modal. Users add additional instances of the shipped
         adapter types (a second Fleet install, another Kandji tenant,
         etc.) or Generic REST once/if that adapter lands. --}}
    <x-modals
        id="add-adapter-modal"
        :title="trans('admin/settings/sync_adapters.add_modal_title')"
        :action="route('settings.adapters.create')"
    >
        <x-form.row
            :label="trans('admin/settings/sync_adapters.add_type_label')"
            name="adapter_type"
            input_div_class="col-md-8"
        >
            <x-slot:input>
                <x-input.select name="adapter_type" :options="$adapterTypes" style="width: 100%" required/>
            </x-slot:input>
        </x-form.row>

        <x-form.row
            :label="trans('admin/settings/sync_adapters.add_label_label')"
            name="label"
            input_div_class="col-md-8"
            :help_text="trans('admin/settings/sync_adapters.add_label_help')"
        >
            <x-slot:input>
                <x-input.text name="label" required/>
            </x-slot:input>
        </x-form.row>

        {{-- Pre-select the currently-filtered company so admins clicking
             "Add adapter" while viewing a specific company get that
             company already selected. The 'shared' filter sentinel and
             absent values leave the modal's company field empty. --}}
        <x-input.company-select
            name="company_id"
            id="modal_adapter_company_id_select"
            :label="trans('admin/settings/sync_adapters.add_company_label')"
            :selected="is_numeric($selectedCompany) ? (int) $selectedCompany : null"
            hideNewButton
        />
    </x-modals>

    {{-- Clone-adapter modal. The form action + prefilled label switch
         per-active-tab via the tab-switch JS below so a single modal
         serves every adapter. New instance starts inactive and blank
         on company so the admin makes deliberate choices before
         turning it on. --}}
    <x-modals
        id="clone-adapter-modal"
        :title="trans('admin/settings/sync_adapters.clone_modal_title')"
        :action="$selected ? route('settings.adapters.clone', $selected->name()) : '#'"
    >
        <x-form.row
            :label="trans('admin/settings/sync_adapters.add_label_label')"
            name="label"
            input_div_class="col-md-8"
            :help_text="trans('admin/settings/sync_adapters.clone_label_help')"
        >
            <x-slot:input>
                <x-input.text
                    name="label"
                    id="clone_adapter_label"
                    :value="$selected ? trans('admin/settings/sync_adapters.clone_label_default', ['label' => $selected->label()]) : ''"
                    required
                />
            </x-slot:input>
        </x-form.row>

        <x-input.company-select
            name="company_id"
            id="modal_adapter_clone_company_id_select"
            :label="trans('admin/settings/sync_adapters.add_company_label')"
            :selected="null"
            hideNewButton
        />
    </x-modals>

    <script>
        // Point Save + Sync Now at the newly-active tab's form when the
        // user switches adapters. Sync Now lives per-pane in
        // the sync-adapter-panel component, so it needs no cross-tab
        // wiring here. The help tab is a real tab but not an adapter,
        // so Save + Delete hide when it's active (detected via absence
        // of a destroy-url data attribute).
        document.addEventListener('DOMContentLoaded', function () {
            var footer = document.getElementById('adapter-footer');
            var saveBtn = document.getElementById('adapter-save-button');
            var deleteTrigger = document.getElementById('adapter-delete-trigger');
            var cloneModal = document.getElementById('clone-adapter-modal');
            var cloneForm = cloneModal ? cloneModal.querySelector('form') : null;
            var cloneLabelInput = document.getElementById('clone_adapter_label');

            // Preselect the adapter-type in the Add adapter modal when
            // triggered from a catalog row's plus button. Falls back to
            // the empty option when opened from the sidebar tab or the
            // empty-state CTA button. select2 needs a trigger('change')
            // for its visible display to sync with the underlying value.
            $('#add-adapter-modal').on('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                var adapterType = trigger ? trigger.getAttribute('data-adapter-type') : null;
                $('#add-adapter-modal select[name="adapter_type"]').val(adapterType || '').trigger('change');
            });

            // Breadcrumb append (Sync Adapters > active-tab-label) is
            // handled globally in snipeit.js off the data-breadcrumb-label
            // attribute on each tab link.

            document.querySelectorAll('.side-tabs a[data-toggle="tab"]').forEach(function (tabLink) {
                $(tabLink).on('shown.bs.tab', function (e) {
                    var name = e.target.getAttribute('data-adapter-name');
                    var destroyUrl = e.target.getAttribute('data-adapter-destroy-url');

                    // Sync the ?adapter= query param with the visible
                    // tab so refresh + bookmark land the admin back
                    // on the same adapter. Uses replaceState (not
                    // pushState) so browser back doesn't fill history
                    // with one entry per tab click. Non-adapter tabs
                    // (help) drop the param entirely rather than leave
                    // a stale value from whichever adapter was last on.
                    // The tab-pane hash BS3 leaves behind gets stripped
                    // so shareable URLs stay clean (initial tab activation
                    // is server-driven, not hash-driven).
                    var url = new URL(window.location.href);
                    url.hash = '';

                    if (destroyUrl) {
                        if (footer) { footer.style.display = ''; }
                        saveBtn.setAttribute('form', 'adapter-form-' + name);
                        deleteTrigger.setAttribute('data-href', destroyUrl);
                        var confirmMsg = e.target.getAttribute('data-adapter-delete-confirm');
                        if (confirmMsg) {
                            deleteTrigger.setAttribute('data-content', confirmMsg);
                        }

                        // Retarget the clone modal at the newly-active
                        // adapter and reset the label input to the
                        // pre-baked "Copy of {label}" default.
                        var cloneUrl = e.target.getAttribute('data-adapter-clone-url');
                        var cloneLabelDefault = e.target.getAttribute('data-adapter-clone-label-default');
                        if (cloneForm && cloneUrl) {
                            cloneForm.setAttribute('action', cloneUrl);
                        }
                        if (cloneLabelInput && cloneLabelDefault) {
                            cloneLabelInput.value = cloneLabelDefault;
                        }

                        url.searchParams.set('adapter', name);
                    }
                    else {
                        if (footer) { footer.style.display = 'none'; }
                        url.searchParams.delete('adapter');
                    }

                    window.history.replaceState(null, '', url.toString());
                });
            });
        });
    </script>

@stop
