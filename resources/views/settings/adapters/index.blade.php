@extends('layouts/default')

@section('title')
    {{ trans('admin/settings/sync_adapters.title') }}
    @parent
@stop

@section('content')

    <x-container class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">

        <x-box>
            <x-slot:header>
                <i class="fa-solid fa-network-wired" aria-hidden="true"></i>
                {{ trans('admin/settings/sync_adapters.title') }}
            </x-slot:header>

            {{-- Company filter. Reloads the page with ?company=blah so only
                 adapters scoped to the picked company show in the tab
                 list. 'shared' filters to adapters with no company_id
                 (built-ins available to all).  --}}
            <x-slot:header_right>
                <form method="GET" action="{{ route('settings.adapters.index') }}" style="display: inline-block;">
                    <select
                        name="company"
                        class="select2"
                        style="width: 220px"
                        onchange="this.form.submit()"
                        aria-label="{{ trans('admin/settings/sync_adapters.company_filter_label') }}"
                    >
                        <option value="">{{ trans('admin/settings/sync_adapters.company_filter_all') }}</option>
                        <option value="shared" @selected($selectedCompany === 'shared')>{{ trans('admin/settings/sync_adapters.company_filter_shared') }}</option>
                        @foreach ($companies as $companyId => $companyName)
                            <option value="{{ $companyId }}" @selected((string) $selectedCompany === (string) $companyId)>{{ $companyName }}</option>
                        @endforeach
                    </select>
                </form>
            </x-slot:header_right>

            <div class="sync-adapters-tabs">
                <div class="adapter-tab-list">
                    <ul class="nav nav-pills nav-stacked" role="tablist">
                        @foreach ($adapters as $adapter)
                            <li role="presentation" @class(['active' => $selected?->name() === $adapter->name()])>
                                <a
                                    href="#adapter-pane-{{ $adapter->name() }}"
                                    role="tab"
                                    data-toggle="tab"
                                    data-adapter-name="{{ $adapter->name() }}"
                                    data-adapter-destroy-url="{{ route('settings.adapters.destroy', $adapter->name()) }}"
                                    data-adapter-built-in="{{ $adapter->isBuiltIn() ? '1' : '0' }}"
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

                        {{-- Add-adapter tab. Not a real tab-pane. Clicking it opens the modal instead. --}}
                        <li role="presentation">
                            <a
                                href="#"
                                data-toggle="modal"
                                data-target="#add-adapter-modal"
                            >
                                <x-icon type="create"/>
                                {{ trans('admin/settings/sync_adapters.add_button') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="adapter-tab-content">
                    <div class="tab-content">
                        @foreach ($adapters as $adapter)
                            <div
                                role="tabpanel"
                                id="adapter-pane-{{ $adapter->name() }}"
                                @class(['tab-pane fade', 'active in' => $selected?->name() === $adapter->name()])
                            >
                                @include($adapter->settingsView(), ['adapter' => $adapter])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot:customfooter>
                <div class="box-footer">
                    <div class="row">
                        <div class="text-left col-md-6">
                            <button
                                id="adapter-delete-trigger"
                                type="button"
                                class="btn btn-danger delete-asset"
                                data-toggle="modal"
                                data-target="#dataConfirmModal"
                                data-href="{{ $selected ? route('settings.adapters.destroy', $selected->name()) : '' }}"
                                data-title="{{ trans('admin/settings/sync_adapters.delete_button') }}"
                                data-content="{{ trans('admin/settings/sync_adapters.delete_confirm') }}"
                                data-icon="fa fa-trash"
                                style="{{ $selected && ! $selected->isBuiltIn() ? '' : 'display: none;' }}"
                                onclick="return false;"
                                @disabled(config('app.lock_passwords') === true)
                            >
                                <x-icon type="delete"/>
                                {{ trans('admin/settings/sync_adapters.delete_button') }}
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

    <script>
        // Point Save + Sync Now at the newly-active tab's form when the
        // user switches adapters. Sync Now lives per-pane in
        // the sync-adapter-panel component, so it needs no cross-tab
        // wiring here.
        document.addEventListener('DOMContentLoaded', function () {
            var saveBtn = document.getElementById('adapter-save-button');
            var deleteTrigger = document.getElementById('adapter-delete-trigger');

            document.querySelectorAll('.sync-adapters-tabs a[data-toggle="tab"]').forEach(function (tabLink) {
                $(tabLink).on('shown.bs.tab', function (e) {
                    var name = e.target.getAttribute('data-adapter-name');
                    var destroyUrl = e.target.getAttribute('data-adapter-destroy-url');
                    var builtIn = e.target.getAttribute('data-adapter-built-in') === '1';

                    saveBtn.setAttribute('form', 'adapter-form-' + name);

                    if (builtIn) {
                        deleteTrigger.style.display = 'none';
                    }
                    else {
                        deleteTrigger.style.display = '';
                        deleteTrigger.setAttribute('data-href', destroyUrl);
                    }

                    // Sync the ?adapter= query param with the visible
                    // tab so refresh + bookmark land the admin back on
                    // the same adapter. Uses replaceState (not pushState)
                    // so browser back doesn't fill history with one
                    // entry per tab click.
                    var url = new URL(window.location.href);
                    url.searchParams.set('adapter', name);
                    window.history.replaceState(null, '', url.toString());
                });
            });
        });
    </script>

@stop
