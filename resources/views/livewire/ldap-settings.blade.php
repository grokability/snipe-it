{{--
    Multi-step LDAP settings wizard. Four steps:
      1. Connection (server URL, TLS, cert)
      2. Authenticate & Scope (bind creds + base DN + filters)
      3. Attribute Mapping (LDAP-attr → Snipe-IT-field)
      4. Sync & Defaults (enable toggle, default group, forgot-pass URL)

    Layout order inside the box:
      box-header (title)
      box-body:
        step title + help text
        wizard progress indicator
        alerts (session flash + inline test result)
        step-specific field content
      box-footer:
        step-specific action buttons (Save & Continue, Back)

    A11y treatments:
      - Complete steps carry a checkmark icon in addition to color state
        so color-blind users have a non-color affordance (WCAG SC 1.4.1).
      - Every step button carries an sr-only "Step N of 5, {title},
        {state}" so screen readers announce full progress context
        (WCAG SC 1.3.1 / 2.4.6).
      - Locked buttons reference #wizard-locked-note via aria-describedby
        so SR users hear WHY the button is disabled.
      - wire:confirm arms on step buttons only when $dirty is true so
        users don't lose in-progress edits by clicking around the header.
      - On step change we dispatch a browser event; the Alpine listener
        on the wizard panel focuses the new panel's fieldset so
        keyboard + SR users move with the visual context.
--}}
<div class="ldap-wizard">
    {{-- Scoped dark-mode fix: for reasons I couldn't fully diagnose,
         .form-control inputs render white on dark inside this component
         while other forms elsewhere in the app read the theme correctly.
         Rather than reach for a global override that might collide with
         whatever mechanism the rest of the app uses, target only fields
         inside this wizard. Uses the theme's --box-bg + --color-fg so
         it matches whatever dark-mode palette the site is running. --}}
    <style>
        /* Scoped to actual input elements. Snipe-IT wraps checkboxes in
           `<label class="form-control">` (see checkbox-row), and applying
           the dark background/border to those labels breaks their
           inherit-from-parent styling. Explicit element targets keep
           this rule off the checkbox rows. */
        [data-theme="dark"] .ldap-wizard input.form-control,
        [data-theme="dark"] .ldap-wizard textarea.form-control,
        [data-theme="dark"] .ldap-wizard select.form-control {
            background-color: var(--table-stripe-bg) !important;
            color: var(--color-fg) !important;
            /* border-top/bottom/left individually so the required-field
               `input:required { border-right: 6px solid orange }` marker
               still shows through. A shorthand `border-color` here
               would clobber the right side too. */
            border-top-color: var(--box-header-top-border-color) !important;
            border-bottom-color: var(--box-header-top-border-color) !important;
            border-left-color: var(--box-header-top-border-color) !important;
        }
        /* Non-required inputs pick up the same dark border on the right
           side. Skipped when :required so the orange marker still wins. */
        [data-theme="dark"] .ldap-wizard input.form-control:not(:required),
        [data-theme="dark"] .ldap-wizard textarea.form-control:not(:required),
        [data-theme="dark"] .ldap-wizard select.form-control:not(:required) {
            border-right-color: var(--box-header-top-border-color) !important;
        }
        [data-theme="dark"] .ldap-wizard input.form-control::placeholder,
        [data-theme="dark"] .ldap-wizard textarea.form-control::placeholder {
            color: var(--text-help);
        }

        /* Restore Bootstrap 3's .has-error red border on inputs. The
           rules above set border-top/bottom/left/right individually
           with !important, which beats Bootstrap's plain
           `.has-error .form-control { border-color: #a94442 }`. This
           override re-asserts red on all four sides so a field in an
           error state actually LOOKS wrong instead of just having a
           red label + inline message. Also covers light mode since
           Snipe-IT's AdminLTE overrides use the same has-error class. */
        .ldap-wizard .has-error input.form-control,
        .ldap-wizard .has-error textarea.form-control,
        .ldap-wizard .has-error select.form-control {
            border-color: #a94442 !important;
        }

        /* Stepper "step complete" celebration. Two icons stacked in a
           fixed-width wrapper. Default state: star hidden, green check
           visible (so all older completed steps just show the check).
           When persistAndAdvance sets justCompletedStep to a step
           number, that step's wrapper gets .step-just-completed, and
           these keyframes fire once (CSS animations do not re-run on
           attribute preservation, so morphdom re-render is fine). The
           star spins with a decelerating cubic-bezier, fades out, and
           the checkmark fades in with a small pop. */
        .ldap-wizard .stepper-icon-wrapper {
            display: inline-block;
            position: relative;
            width: 1.1em;
            height: 1em;
            vertical-align: middle;
            margin-right: 0.15em;
        }

        .ldap-wizard .stepper-icon-wrapper .stepper-star,
        .ldap-wizard .stepper-icon-wrapper .stepper-check {
            position: absolute;
            left: 0;
            top: 0;
        }

        .ldap-wizard .stepper-icon-wrapper .stepper-star {
            opacity: 0;
        }

        .ldap-wizard .stepper-icon-wrapper .stepper-check {
            opacity: 1;
        }

        .ldap-wizard .stepper-icon-wrapper.step-just-completed .stepper-star {
            color: #f0ad4e;
            opacity: 1;
            animation: ldap-stepper-star 900ms cubic-bezier(0.15, 0.6, 0.2, 1) forwards;
        }

        .ldap-wizard .stepper-icon-wrapper.step-just-completed .stepper-check {
            opacity: 0;
            animation: ldap-stepper-check 350ms ease 550ms forwards;
        }

        @keyframes ldap-stepper-star {
            0% {
                transform: rotate(0deg) scale(1);
                opacity: 1;
            }
            70% {
                transform: rotate(900deg) scale(1.15);
                opacity: 1;
            }
            100% {
                transform: rotate(1080deg) scale(1.3);
                opacity: 0;
            }
        }

        @keyframes ldap-stepper-check {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            60% {
                transform: scale(1.25);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ldap-wizard .stepper-icon-wrapper.step-just-completed .stepper-star {
                animation: none;
                opacity: 0;
            }

            .ldap-wizard .stepper-icon-wrapper.step-just-completed .stepper-check {
                animation: none;
                opacity: 1;
            }
        }

        /* Modal body text inherits AdminLTE's default dark-grey in
           light mode and the theme's neutral fg in dark mode, both of
           which are unreadable on the red .modal-danger background.
           Force white so paragraph and inline text sit legibly against
           the red panel. Scoped to the disable-LDAP modal so we don't
           bleed into other .modal-danger usages elsewhere in the app. */
        #disableLdapModal.modal-danger .modal-body,
        #disableLdapModal.modal-danger .modal-body p {
            color: #fff;
        }
    </style>

    <div class="panel box box-default">
        <div class="box-header with-border">
            <h2 class="box-title">
                <x-icon type="ldap"/>
                {{ $currentStep === 5
                    ? trans('admin/settings/general.ldap_wizard.done.title')
                    : ($this->stepTitles[$currentStep] ?? '') }}
            </h2>
            {{-- One-click disable when LDAP is currently on. Renders on
                 every step (via the shared header) so admins never have
                 to walk to step 4 to turn it off. Fires an AdminLTE
                 .modal-danger red-header confirm rather than a browser
                 native prompt so it matches the rest of the app's
                 destructive-action UX. --}}
            @if ($snipeSettings->ldap_enabled == '1')
                <div class="box-tools pull-right">
                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        data-toggle="modal"
                        data-target="#disableLdapModal"
                        @disabled(config('app.lock_passwords'))
                    >
                        <x-icon type="ban" />
                        {{ trans('admin/settings/general.ldap_wizard.disable_ldap_button') }}
                    </button>
                </div>
            @endif
        </div>

        <div class="box-body">

            <span id="wizard-locked-note" class="sr-only">
                {{ trans('admin/settings/general.ldap_wizard.locked_help') }}
            </span>

            {{-- Step title + help text, always the current step's copy. --}}
            @php
                $stepHelpKey = match ($currentStep) {
                    1 => 'admin/settings/general.ldap_wizard.step_connection_help',
                    2 => 'admin/settings/general.ldap_wizard.step_authscope_help',
                    3 => 'admin/settings/general.ldap_wizard.step_mapping_help',
                    4 => 'admin/settings/general.ldap_wizard.step_sync_help',
                    default => null,
                };
            @endphp
            @if ($stepHelpKey)
                <x-form.legend for="{{ $currentStep }}" help_text="{!! trans($stepHelpKey) !!}" />
            @endif

            {{-- Wizard progress indicator. Same .bs-wizard class the
                 quickstart setup layout + importer modal use. Flex + flex:1 on
                 children rather than bootstrap col-md-*, so the layout
                 stays uniform regardless of step count. --}}
            <div class="bs-wizard" style="border-bottom:0; margin-bottom: 25px; display: flex;" role="group" aria-label="{{ trans('admin/settings/general.ldap_wizard.progress_label') }}">
                @foreach ($this->stepTitles as $stepNum => $stepTitle)
                    @php
                        $state = $stepNum < $currentStep
                            ? 'complete'
                            : ($stepNum === $currentStep ? 'active' : 'disabled');
                        $reachable = $stepNum <= $highestStepReached;

                        $srState = match ($state) {
                            'complete' => trans('admin/settings/general.ldap_wizard.state_complete'),
                            'active' => trans('admin/settings/general.ldap_wizard.state_current'),
                            default => trans('admin/settings/general.ldap_wizard.state_locked'),
                        };
                    @endphp
                    <div class="bs-wizard-step {{ $state }}" style="flex: 1;">
                        <div class="text-center bs-wizard-stepnum">
                            <button
                                type="button"
                                wire:click="goToStep({{ $stepNum }})"
                                @if ($dirty && $stepNum !== $currentStep && $reachable)
                                    wire:confirm="{{ trans('admin/settings/general.ldap_wizard.confirm_discard') }}"
                                @endif
                                @disabled(! $reachable)
                                style="padding: 0; border: 0; background: transparent; color: inherit; font-weight: {{ $stepNum === $currentStep ? '600' : '400' }};"
                                aria-current="{{ $stepNum === $currentStep ? 'step' : 'false' }}"
                                @unless ($reachable) aria-describedby="wizard-locked-note" @endunless
                            >
                                <span aria-hidden="true">
                                    @if ($state === 'complete')
                                        {{-- Two icons stacked in a fixed-width
                                             wrapper. Default paint: star hidden,
                                             check visible. When this step number
                                             matches $justCompletedStep (set by
                                             persistAndAdvance right before the
                                             step advance), the .step-just-completed
                                             class kicks in and the CSS keyframes
                                             below spin the yellow star into a
                                             green checkmark. --}}
                                        <span class="stepper-icon-wrapper @if ($justCompletedStep === $stepNum) step-just-completed @endif">
                                            <x-icon type="star" class="stepper-star"/>
                                            <x-icon type="checkmark" class="text-success stepper-check"/>
                                        </span>
                                    @endif
                                    {{ $stepNum }}<span class="hidden-xs">. {{ $stepTitle }}</span>
                                </span>
                                <span class="sr-only">
                                    {{ trans('admin/settings/general.ldap_wizard.step_of', ['num' => $stepNum, 'total' => 4]) }},
                                    {{ $stepTitle }}, {{ $srState }}
                                </span>
                            </button>
                        </div>
                        <div class="progress" aria-hidden="true">
                            <div class="progress-bar"></div>
                        </div>
                        <span class="bs-wizard-dot" aria-hidden="true"></span>
                    </div>
                @endforeach
            </div>

            {{-- Step-specific field content. Focusable wrapper so the
                 Alpine listener can move focus here after a step change.
                 Inline test-status alert renders INSIDE this panel (right
                 above the fields it relates to) so a failed network test
                 sits next to what the user needs to fix. Session flashes
                 are intentionally NOT rendered here. Steps 1-3 advance
                 in place (the wizard checkmark IS the success signal),
                 and the step 4 / disableLdap paths redirect to a page
                 that renders session flashes via the app layout. --}}
            <div
                id="wizard-panel"
                tabindex="-1"
                wire:key="wizard-panel-{{ $currentStep }}"
                x-data="{
                    focusFirstField() {
                        const selector = 'input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=submit]):not([type=button]), textarea';
                        const el = this.$el.querySelector(selector);
                        if (el) el.focus(); else this.$el.focus();
                    }
                }"
                x-init="$nextTick(() => focusFirstField())"
                x-on:wizard-step-changed.window="
                    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    setTimeout(() => focusFirstField(), reduced ? 0 : 1000);
                "
                class="form-horizontal"
                style="outline: none;"
            >
                {{-- Panel-top alert is for INFRASTRUCTURE feedback
                     (connect failures, TLS handshake, bind rejection).
                     Step 3's lookup-success alert renders inside the
                     well next to the search box instead, since it's
                     scoped to what the user just searched. The check
                     below suppresses it here for that case. --}}
                @if ($testStatus && ! ($currentStep === 3 && $testStatus === 'success'))
                    <x-alert
                        :type="$testStatus === 'success' ? 'success' : 'danger'"
                        :role="$testStatus === 'success' ? 'status' : 'alert'"
                        :icon="$testStatus === 'success' ? 'checkmark' : 'warning'"
                    >
                        {!! $testMessage !!}
                    </x-alert>
                @endif

                @if ($currentStep === 1)
                    <!-- AD flag -->
                    <x-form.checkbox-row
                        name="is_ad"
                        wire:model.live="is_ad"
                        :label="trans('admin/settings/general.ad')"
                        :checked="$is_ad"
                    />

                    <!-- AD Domain (only when is_ad is checked) -->
                    @if ($is_ad)
                        <x-form.row
                            name="ad_domain"
                            :label="trans('admin/settings/general.ad_domain')"
                            help_text="{!! trans('admin/settings/general.ad_domain_help') !!}"
                        >
                            <x-slot:input>
                                <x-input.text
                                    name="ad_domain"
                                    wire:model="ad_domain"
                                    placeholder="{{ trans('general.example').'example.com' }}"
                                    :required="true"
                                />
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    <!-- LDAP Server -->
                    <x-form.row
                        name="ldap_server"
                        :label="trans('admin/settings/general.ldap_server')"
                        help_text="{!! trans('admin/settings/general.ldap_server_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.text
                                name="ldap_server"
                                wire:model.live.debounce.500ms="ldap_server"
                                placeholder="{{ trans('general.example').'ldap://ldap.example.com' }}"
                                :required="true"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- Start TLS -->
                    <x-form.checkbox-row
                        name="ldap_tls"
                        wire:model.live="ldap_tls"
                        :label="trans('admin/settings/general.ldap_tls')"
                        :checked="$ldap_tls"
                        help_text="{!! trans('admin/settings/general.ldap_tls_help') !!}"
                    />

                    <!-- Ignore LDAP certificate -->
                    <x-form.checkbox-row
                        name="ldap_server_cert_ignore"
                        wire:model.live="ldap_server_cert_ignore"
                        :label="trans('admin/settings/general.ldap_server_cert_ignore')"
                        :checked="$ldap_server_cert_ignore"
                        help_text="{!! trans('admin/settings/general.ldap_server_cert_help') !!}"
                    />

                    <!-- Client TLS key -->
                    <x-form.row
                        name="ldap_client_tls_key"
                        :label="trans('admin/settings/general.ldap_client_tls_key')"
                    >
                        <x-slot:input>
                            <x-input.textarea
                                name="ldap_client_tls_key"
                                wire:model="ldap_client_tls_key"
                                rows="4"
                                :placeholder="sprintf('%s-----BEGIN RSA PRIVATE KEY-----%s1234567890%s-----END RSA PRIVATE KEY-----', trans('general.example'), PHP_EOL, PHP_EOL)"
                                :required="$ldap_client_tls_cert !== ''"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- Client TLS cert -->
                    <x-form.row
                        name="ldap_client_tls_cert"
                        :label="trans('admin/settings/general.ldap_client_tls_cert')"
                        help_text="{!! trans('admin/settings/general.ldap_client_tls_cert_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.textarea
                                name="ldap_client_tls_cert"
                                wire:model="ldap_client_tls_cert"
                                rows="4"
                                :placeholder="sprintf('%s-----BEGIN CERTIFICATE-----%s1234567890%s-----END CERTIFICATE-----', trans('general.example'), PHP_EOL, PHP_EOL)"
                                :required="$ldap_client_tls_key !== ''"
                            />
                        </x-slot:input>
                    </x-form.row>

                @elseif ($currentStep === 2)
                    {{-- Merged Authenticate + Scope step. Bind credentials
                         appear next to the base DN so users composing a
                         full admin DN can see the base DN portion right
                         alongside. That was the friction that pushed us
                         to combine what used to be two separate steps. --}}

                    <!-- Base Bind DN, placed first so users compose their
                         admin DN with the base DN already visible. -->
                    <x-form.row
                        name="ldap_basedn"
                        :label="trans('admin/settings/general.ldap_basedn')"
                        help_text="{!! trans('admin/settings/general.ldap_wizard.ldap_basedn_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.text
                                name="ldap_basedn"
                                wire:model.live.debounce.500ms="ldap_basedn"
                                placeholder="{{ trans('general.example').'ou=users,dc=example,dc=com' }}"
                                :required="true"
                                :ignore-autofill="true"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- Bind username -->
                    <x-form.row
                        name="ldap_uname"
                        :label="trans('admin/settings/general.ldap_uname')"
                        help_html="{!! trans('admin/settings/general.ldap_wizard.ldap_uname_help') !!}"
                    >
                        <x-slot:input>
                            {{-- Placeholder swaps based on the step-1 AD flag:
                                 UPN form for AD, full DN form otherwise. --}}
                            <x-input.text
                                name="ldap_uname"
                                wire:model.live.debounce.500ms="ldap_uname"
                                placeholder="{{ trans('general.example').($is_ad ? 'admin@example.com' : 'cn=admin,dc=example,dc=com') }}"
                                :ignore-autofill="true"
                                :required="true"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- Bind password (with show/hide toggle) -->
                    <x-form.row
                        name="ldap_pword"
                        :label="trans('admin/settings/general.ldap_pword')"
                        help_text="{!! trans('admin/settings/general.ldap_wizard.ldap_pword_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.password
                                name="ldap_pword"
                                wire:model.live.debounce.500ms="ldap_pword"
                                :required="true"
                                :ignore-autofill="true"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- LDAP Filter -->
                    <x-form.row
                        name="ldap_filter"
                        :label="trans('admin/settings/general.ldap_filter')"
                        help_text="{!! trans('admin/settings/general.ldap_wizard.ldap_filter_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.text
                                name="ldap_filter"
                                wire:model.live.debounce.500ms="ldap_filter"
                                placeholder="{{ trans('general.example').'&(cn=*)' }}"
                                :ignore-autofill="true"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- LDAP Auth Filter Query -->
                    <x-form.row
                        name="ldap_auth_filter_query"
                        :label="trans('admin/settings/general.ldap_auth_filter_query')"
                        help_text="{!! trans('admin/settings/general.ldap_wizard.ldap_auth_filter_query_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.text
                                name="ldap_auth_filter_query"
                                wire:model.live.debounce.500ms="ldap_auth_filter_query"
                                placeholder="{{ trans('general.example').'uid=' }}"
                                :required="true"
                                :ignore-autofill="true"
                            />
                        </x-slot:input>
                    </x-form.row>

                @elseif ($currentStep === 3)
                    {{-- Single-column x-form.row layout. Only
                         ldap_username_field + ldap_fname_field are
                         required. Everything else is optional. The
                         directory just skips syncing whatever attribute
                         isn't mapped. Trans labels shortened to drop the
                         "LDAP " prefix (the step title already provides
                         that context). --}}
                    {{-- Field descriptors come from $this->mappingFields
                         (computed on the component). Placeholders swap
                         between AD (samaccountname, streetaddress, etc.)
                         and non-AD (uid, street, etc.) based on the
                         is_ad flag from step 1. Fifth tuple element is
                         an optional help-text trans key, only set on
                         fields with per-field operational notes carried
                         over from the legacy form. --}}
                    @foreach ($this->mappingFields as [$propName, $labelKey, $placeholderExample, $required, $helpKey])
                        <x-form.row
                            :name="$propName"
                            :label="trans('admin/settings/general.'.$labelKey)"
                            help_html="{!! $helpKey ? trans('admin/settings/general.'.$helpKey) : '' !!}"
                        >
                            <x-slot:input>
                                <x-input.text
                                    :name="$propName"
                                    wire:model.blur="{{ $propName }}"
                                    :placeholder="$placeholderExample !== '' ? trans('general.example').$placeholderExample : ''"
                                    :required="$required"
                                    :ignore-autofill="true"
                                />
                            </x-slot:input>
                        </x-form.row>
                    @endforeach

                    {{-- Invert active flag lives in its own row below the
                         grid. It's a checkbox, not a text input, and
                         belongs conceptually with ldap_active_flag but
                         doesn't visually fit the two-column layout. --}}
                    <x-form.checkbox-row
                        name="ldap_invert_active_flag"
                        wire:model.live="ldap_invert_active_flag"
                        :label="trans('admin/settings/general.ldap_invert_active_flag')"
                        :checked="$ldap_invert_active_flag"
                        help_text="{!! trans('admin/settings/general.ldap_invert_active_flag_help') !!}"
                    />

                    {{-- Sample-lookup section, boxed in an x-well so it
                         reads as a "try it" tool distinct from the field
                         list above. Fires wire:click directly (not the
                         Save & Continue flow) so users can iterate on
                         the preview without triggering step advance. --}}
                    <x-well style="margin-top: 20px;" class="col-md-8 col-md-offset-2">
                        <h4 style="margin-top: 0;">
                            <x-icon type="search" />
                            {{ trans('admin/settings/general.ldap_wizard.mapping.sample_username_label') }}
                        </h4>
                        <p class="help-block" style="margin-top: 0;">
                            {!! trans('admin/settings/general.ldap_wizard.mapping.sample_username_help') !!}
                        </p>

                        <div class="input-group">
                            <x-input.text
                                name="test_sample_username"
                                wire:model.live.debounce.500ms="test_sample_username"
                                :placeholder="trans('admin/settings/general.ldap_wizard.mapping.sample_username_placeholder')"
                                :ignore-autofill="true"
                            />
                            <span class="input-group-btn">
                                <button
                                    type="button"
                                    wire:click="runStep3NetworkTest"
                                    wire:loading.attr="disabled"
                                    wire:target="runStep3NetworkTest"
                                    class="btn btn-default"
                                    @disabled(trim($test_sample_username) === '')
                                >
                                    <span wire:loading.remove wire:target="runStep3NetworkTest">
                                        <x-icon type="checkmark" />
                                    </span>
                                    <span wire:loading wire:target="runStep3NetworkTest">
                                        <x-icon type="spinner" />
                                    </span>
                                    {{ trans('admin/settings/general.ldap_wizard.mapping.lookup_button') }}
                                </button>
                            </span>
                        </div>

                        {{-- Inline lookup errors (not_found, multiple_found,
                             empty sample) render here, right below the
                             search box where results would appear. --}}
                        <x-form.error name="test_sample_username" />

                        {{-- Lookup-success alert lives inside the well
                             so it sits with the search box and the
                             preview table it introduces. Infrastructure
                             errors (connect/bind failures) still surface
                             at the top of the panel because they suggest
                             going back to earlier steps. --}}
                        @if ($testStatus === 'success')
                            <x-alert type="success" role="status" icon="checkmark" style="margin-top: 15px;">
                                {!! $testMessage !!}
                            </x-alert>
                        @endif

                        {{-- Preview table populated by a successful
                             lookup. Muted rows for unmapped fields or
                             mapped attributes the entry doesn't carry.
                             Makes it obvious where the mapping produces
                             no data. --}}
                        @if (! empty($step3TestAttributes))
                            <p class="text-muted" style="margin-top: 15px;"><small><code>{{ $step3TestDn }}</code></small></p>
                            <table class="table table-condensed table-striped" style="margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th>{{ trans('admin/settings/general.ldap_wizard.mapping.preview_snipe_field') }}</th>
                                        <th>{{ trans('admin/settings/general.ldap_wizard.mapping.preview_ldap_attribute') }}</th>
                                        <th>{{ trans('admin/settings/general.ldap_wizard.mapping.preview_ldap_value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($step3TestAttributes as $snipeField => $preview)
                                        <tr>
                                            <td>{{ $snipeField }}</td>
                                            <td>
                                                @if ($preview['attr'])
                                                    <code>{{ $preview['attr'] }}</code>
                                                @else
                                                    <span class="text-muted"><em>{{ trans('admin/settings/general.ldap_wizard.mapping.field_not_mapped') }}</em></span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($preview['value'] !== null)
                                                    {{ $preview['value'] }}
                                                @elseif ($preview['attr'])
                                                    <span class="text-muted"><em>{{ trans('admin/settings/general.ldap_wizard.mapping.value_missing') }}</em></span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </x-well>

                @elseif ($currentStep === 4)
                    {{-- ldap_enabled is intentionally not rendered here.
                         Reaching step 4 means the earlier steps verified
                         the connection, bind, and mapping. The save
                         action flips ldap_enabled=1 on the user's behalf.
                         Users who want to disable LDAP later toggle it
                         from the LDAP settings page directly. --}}

                    <!-- Password sync -->
                    <x-form.checkbox-row
                        name="ldap_pw_sync"
                        wire:model.live="ldap_pw_sync"
                        :label="trans('admin/settings/general.ldap_wizard.sync.ldap_pw_sync_label')"
                        :checked="$ldap_pw_sync"
                        help_text="{!! trans('admin/settings/general.ldap_pw_sync_help') !!}"
                    />

                    <!-- Default permissions group -->
                    <x-form.row
                        name="ldap_default_group"
                        :label="trans('admin/settings/general.ldap_default_group')"
                        help_text="{!! trans('admin/settings/general.ldap_wizard.sync.default_group_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.select
                                name="ldap_default_group"
                                wire:model="ldap_default_group"
                                :selected="$ldap_default_group"
                                :options="[
                                    '' => trans('admin/settings/general.ldap_wizard.sync.no_default_group'),
                                ] + $this->permissionGroups"
                                :forLivewire="true"
                                style="width: 100%"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <!-- Custom forgot-password URL -->
                    <x-form.row
                        name="custom_forgot_pass_url"
                        :label="trans('admin/settings/general.custom_forgot_pass_url')"
                        help_text="{!! trans('admin/settings/general.ldap_wizard.sync.custom_forgot_pass_url_help') !!}"
                    >
                        <x-slot:input>
                            <x-input.text
                                type="url"
                                name="custom_forgot_pass_url"
                                wire:model.blur="custom_forgot_pass_url"
                                placeholder="{{ trans('general.example').'https://my.ldapserver-forgotpass.com' }}"
                            />
                        </x-slot:input>
                    </x-form.row>

                @elseif ($currentStep === 5)
                    {{-- Completion summary. Reached only via a successful
                         save on step 4. Points admins at sync-scheduling
                         options. Login is live but recurring user sync
                         has to be scheduled separately since the app
                         ships no default schedule for snipeit:ldap-sync. --}}


                    <div class="col-md-12">
                        <h2>{{ trans('admin/settings/general.ldap_wizard.done.subtitle') }}</h2>
                        <p>{{ trans('admin/settings/general.ldap_wizard.done.intro') }}</p>
                        <p>{{ trans('admin/settings/general.ldap_wizard.done.sync_intro') }}</p>
                        <br><br>

                        <br><br><br><br><br>
                    </div>

                @else
                    <x-alert type="info" role="status">
                        {{ trans('admin/settings/general.ldap_wizard.placeholder') }}
                    </x-alert>
                @endif

            </div>

            <div class="row">
            <div class="col-md-8 col-md-offset-3">
                {{-- Verification-in-progress hint. Only visible while
                     saveAndAdvance is running so users don't wonder why
                     the button "hung". The actual delay is the LDAP
                     handshake + bind, which is out of our control.
                     .delay.longer waits 500ms before showing so fast
                     requests (no-op advances, cached lookups) don't
                     briefly flash the hint at the user, which read as
                     "you missed something" on quick steps. Only slow
                     LDAP round-trips actually paint it. --}}
                <p wire:loading.delay.longer wire:target="saveAndAdvance" class="text-info" role="status">
                    <x-icon type="spinner" />
                    <strong>{{ trans('admin/settings/general.ldap_wizard.verifying_help') }}</strong>
                </p>

                <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
            </div>
            </div>

        </div>

        <div class="box-footer">
            @if ($currentStep <= 4)
                <div class="text-left col-md-6">
                    @if ($currentStep > 1)
                        <button type="button" wire:click="goToStep({{ $currentStep - 1 }})" class="btn btn-default">
                            <x-icon type="arrow-left" /> {{ trans('button.back') }}
                        </button>
                    @endif
                </div>
                <div class="text-right col-md-6">
                    {{-- Single Save & Continue button doubles as the test.
                         Runs syntax validation, the live network test (SSRF
                         filter + rate limit + audit + 3s response floor),
                         then persists + advances. Any failure short-circuits
                         with an inline error or alert. --}}
                    <button
                        type="button"
                        wire:click="saveAndAdvance"
                        wire:loading.attr="disabled"
                        wire:target="saveAndAdvance"
                        class="btn btn-primary"
                        @disabled(config('app.lock_passwords') || ! $this->canAdvance)
                    >
                        <span wire:loading.remove wire:target="saveAndAdvance">
                            @if ($currentStep === 4)
                                {{-- Return visitors (LDAP already on) see
                                     "Save" instead of "Save and Enable".
                                     Otherwise the header disable button
                                     and a bottom enable button imply
                                     contradictory states. --}}
                                {{ trans($ldap_enabled
                                    ? 'admin/settings/general.ldap_wizard.sync.save_and_finish'
                                    : 'admin/settings/general.ldap_wizard.sync.save_and_enable') }}
                            @else
                                {{ trans('admin/settings/general.ldap_wizard.save_and_next') }}
                                <x-icon type="arrow-right" />
                            @endif
                        </span>
                        <span wire:loading wire:target="saveAndAdvance">
                            <x-icon type="spinner" />
                            {{ trans('admin/settings/general.ldap_wizard.verifying') }}
                        </span>
                    </button>
                </div>
            @else
                {{-- Step 5 completion footer. Back returns to step 4 so
                     return visitors (who now land on step 5 by default
                     when LDAP is already enabled) can edit config
                     without hunting through the wizard stepper. Back
                     to Settings clears wizard progress and exits. --}}
                <div class="text-left col-md-6">
                    <button type="button" wire:click="goToStep({{ $currentStep - 1 }})" class="btn btn-default">
                        <x-icon type="arrow-left" /> {{ trans('button.back') }}
                    </button>
                </div>
                <div class="text-right col-md-6">
                    <button type="button" wire:click="finishWizard" class="btn btn-primary">
                        {{ trans('admin/settings/general.ldap_wizard.done.back_to_settings') }}
                        <x-icon type="arrow-right" />
                    </button>
                </div>
            @endif


        </div>

    </div>
    <x-form.help name="legacy_form" icon="help">
        Having trouble with the new wizard? You can <a href="{{ route('settings.ldap.index') }}">find
            the legacy form here</a>, but please do let us know what trouble you're having so we can fix it.
    </x-form.help>


    {{-- Disable-LDAP confirm modal. Only useful when LDAP is currently
         on; skipped otherwise so we don't emit dead markup. wire:ignore
         so Livewire's morphdom leaves Bootstrap's `.in`/`body.modal-open`
         state alone between wizard re-renders. The confirm button still
         fires wire:click because Livewire delegates click events at the
         document level. --}}
    @if ($snipeSettings->ldap_enabled == '1')
        <div
            wire:ignore
            class="modal modal-danger fade"
            id="disableLdapModal"
            tabindex="-1"
            role="dialog"
            aria-labelledby="disableLdapModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('general.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="disableLdapModalLabel">
                            <x-icon type="warning" />
                            {{ trans('admin/settings/general.ldap_wizard.disable_ldap_modal_title') }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>{{ trans('admin/settings/general.ldap_wizard.confirm_disable_ldap') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                            {{ trans('button.cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="disableLdap"
                            class="btn btn-outline pull-right"
                        >
                            <x-icon type="ban" />
                            {{ trans('admin/settings/general.ldap_wizard.disable_ldap_button') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
