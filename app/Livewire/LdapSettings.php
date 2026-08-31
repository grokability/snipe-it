<?php

namespace App\Livewire;

use App\Models\Ldap;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Multi-step Livewire wizard replacing the monolithic settings/ldap.blade.php.
 *
 * Four-step wizard: Connection, Authenticate + Scope (bind creds +
 * base DN + filters), Attribute Mapping, and Sync + Defaults.
 *
 * Per-step dispatch: saveAndAdvance() and canAdvance() both switch on
 * $currentStep to the appropriate step-specific method. Adding a new
 * step means implementing saveStep{N}(), canAdvanceStep{N}(), and
 * runStep{N}NetworkTest() and appending them to the match() arms.
 */
class LdapSettings extends Component
{
    // Test connection button ceiling. 10 attempts/minute per user per step is
    // comfortable for real debugging and useless for probing a meaningful
    // port range across even a single /24. Each step gets its own budget
    // via its own rate-limit key so a user legitimately debugging bind
    // credentials doesn't exhaust the connection-test budget.
    private const TEST_RATE_LIMIT_ATTEMPTS = 10;

    private const TEST_RATE_LIMIT_DECAY_SECONDS = 60;

    // Cap on ldap_bind() network activity. 5s is generous enough for a
    // real LDAPS handshake (Google Cloud Identity, Microsoft Entra) which
    // can push past 3s on the TLS negotiation alone.
    //
    // Note: there is no uniform response-time floor. An earlier iteration
    // padded every response to a fixed duration to defeat stopwatch
    // enumeration of internal targets, but the rate limit (10 tests/min
    // per user per step) + IP filter (blocks all RFC1918 / loopback /
    // cloud-metadata ranges) already cap the practical enumeration
    // surface. Adding a multi-second wait to every legit save-and-
    // continue click for a defense that rate-limit already handles is
    // a bad UX trade-off.
    private const TEST_NETWORK_TIMEOUT_SECONDS = 5;

    // Wizard nav state. currentStep is URL-synced (?step=N) so a page
    // reload keeps the user where they were. mount() clamps to
    // highestStepReached so URL fuzzing can't skip past locked steps,
    // though that would be kinda dumb to do anyway, since you'd
    // just be saving bad data that won't work with LDAP
    #[Url(as: 'step')]
    public int $currentStep = 1;

    public int $highestStepReached = 1;

    // Flipped true by any updated() hook and back to false on successful
    // save. Blade uses this to arm wire:confirm on wizard-header buttons
    // so users with unsaved edits get prompted before losing them to a
    // step jump. saveStep{N}() uses it to skip network tests when nothing
    // has changed since the last successful save.
    public bool $dirty = false;

    // Alert state (top-of-body). recordTestResult() populates.
    public ?string $testStatus = null;

    public ?string $testMessage = null;

    // Which step number was just completed on this render cycle.
    // Populated by persistAndAdvance() the moment before it advances
    // to the next step, consumed by the stepper's CSS animation so
    // the newly-checked step spins a yellow star that morphs into a
    // green checkmark. Cleared on goToStep, disableLdap, and
    // finishWizard so back-nav doesn't retrigger the animation.
    public ?int $justCompletedStep = null;

    // Read-only lock. Set from config('app.lock_passwords') in mount()
    // and blade uses it to render every wire:model input with the
    // `readonly` / `disabled` attribute so demo visitors can't retype
    // real LDAP creds into the wizard. Server-side enforcement lives
    // in updated(), which reverts any prop mutation back to the
    // persisted Setting values (defense against a caller that fakes
    // wire:model updates around the disabled UI).
    public bool $isReadOnly = false;

    // Properties that stay editable even when isReadOnly is on. The
    // sample-username field on step 3 has to remain writable so the
    // Test Find User preview still works, which is the one wizard
    // interaction we do want demo visitors to exercise.
    private const READ_ONLY_ALLOWED_PROPS = [
        'currentStep',
        'test_sample_username',
    ];

    // Step 1: Connection
    public bool $ldap_enabled = false;

    public bool $is_ad = false;

    public string $ad_domain = '';

    public string $ldap_server = '';

    public bool $ldap_tls = false;

    public bool $ldap_server_cert_ignore = false;

    public string $ldap_client_tls_key = '';

    public string $ldap_client_tls_cert = '';

    // Step 2: Bind credentials
    public string $ldap_uname = '';

    // Password is ALWAYS empty on mount. Never round-trip a stored
    // password to the browser. An empty value on save means "keep the
    // persisted encrypted password". Non-empty replaces it.
    public string $ldap_pword = '';

    // Merged into step 2 (Auth + Scope). Kept as separate properties for clarity.
    public string $ldap_basedn = '';

    public string $ldap_filter = '';

    public string $ldap_auth_filter_query = '';

    // Step 3: Attribute mapping. LDAP attribute names Snipe-IT reads
    // from each entry. Only ldap_username_field + ldap_fname_field are
    // required per the legacy StoreLdapSettings rules.
    public string $ldap_username_field = '';

    public string $ldap_fname_field = '';

    public string $ldap_lname_field = '';

    public string $ldap_display_name = '';

    public string $ldap_email = '';

    public string $ldap_emp_num = '';

    public string $ldap_phone_field = '';

    public string $ldap_mobile = '';

    public string $ldap_jobtitle = '';

    public string $ldap_manager = '';

    public string $ldap_dept = '';

    public string $ldap_address = '';

    public string $ldap_city = '';

    public string $ldap_state = '';

    public string $ldap_zip = '';

    public string $ldap_country = '';

    public string $ldap_location = '';

    public string $ldap_active_flag = '';

    public bool $ldap_invert_active_flag = false;

    // Step 4: Sync + defaults
    public bool $ldap_pw_sync = false;

    public ?int $ldap_default_group = null;

    public string $custom_forgot_pass_url = '';

    // Not persisted. Just the input for the step-4 "look up sample user"
    // test. Populated by the user, consumed by runStep4NetworkTest to
    // fetch a real LDAP entry so they can preview the mapping.
    public string $test_sample_username = '';

    // Populated by a successful step-4 test with the mapped attribute
    // values from the fetched LDAP entry. Blade renders as a preview
    // table so the user can verify their mapping picks up the right
    // values before advancing.
    public array $step3TestAttributes = [];

    public string $step3TestDn = '';

    /**
     * mount() only fires on the initial page render, not on subsequent
     * POST /livewire/update requests. Route-level middleware on the LDAP
     * settings wizard requires superadmin, but a snapshot replay lands
     * here without going through that middleware. boot() runs on every
     * Livewire request, so it catches both surfaces.
     */
    public function boot(): void
    {
        abort_unless(Gate::allows('superadmin'), 403);
    }

    public function mount(): void
    {
        $this->isReadOnly = (bool) config('app.lock_passwords');
        $this->hydrateFromPersisted();

        // Restore in-flight wizard progress from the session so a page
        // reload mid-wizard doesn't slam the user back to step 1. The
        // session key is user-scoped, only bumps forward, never back.
        // Cleared once ldap_enabled flips true (step 5 complete): from
        // that point the ldap_enabled=true branch below unlocks everything
        // on every mount, so the session pointer becomes redundant.
        $this->highestStepReached = max($this->highestStepReached, (int) session($this->progressSessionKey(), 1));

        // When LDAP is already enabled the whole wizard is unlocked
        // including step 5 (the completion / help screen). Return
        // visitors land on step 5 by default so they can reference
        // the sync-scheduling copy without walking through the four
        // config steps again. The Back button on step 5 and the
        // wizard stepper at the top both let them jump back to
        // earlier steps if they need to edit config.
        if ($this->ldap_enabled) {
            $this->highestStepReached = 5;
            if (! request()->has('step')) {
                $this->currentStep = 5;
            }
        }

        // Demo mode unlocks the wizard independent of ldap_enabled.
        // The save/advance methods are gated shut by lock_passwords
        // so a visitor with ldap_enabled=false would otherwise be
        // trapped on step 1 with no way to reach the Test Find User
        // preview on step 3. Unlocking the stepper here lets them
        // jump to any step. Fields stay locked via isReadOnly /
        // updated() enforcement.
        if ($this->isReadOnly) {
            $this->highestStepReached = 5;
        }

        // Clamp against total step count in case a session pointer
        // was seeded when the wizard had a different step layout.
        $this->highestStepReached = min($this->highestStepReached, 5);

        // Clamp URL-restored step to [1, highestStepReached]. Blocks
        // both direct URL tampering (?step=99) and stale bookmarks
        // pointing at a step the user hasn't unlocked yet.
        $this->currentStep = max(1, min($this->currentStep, $this->highestStepReached));
    }

    protected function progressSessionKey(): string
    {
        return 'ldap_wizard_highest_step:'.auth()->id();
    }

    /**
     * Load every wizard prop from the persisted Setting row. Called by
     * mount() on first render and by goToStep() when the user opts to
     * discard unsaved changes via the wire:confirm dialog. Password is
     * intentionally NOT hydrated (see property docstring).
     */
    protected function hydrateFromPersisted(): void
    {
        $setting = Setting::getSettings();

        $this->ldap_enabled = (bool) $setting->ldap_enabled;
        $this->is_ad = (bool) $setting->is_ad;
        $this->ad_domain = (string) $setting->ad_domain;
        $this->ldap_server = (string) $setting->ldap_server;
        $this->ldap_tls = (bool) $setting->ldap_tls;
        $this->ldap_server_cert_ignore = (bool) $setting->ldap_server_cert_ignore;
        $this->ldap_client_tls_key = (string) $setting->ldap_client_tls_key;
        $this->ldap_client_tls_cert = (string) $setting->ldap_client_tls_cert;

        $this->ldap_uname = (string) $setting->ldap_uname;
        // ldap_pword stays ''. See property docstring.

        $this->ldap_basedn = (string) $setting->ldap_basedn;
        $this->ldap_filter = (string) $setting->ldap_filter;
        $this->ldap_auth_filter_query = (string) $setting->ldap_auth_filter_query;

        $this->ldap_username_field = (string) $setting->ldap_username_field;
        $this->ldap_fname_field = (string) $setting->ldap_fname_field;
        $this->ldap_lname_field = (string) $setting->ldap_lname_field;
        $this->ldap_display_name = (string) $setting->ldap_display_name;
        $this->ldap_email = (string) $setting->ldap_email;
        $this->ldap_emp_num = (string) $setting->ldap_emp_num;
        $this->ldap_phone_field = (string) $setting->ldap_phone_field;
        $this->ldap_mobile = (string) $setting->ldap_mobile;
        $this->ldap_jobtitle = (string) $setting->ldap_jobtitle;
        $this->ldap_manager = (string) $setting->ldap_manager;
        $this->ldap_dept = (string) $setting->ldap_dept;
        $this->ldap_address = (string) $setting->ldap_address;
        $this->ldap_city = (string) $setting->ldap_city;
        $this->ldap_state = (string) $setting->ldap_state;
        $this->ldap_zip = (string) $setting->ldap_zip;
        $this->ldap_country = (string) $setting->ldap_country;
        $this->ldap_location = (string) $setting->ldap_location;
        $this->ldap_active_flag = (string) $setting->ldap_active_flag;
        $this->ldap_invert_active_flag = (bool) $setting->ldap_invert_active_flag;

        $this->ldap_pw_sync = (bool) $setting->ldap_pw_sync;
        $this->ldap_default_group = $setting->ldap_default_group ? (int) $setting->ldap_default_group : null;
        $this->custom_forgot_pass_url = (string) $setting->custom_forgot_pass_url;
    }

    #[Computed]
    public function stepTitles(): array
    {
        return [
            1 => trans('admin/settings/general.ldap_wizard.step_connection'),
            2 => trans('admin/settings/general.ldap_wizard.step_authscope'),
            3 => trans('admin/settings/general.ldap_wizard.step_mapping'),
            4 => trans('admin/settings/general.ldap_wizard.step_sync'),
        ];
    }

    /**
     * Whether a bind password is currently stored in the settings row.
     *
     * hydrateFromPersisted() intentionally leaves $ldap_pword as ''
     * to avoid round-tripping the plaintext to the browser, so the
     * summary on step 5 can't tell "no password saved" from "password
     * saved but not loaded" by looking at the property alone. This
     * computed checks the persisted row directly for the render.
     */
    #[Computed]
    public function hasPersistedLdapPword(): bool
    {
        return ! empty(Setting::getSettings()->ldap_pword);
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->highestStepReached) {
            // If the user had unsaved changes when they clicked the
            // stepper (dirty=true), the confirm dialog said "discard
            // them" and they accepted. Actually discard by re-loading
            // every prop from the persisted Setting row. Without this,
            // the cleared-but-not-saved in-memory values would still
            // be there when the user walked forward and hit Save &
            // Continue on the same step, silently persisting the
            // "discarded" edits (data loss).
            if ($this->dirty) {
                $this->hydrateFromPersisted();
            }

            $this->currentStep = $step;
            $this->dirty = false;
            $this->justCompletedStep = null;
            $this->clearTestResult();
            $this->dispatch('wizard-step-changed');
        }
    }

    // === Save & Continue dispatch ============================================

    public function saveAndAdvance()
    {
        // Demo mode: nothing to save (isReadOnly + updated() lock the
        // fields to seeded values), but visitors still want to walk
        // the wizard forward step by step to see each screen. Skip
        // validation / network test / persist and just advance. The
        // per-step Test Bind / Test Find User buttons on individual
        // steps remain available for anyone who wants to fire a live
        // request against the seeded Forumsys config.
        if (config('app.lock_passwords')) {
            if ($this->currentStep < 5) {
                $this->goToStep($this->currentStep + 1);
            }

            return null;
        }

        // Step 4 returns a redirect so Livewire client-side navigates to
        // the settings index once the final save succeeds. The earlier
        // steps advance in place and return null.
        return match ($this->currentStep) {
            1 => $this->saveStep1(),
            2 => $this->saveStep2(),
            3 => $this->saveStep3(),
            4 => $this->saveStep4(),
            default => null,
        };
    }

    #[Computed]
    public function canAdvance(): bool
    {
        return match ($this->currentStep) {
            1 => $this->canAdvanceStep1(),
            2 => $this->canAdvanceStep2(),
            3 => $this->canAdvanceStep3(),
            4 => $this->canAdvanceStep4(),
            default => false,
        };
    }

    // === Step 1: Connection ==================================================

    protected function saveStep1(): void
    {
        $this->validate(
            $this->step1SyntaxRules() + [
                'ad_domain' => 'nullable|required_if_accepted:is_ad',
            ],
            attributes: $this->step1SyntaxAttributes() + [
                'ad_domain' => trans('admin/settings/general.ad_domain'),
            ],
        );
        $this->validateTlsPair();

        if ($this->dirty) {
            $this->runStep1NetworkTest();
            if ($this->testStatus !== 'success') {
                return;
            }
        }

        $setting = Setting::getSettings();
        $setting->is_ad = $this->is_ad ? '1' : '0';
        $setting->ad_domain = $this->ad_domain;
        $setting->ldap_server = $this->ldap_server;
        $setting->ldap_tls = $this->ldap_tls ? '1' : '0';
        $setting->ldap_server_cert_ignore = $this->ldap_server_cert_ignore ? '1' : '0';
        $setting->ldap_client_tls_key = $this->ldap_client_tls_key;
        $setting->ldap_client_tls_cert = $this->ldap_client_tls_cert;

        $this->persistAndAdvance($setting);
    }

    protected function canAdvanceStep1(): bool
    {
        // Only cross-field syntactic gates go here. Empty-required-field
        // checks are intentionally NOT gated on the button. A disabled
        // button gives the user no feedback about WHY nothing happened,
        // so instead we let the click fire, let saveStep1()'s validate()
        // throw, and render inline errors under the offending field.
        //
        // The TLS pair XOR stays: it's a cross-field rule that would be
        // confusing to render under just one of the two file fields.
        if (($this->ldap_client_tls_key !== '') !== ($this->ldap_client_tls_cert !== '')) {
            return false;
        }

        return true;
    }

    /**
     * Step 1's syntax rules. Called from saveStep1(). Step 1's network
     * test path is not user-callable (we got rid of the button) so it doesn't need to run these
     * separately.
     */
    protected function step1SyntaxRules(): array
    {
        return [
            'ldap_server' => 'required|starts_with:ldap://,ldaps://',
            'ldap_client_tls_key' => [
                'nullable',
                'required_with:ldap_client_tls_cert',
                /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
                function ($attribute, $value, $fail) {
                    if (trim((string) $value) === '') {
                        return;
                    }
                    if (! @openssl_pkey_get_private($value)) {
                        $fail(trans('admin/settings/general.ldap_wizard.tls.key_parse_failed'));
                    }
                },
            ],
            'ldap_client_tls_cert' => [
                'nullable',
                'required_with:ldap_client_tls_key',
                /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
                function ($attribute, $value, $fail) {
                    if (trim((string) $value) === '') {
                        return;
                    }
                    $cert = @openssl_x509_read($value);
                    if (! $cert) {
                        $fail(trans('admin/settings/general.ldap_wizard.tls.cert_parse_failed'));

                        return;
                    }
                    $info = @openssl_x509_parse($cert);
                    if ($info && isset($info['validTo_time_t']) && $info['validTo_time_t'] < time()) {
                        $fail(trans('admin/settings/general.ldap_wizard.tls.cert_expired', [
                            'date' => date('Y-m-d', $info['validTo_time_t']),
                        ]));
                    }
                },
            ],
        ];
    }

    protected function step1SyntaxAttributes(): array
    {
        return [
            'ldap_server' => trans('admin/settings/general.ldap_server'),
            'ldap_client_tls_key' => trans('admin/settings/general.ldap_client_tls_key'),
            'ldap_client_tls_cert' => trans('admin/settings/general.ldap_client_tls_cert'),
        ];
    }

    protected function validateTlsPair(): void
    {
        if (trim($this->ldap_client_tls_key) === '' || trim($this->ldap_client_tls_cert) === '') {
            return;
        }
        $key = @openssl_pkey_get_private($this->ldap_client_tls_key);
        $cert = @openssl_x509_read($this->ldap_client_tls_cert);
        if (! $key || ! $cert) {
            return;
        }
        if (! @openssl_x509_check_private_key($cert, $key)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'ldap_client_tls_key' => trans('admin/settings/general.ldap_wizard.tls.pair_mismatch'),
            ]);
        }
    }

    protected function runStep1NetworkTest(): void
    {
        $this->clearTestResult();

        if (! $this->checkRateLimit('ldap-test-step1', 'ldap connection test')) {
            return;
        }

        $ipError = $this->serverPassesIpPolicy($this->ldap_server);
        if ($ipError !== null) {
            $this->recordFieldError('ldap_server', $ipError, 'ldap connection test');

            return;
        }

        if ($this->ldap_server_cert_ignore) {
            putenv('LDAPTLS_REQCERT=never');
        }

        $conn = @ldap_connect($this->ldap_server);
        if (! $conn) {
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.test.connect_failed', ['server' => $this->ldap_server]),
                'ldap connection test',
            );

            return;
        }

        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, self::TEST_NETWORK_TIMEOUT_SECONDS);

        if ($this->ldap_tls) {
            if (! @ldap_start_tls($conn)) {
                $this->recordTestResult(
                    'error',
                    trans('admin/settings/general.ldap_wizard.test.starttls_failed', ['error' => ldap_error($conn)]),
                    'ldap connection test',
                );
                @ldap_unbind($conn);

                return;
            }
        }

        $bindOk = @ldap_bind($conn);
        if (! $bindOk) {
            $errno = ldap_errno($conn);
            $ldapError = ldap_error($conn);
            @ldap_unbind($conn);

            if ($errno === -1) {
                $this->recordTestResult(
                    'error',
                    trans('admin/settings/general.ldap_wizard.test.connect_failed', ['server' => $this->ldap_server]).' ('.$ldapError.')',
                    'ldap connection test',
                );

                return;
            }
            // Any other errno means the server SPOKE to us in LDAP
            // protocol before rejecting. That's a successful connectivity
            // test. Auth belongs in step 2.
            $this->recordTestResult(
                'success',
                trans('admin/settings/general.ldap_wizard.test.connected_no_anon', ['server' => $this->ldap_server, 'error' => $ldapError]),
                'ldap connection test',
            );

            return;
        }

        @ldap_unbind($conn);
        $this->recordTestResult(
            'success',
            trans('admin/settings/general.ldap_wizard.test.connected', ['server' => $this->ldap_server]),
            'ldap connection test',
        );
    }

    // === Step 2: Authenticate + scope ========================================
    // Merged step: admin bind credentials + base DN + filter + auth-filter
    // query. Combined test = bind with the supplied credentials AND run a
    // search under the supplied base DN with the supplied filter. Either
    // failing aborts save. The old wizard had these as separate steps
    // (bind, then search) but the base DN is part of a full admin DN so
    // asking for the bind DN without the base DN in front of the user
    // was confusing.

    protected function saveStep2(): void
    {
        $this->validate($this->step2SyntaxRules(), attributes: $this->step2SyntaxAttributes());

        if ($this->dirty) {
            $this->runStep2NetworkTest();
            if ($this->testStatus !== 'success') {
                return;
            }
        }

        $setting = Setting::getSettings();
        $setting->ldap_uname = $this->ldap_uname;
        // Only overwrite the persisted encrypted password when the user
        // provided a new value. Blank pword = keep-what's-in-DB.
        if ($this->ldap_pword !== '') {
            $setting->ldap_pword = Crypt::encrypt($this->ldap_pword);
        }
        $setting->ldap_basedn = $this->ldap_basedn;
        $setting->ldap_filter = $this->ldap_filter;
        $setting->ldap_auth_filter_query = $this->ldap_auth_filter_query;

        // Clear the local password field so the next re-render doesn't
        // send the value back to the browser after a successful save.
        $this->ldap_pword = '';

        $this->persistAndAdvance($setting);
    }

    /**
     * Live-form wrapper around Ldap::shouldUseSaslExternal(). The
     * component is passed as-is because it carries the four properties
     * the predicate reads. Blade-accessible via #[Computed].
     */
    #[Computed]
    public function isSaslExternalCandidate(): bool
    {
        return Ldap::shouldUseSaslExternal($this);
    }

    protected function canAdvanceStep2(): bool
    {
        // SASL EXTERNAL (auto-detected in bindAdminToLdap when client
        // cert + key are populated and uname/pword are blank) skips
        // the empty-uname gate: those fields are meant to be blank on
        // that path.
        if (trim($this->ldap_uname) === '' && ! $this->isSaslExternalCandidate()) {
            return false;
        }
        if (trim($this->ldap_basedn) === '') {
            return false;
        }
        if (trim($this->ldap_auth_filter_query) === '') {
            return false;
        }

        return true;
    }

    protected function step2SyntaxRules(): array
    {
        // Password reuse-persisted logic: blank pword is OK when the
        // username is unchanged AND the DB has an encrypted password
        // to fall back on. Rule::when() rather than a closure because
        // Laravel's validator skips closure rules on empty values by
        // default (same reason `nullable` works implicitly), which
        // would let a blank pword sail past a closure-based required.
        $persisted = Setting::getSettings();
        $unameUnchanged = trim($this->ldap_uname) === trim((string) $persisted->ldap_uname);
        $canReusePersisted = $unameUnchanged && ! empty($persisted->ldap_pword);

        // Normalize DNs for comparison (lowercase + strip whitespace around
        // component separators). Used by the base-DN-equals-bind-DN closure
        // so `cn=admin, dc=example, dc=com` and `CN=Admin,dc=example,dc=com`
        // both match when they should.
        $normalizeDn = fn ($dn) => strtolower(preg_replace('/\s*,\s*/', ',', trim((string) $dn)));
        $bindDn = $normalizeDn($this->ldap_uname);

        // Auto-detected SASL EXTERNAL (client cert + key populated,
        // uname/pword blank) uses the TLS cert as the bind identity,
        // so ldap_uname / ldap_pword are optional on that path.
        $sasl = $this->isSaslExternalCandidate();
        $unameRule = $sasl ? ['nullable', 'max:191'] : 'required|max:191';
        $pwordRule = $sasl
            ? 'nullable'
            : \Illuminate\Validation\Rule::when(! $canReusePersisted, 'required');

        return [
            'ldap_uname' => $unameRule,
            'ldap_pword' => $pwordRule,
            'ldap_basedn' => [
                'required',
                // Guard against the common misconfiguration where the base
                // DN is set to the bind account itself (a leaf entry).
                // A subtree search under a leaf DN returns just that entry,
                // which is enough to sneak past the step-2 "at least one
                // match" check but will always return zero users on any
                // real filter. Catch it here with an explicit inline error
                // rather than letting the wizard advance and confuse the
                // user on step 3.
                /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
                function ($attribute, $value, $fail) use ($normalizeDn, $bindDn) {
                    if ($bindDn !== '' && $normalizeDn($value) === $bindDn) {
                        $fail(trans('admin/settings/general.ldap_wizard.search.basedn_equals_binddn'));
                    }
                },
            ],
            'ldap_filter' => ['nullable', 'regex:/^[^(]/'],
            'ldap_auth_filter_query' => 'required|not_in:uid=samaccountname',
        ];
    }

    protected function step2SyntaxAttributes(): array
    {
        return [
            'ldap_uname' => trans('admin/settings/general.ldap_uname'),
            'ldap_pword' => trans('admin/settings/general.ldap_pword'),
            'ldap_basedn' => trans('admin/settings/general.ldap_basedn'),
            'ldap_filter' => trans('admin/settings/general.ldap_filter'),
            'ldap_auth_filter_query' => trans('admin/settings/general.ldap_auth_filter_query'),
        ];
    }

    /**
     * Combined bind + search test. Binds with the form credentials
     * (never trusts persisted, the whole point is to verify what the
     * user just typed), then runs a search under the form base DN with
     * the form filter. Success = bind AND search both succeeded AND
     * search returned at least one entry (zero entries is functionally
     * broken for sync). Any failure surfaces a specific message
     * pointing at the actual problem (bind vs search).
     */
    protected function runStep2NetworkTest(): void
    {
        $this->clearTestResult();

        if (! $this->checkRateLimit('ldap-test-step2', 'ldap bind test')) {
            return;
        }

        $conn = $this->openLdapConnectionForTest('ldap bind test');
        if (! $conn) {
            return;
        }

        // Bind. SASL EXTERNAL uses the client cert loaded by
        // openLdapConnectionForTest() (via LDAP_OPT_X_TLS_CERTFILE /
        // _KEYFILE) as the auth identity, so no username / password
        // gets passed. Simple bind path resolves the password from the
        // form value first, otherwise falls back to the persisted
        // encrypted password when the username matches.
        $settings = Setting::getSettings();
        $server = (string) $settings->ldap_server;

        if ($this->isSaslExternalCandidate()) {
            // Success message uses $uname for the "Bound as ..."
            // interpolation. Under SASL EXTERNAL there is no bind
            // username, the client cert is the identity, so surface
            // that instead of leaving $uname undefined.
            $uname = trans('admin/settings/general.ldap_wizard.bind.sasl_external_identity');
            $bindOk = @ldap_sasl_bind($conn, null, null, 'EXTERNAL');
        } else {
            $uname = trim($this->ldap_uname);
            $pword = $this->ldap_pword;
            if ($pword === '' && $uname === trim((string) $settings->ldap_uname) && $settings->ldap_pword) {
                try {
                    $pword = Crypt::decrypt($settings->ldap_pword);
                } catch (\Exception $e) {
                    @ldap_unbind($conn);
                    $this->recordTestResult(
                        'error',
                        trans('admin/settings/general.ldap_wizard.bind.pword_decrypt_failed'),
                        'ldap bind test',
                    );

                    return;
                }
            }

            $bindOk = @ldap_bind($conn, $uname, $pword);
        }
        if (! $bindOk) {
            $errno = ldap_errno($conn);
            $ldapError = Ldap::bindError($conn);
            @ldap_unbind($conn);

            if ($errno === -1) {
                $this->recordTestResult(
                    'error',
                    trans('admin/settings/general.ldap_wizard.test.connect_failed', ['server' => $server]).' ('.$ldapError.')',
                    'ldap bind test',
                );

                return;
            }
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.bind.rejected', ['error' => $ldapError]),
                'ldap bind test',
            );

            return;
        }

        // Bind succeeded. Proceed to search under the base DN. Empty
        // filter falls back to (objectClass=*) which returns everything
        // under the base DN (matches legacy "no filter" semantics).
        $filter = trim($this->ldap_filter) !== ''
            ? '('.$this->ldap_filter.')'
            : '(objectClass=*)';

        $searchResult = @ldap_search($conn, $this->ldap_basedn, $filter, ['dn']);
        if (! $searchResult) {
            $ldapError = ldap_error($conn);
            @ldap_unbind($conn);
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.search.search_failed', ['error' => $ldapError]),
                'ldap search test',
            );

            return;
        }

        $count = ldap_count_entries($conn, $searchResult);
        @ldap_unbind($conn);

        if ($count === 0) {
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.search.no_results'),
                'ldap search test',
            );

            return;
        }

        $entriesLabel = $count === 1
            ? trans('admin/settings/general.ldap_wizard.search.entries_one')
            : trans('admin/settings/general.ldap_wizard.search.entries_other');
        $this->recordTestResult(
            'success',
            trans('admin/settings/general.ldap_wizard.combined.success', [
                'user' => $uname,
                'count' => $count,
                'entries' => $entriesLabel,
            ]),
            'ldap bind test',
        );
    }

    // === Step 3: Attribute mapping ========================================= =============================

    protected function saveStep3(): void
    {
        $this->validate($this->step3SyntaxRules(), attributes: $this->step3SyntaxAttributes());

        // No auto-test on Save. Attribute mapping is a client-input-only
        // thing. The Look up button previews it against a real user, but
        // that's an optional sanity check the user runs on demand to make sure
        // their syncs will look right and map all of the right fields, not
        // a network gate we run for them. Save + advance to step 5.
        $setting = Setting::getSettings();
        $setting->ldap_username_field = $this->ldap_username_field;
        $setting->ldap_fname_field = $this->ldap_fname_field;
        $setting->ldap_lname_field = $this->ldap_lname_field;
        $setting->ldap_display_name = $this->ldap_display_name;
        $setting->ldap_email = $this->ldap_email;
        $setting->ldap_emp_num = $this->ldap_emp_num;
        $setting->ldap_phone_field = $this->ldap_phone_field;
        $setting->ldap_mobile = $this->ldap_mobile;
        $setting->ldap_jobtitle = $this->ldap_jobtitle;
        $setting->ldap_manager = $this->ldap_manager;
        $setting->ldap_dept = $this->ldap_dept;
        $setting->ldap_address = $this->ldap_address;
        $setting->ldap_city = $this->ldap_city;
        $setting->ldap_state = $this->ldap_state;
        $setting->ldap_zip = $this->ldap_zip;
        $setting->ldap_country = $this->ldap_country;
        $setting->ldap_location = $this->ldap_location;
        $setting->ldap_active_flag = $this->ldap_active_flag;
        $setting->ldap_invert_active_flag = $this->ldap_invert_active_flag ? '1' : '0';

        $this->persistAndAdvance($setting);
    }

    protected function canAdvanceStep3(): bool
    {
        if (trim($this->ldap_username_field) === '') {
            return false;
        }
        if (trim($this->ldap_fname_field) === '') {
            return false;
        }

        return true;
    }

    /**
     * Step 3 rules mirror the legacy StoreLdapSettings:
     *   - ldap_username_field: required + not_in (sAMAccountName in
     *     LDAP is case-insensitive, storing the CamelCase form has
     *     been a recurring support case, so we deny it explicitly and
     *     the legacy custom validation message points at the fix).
     *   - ldap_fname_field: required (needed to populate user.first_name).
     *   - All other mapping fields are optional. Directories that don't
     *     carry that attribute just skip syncing it.
     */
    protected function step3SyntaxRules(): array
    {
        return [
            'ldap_username_field' => 'required|not_in:sAMAccountName',
            'ldap_fname_field' => 'required',
        ];
    }

    protected function step3SyntaxAttributes(): array
    {
        return [
            'ldap_username_field' => trans('admin/settings/general.ldap_username_field'),
            'ldap_fname_field' => trans('admin/settings/general.ldap_fname_field'),
        ];
    }

    /**
     * Step 3's optional preview: bind + search for a specific sample
     * username, fetch that entry's attributes, populate
     * $step3TestAttributes so the blade can render a preview table. Not
     * called from saveStep3. This is a user-triggered lookup only,
     * exposed via wire:click on the Look Up button. Public because
     * Livewire can't route wire:click to protected methods.
     */
    public function runStep3NetworkTest(): void
    {
        $this->clearTestResult();
        $this->step3TestAttributes = [];
        $this->step3TestDn = '';

        if (trim($this->test_sample_username) === '') {
            $this->recordFieldError(
                'test_sample_username',
                trans('admin/settings/general.ldap_wizard.mapping.no_sample'),
                'ldap search test',
            );

            return;
        }

        if (! $this->checkRateLimit('ldap-test-step3', 'ldap search test')) {
            return;
        }

        $conn = $this->openLdapConnectionForTest('ldap search test');
        if (! $conn) {
            return;
        }

        if (! $this->bindWithPersistedCredentials($conn, 'ldap search test')) {
            return;
        }

        // Build the lookup filter: combine the step-3 base filter (if any)
        // with a "username_field = sample" clause escaped against LDAP
        // filter injection.
        $escaped = ldap_escape($this->test_sample_username, '', LDAP_ESCAPE_FILTER);
        $userClause = '('.$this->ldap_username_field.'='.$escaped.')';
        $lookupFilter = trim($this->ldap_filter) !== ''
            ? '(&('.$this->ldap_filter.')'.$userClause.')'
            : $userClause;

        // Fetch only the attributes we're mapping. Avoids pulling the
        // whole entry when we only care about a handful.
        $requestedAttrs = array_values(array_filter(array_map(
            fn ($attr) => trim(strtolower((string) $attr)),
            Ldap::attributeMap($this),
        )));

        $searchResult = @ldap_search($conn, $this->ldap_basedn, $lookupFilter, $requestedAttrs);
        if (! $searchResult) {
            $ldapError = ldap_error($conn);
            @ldap_unbind($conn);
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.search.search_failed', ['error' => $ldapError]),
                'ldap search test',
            );

            return;
        }

        $count = ldap_count_entries($conn, $searchResult);
        if ($count === 0) {
            @ldap_unbind($conn);
            // Route lookup-shaped errors to the sample-username field's
            // inline slot rather than the top-of-body alert. They're
            // scoped to that field's value, and rendering them where
            // the results-would-go slot lives keeps the feedback local
            // to the lookup control. Blade renders via <x-form.error>
            // which escapes {{ $message }}, so no e() needed here.
            $this->recordFieldError(
                'test_sample_username',
                trans('admin/settings/general.ldap_wizard.mapping.not_found', [
                    'username' => $this->test_sample_username,
                    'filter' => $lookupFilter,
                    'basedn' => $this->ldap_basedn,
                ]),
                'ldap search test',
            );

            return;
        }
        if ($count > 1) {
            @ldap_unbind($conn);
            $this->recordFieldError(
                'test_sample_username',
                trans('admin/settings/general.ldap_wizard.mapping.multiple_found', [
                    'count' => $count,
                    'username' => $this->test_sample_username,
                    'filter' => $lookupFilter,
                ]),
                'ldap search test',
            );

            return;
        }

        $entry = ldap_first_entry($conn, $searchResult);
        $dn = ldap_get_dn($conn, $entry);
        $attributes = array_change_key_case((array) ldap_get_attributes($conn, $entry));
        @ldap_unbind($conn);

        // Build the preview table: for each Snipe-IT field, resolve the
        // configured LDAP attribute name to its actual value (or a
        // "not mapped" / "not present" marker for blade to render
        // muted). Attribute names are compared lowercase. LDAP is
        // case-insensitive on attribute names.
        $preview = [];
        $labels = Ldap::attributeLabels();
        foreach (Ldap::attributeMap($this) as $snipeField => $ldapAttr) {
            $label = $labels[$snipeField] ?? $snipeField;
            $ldapAttrLower = trim(strtolower((string) $ldapAttr));
            if ($ldapAttrLower === '') {
                $preview[$snipeField] = ['label' => $label, 'attr' => null, 'value' => null];

                continue;
            }
            $value = null;
            if (isset($attributes[$ldapAttrLower][0])) {
                $value = $attributes[$ldapAttrLower][0];
            }
            $preview[$snipeField] = ['label' => $label, 'attr' => $ldapAttr, 'value' => $value];
        }

        $this->step3TestDn = (string) $dn;
        $this->step3TestAttributes = $preview;

        $this->recordTestResult(
            'success',
            trans('admin/settings/general.ldap_wizard.mapping.found', ['dn' => $dn]),
            'ldap search test',
        );
    }

    // === Step 4: Sync + defaults (was step 5) ============================================

    protected function saveStep4()
    {
        $this->validate($this->step4SyntaxRules(), attributes: $this->step4SyntaxAttributes());

        // Reaching step 4 means the earlier steps verified the connection,
        // bind, and mapping, so enabling LDAP is the whole point of the
        // final save. ldap_enabled is not user-toggleable in the wizard.
        // It's forced to 1 here.
        $setting = Setting::getSettings();
        $setting->ldap_enabled = '1';
        $setting->ldap_pw_sync = $this->ldap_pw_sync ? '1' : '0';
        $setting->ldap_default_group = $this->ldap_default_group;
        $setting->custom_forgot_pass_url = $this->custom_forgot_pass_url;

        return $this->persistAndAdvance($setting);
    }

    protected function canAdvanceStep4(): bool
    {
        // No syntactic preconditions. User can save at any time to
        // toggle enable on/off. URL format validation runs at save.
        return true;
    }

    protected function step4SyntaxRules(): array
    {
        return [
            'custom_forgot_pass_url' => 'nullable|url',
            'ldap_default_group' => 'nullable|integer|exists:permission_groups,id',
        ];
    }

    protected function step4SyntaxAttributes(): array
    {
        return [
            'custom_forgot_pass_url' => trans('admin/settings/general.custom_forgot_pass_url'),
            'ldap_default_group' => trans('admin/settings/general.ldap_default_group'),
        ];
    }

    /**
     * Permission groups available for the step-5 default-group select.
     * Computed so the query only runs when the blade dereferences it
     * (steps 1-4 don't need it). Ordered alphabetically for pick-list
     * ergonomics.
     */
    #[Computed]
    public function permissionGroups(): array
    {
        return \App\Models\Group::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Row descriptors for the step-3 mapping loop. Each entry is
     *   [ prop name, label trans key, placeholder attribute example,
     *     required, optional per-field help trans key ].
     *
     * Placeholder examples toggle based on $is_ad because the two
     * common LDAP schemas name several attributes differently:
     *   - AD: samaccountname, streetaddress, department, co (full
     *     country name), useraccountcontrol
     *   - inetOrgPerson / posixAccount: uid, street, departmentNumber,
     *     c (ISO country code), no standard active-flag attribute
     * The rest (givenname, sn, mail, telephonenumber, mobile, title,
     * manager, l, st, postalcode) are shared across both.
     *
     * Computed so the placeholder set re-evaluates when a user toggles
     * the AD box on step 1.
     */
    #[Computed]
    public function mappingFields(): array
    {
        $usernameField = $this->is_ad ? 'samaccountname' : 'uid';
        $displayName = $this->is_ad ? 'displayname' : 'cn';
        $dept = $this->is_ad ? 'department' : 'departmentNumber';
        $address = $this->is_ad ? 'streetaddress' : 'street';
        $country = $this->is_ad ? 'co' : 'c';
        $activeFlag = $this->is_ad ? 'useraccountcontrol' : '';

        return [
            ['ldap_username_field', 'ldap_username_field', $usernameField, true, null],
            ['ldap_fname_field', 'ldap_fname_field', 'givenname', true, null],
            ['ldap_lname_field', 'ldap_lname_field', 'sn', false, null],
            ['ldap_display_name', 'ldap_display_name', $displayName, false, 'ldap_display_name_help'],
            ['ldap_email', 'ldap_email', 'mail', false, null],
            ['ldap_emp_num', 'ldap_emp_num', 'employeenumber', false, null],
            ['ldap_phone_field', 'ldap_phone', 'telephonenumber', false, null],
            ['ldap_mobile', 'ldap_mobile', 'mobile', false, null],
            ['ldap_jobtitle', 'ldap_jobtitle', 'title', false, null],
            ['ldap_manager', 'ldap_manager', 'manager', false, null],
            ['ldap_dept', 'ldap_dept', $dept, false, null],
            ['ldap_address', 'ldap_address', $address, false, null],
            ['ldap_city', 'ldap_city', 'l', false, null],
            ['ldap_state', 'ldap_state', 'st', false, null],
            ['ldap_zip', 'ldap_zip', 'postalcode', false, null],
            ['ldap_country', 'ldap_country', $country, false, null],
            ['ldap_location', 'ldap_location', 'physicaldeliveryofficename', false, 'ldap_location_help'],
            ['ldap_active_flag', 'ldap_active_flag', $activeFlag, false, 'ldap_activated_flag_help'],
        ];
    }

    // === Global wizard actions ==============================================

    /**
     * "Back to Settings" from the step 5 completion screen. Clears the
     * wizard-progress session key so a return visit lands on step 1
     * with a fresh state rather than dumping the user back on the
     * summary screen mid-flow.
     */
    public function finishWizard()
    {
        session()->forget($this->progressSessionKey());

        return $this->redirect(route('settings.index'));
    }

    /**
     * Turn LDAP off from anywhere in the wizard. Preserves every other
     * setting (bind creds, base DN, mapping) so re-enabling later is a
     * matter of walking back through the wizard. Rendered as a small
     * banner at the top of the wizard when LDAP is currently on. The
     * wizard is otherwise a one-way enable path.
     */
    public function disableLdap()
    {
        if (config('app.lock_passwords')) {
            return null;
        }

        $setting = Setting::getSettings();
        $setting->ldap_enabled = '0';
        if (! $setting->save()) {
            $this->addError('save', trans('admin/settings/message.update.error'));

            return null;
        }

        session()->forget($this->progressSessionKey());
        session()->flash('success', trans('admin/settings/general.ldap_wizard.disabled_success'));

        return $this->redirect(route('settings.index'));
    }

    // === Shared helpers ======================================================

    /**
     * Check + hit the per-step rate limiter. Returns false when blocked,
     * having already called recordTestResult() with the rate-limit
     * message so the caller just needs to return.
     */
    protected function checkRateLimit(string $keyPrefix, string $actionType): bool
    {
        $key = $keyPrefix.':'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, self::TEST_RATE_LIMIT_ATTEMPTS)) {
            $wait = RateLimiter::availableIn($key);
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.test.rate_limited', ['seconds' => $wait]),
                $actionType,
            );

            return false;
        }
        RateLimiter::hit($key, self::TEST_RATE_LIMIT_DECAY_SECONDS);

        return true;
    }

    /**
     * Shared connect / set_option / start_tls sequence for the step 2
     * and step 3 network tests. Returns the LDAP connection on success
     * or null on any failure (with the appropriate error message
     * already recorded via recordTestResult, so the caller just needs
     * to return). Reads server + cert-ignore + tls flags off the
     * persisted Setting.
     */
    protected function openLdapConnectionForTest(string $actionType): ?\LDAP\Connection
    {
        $settings = Setting::getSettings();
        $server = (string) $settings->ldap_server;
        if ($settings->ldap_server_cert_ignore) {
            putenv('LDAPTLS_REQCERT=never');
        }

        // Client TLS cert/key MUST be set on the global (null-handle) LDAP
        // context BEFORE ldap_connect(), the same way Ldap::connectToLdap
        // does at runtime. Without this, mTLS-required directories (e.g.
        // Google Secure LDAP) reject the wizard's bind test with a
        // generic "Invalid credentials" even when everything the admin
        // entered is correct, and since saveStep2 gates persistence on the
        // test passing, the wizard becomes impossible to complete against
        // those servers. See #19519.
        if ($settings->ldap_client_tls_cert && $settings->ldap_client_tls_key) {
            ldap_set_option(null, LDAP_OPT_X_TLS_CERTFILE, Setting::get_client_side_cert_path());
            ldap_set_option(null, LDAP_OPT_X_TLS_KEYFILE, Setting::get_client_side_key_path());
        }

        $conn = @ldap_connect($server);
        if (! $conn) {
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.test.connect_failed', ['server' => $server]),
                $actionType,
            );

            return null;
        }

        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, self::TEST_NETWORK_TIMEOUT_SECONDS);

        if ($settings->ldap_tls && ! @ldap_start_tls($conn)) {
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.test.starttls_failed', ['error' => ldap_error($conn)]),
                $actionType,
            );
            @ldap_unbind($conn);

            return null;
        }

        return $conn;
    }

    /**
     * Bind $conn using the persisted (encrypted) bind username and
     * password. Used by step 3 which trusts step 2 has already
     * verified those credentials. Returns true on success, false
     * after recording the appropriate error and unbinding. Handles
     * password decrypt failure and bind rejection.
     */
    protected function bindWithPersistedCredentials(\LDAP\Connection $conn, string $actionType): bool
    {
        $settings = Setting::getSettings();
        $uname = (string) $settings->ldap_uname;
        try {
            $pword = $settings->ldap_pword ? Crypt::decrypt($settings->ldap_pword) : '';
        } catch (\Exception $e) {
            @ldap_unbind($conn);
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.search.pword_decrypt_failed'),
                $actionType,
            );

            return false;
        }

        $bindOk = $uname !== '' ? @ldap_bind($conn, $uname, $pword) : @ldap_bind($conn);
        if (! $bindOk) {
            $ldapError = Ldap::bindError($conn);
            @ldap_unbind($conn);
            $this->recordTestResult(
                'error',
                trans('admin/settings/general.ldap_wizard.search.bind_failed', ['error' => $ldapError]),
                $actionType,
            );

            return false;
        }

        return true;
    }

    /**
     * Persist $setting and advance to the next step. Extracted so
     * saveStep1 and saveStep2 share the tail. New step handlers just
     * populate their own fields on $setting and call through.
     */
    protected function persistAndAdvance(Setting $setting)
    {
        if (! $setting->save()) {
            $this->addError('save', trans('admin/settings/message.update.error'));

            return null;
        }

        $this->dirty = false;

        // Remember which step number we're leaving so the stepper CSS
        // animation can run on that step's dot (spinning yellow star
        // that morphs into the green checkmark). Cleared elsewhere so
        // manual back-nav via goToStep doesn't retrigger it.
        $this->justCompletedStep = $this->currentStep;

        // Step 4 is the final config step, but it advances INTO a
        // completion summary (step 5) instead of redirecting away.
        // The summary lists sync-scheduling options. The wizard only
        // enabled login, not the recurring user sync, so pointing the
        // admin at cron / Task Scheduler / manual `snipeit:ldap-sync`
        // right after enable is the useful hand-off.
        if ($this->currentStep === 4) {
            $this->highestStepReached = 5;
            session()->put($this->progressSessionKey(), 5);
            $this->currentStep = 5;
            $this->clearTestResult();
            $this->dispatch('wizard-step-changed');

            return null;
        }

        $this->highestStepReached = max($this->highestStepReached, $this->currentStep + 1);
        session()->put($this->progressSessionKey(), $this->highestStepReached);
        $this->currentStep++;
        $this->clearTestResult();
        $this->dispatch('wizard-step-changed');

        return null;
    }

    protected function serverPassesIpPolicy(string $url): ?string
    {
        $parsed = parse_url($url);
        if (! $parsed || empty($parsed['host'])) {
            return trans('validation.starts_with', [
                'attribute' => trans('admin/settings/general.ldap_server'),
                'values' => 'ldap://, ldaps://',
            ]);
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        if (! in_array($scheme, ['ldap', 'ldaps'], true)) {
            return trans('validation.starts_with', [
                'attribute' => trans('admin/settings/general.ldap_server'),
                'values' => 'ldap://, ldaps://',
            ]);
        }

        $host = trim($parsed['host'], '[]');
        $ip = gethostbyname($host);
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return trans('admin/settings/general.ldap_wizard.test.dns_failed', ['host' => $host]);
        }

        if (! config('app.test_allow_private_ips')) {
            // Block loopback, RFC-1918, link-local (169.254/16 -- cloud
            // metadata!), multicast, broadcast, and IPv6 transition
            // prefixes that would embed a non-public IPv4 payload. See
            // App\Helpers\PublicIpCheck for the exact ranges covered.
            // The check is gated on TEST_ALLOW_PRIVATE_IPS because some
            // installs legitimately need to talk to internal LDAP servers.
            if (! \App\Helpers\PublicIpCheck::isPublic($ip)) {
                return trans('admin/settings/general.ldap_wizard.test.private_ip_blocked', ['host' => $host, 'ip' => $ip]);
            }
        }

        return null;
    }

    protected function recordTestResult(string $status, string $message, string $actionType): void
    {
        $this->testStatus = $status;
        $this->testMessage = $message;
        $this->writeTestAuditLog($status, $message, $actionType);
    }

    protected function recordFieldError(string $field, string $message, string $actionType): void
    {
        $this->addError($field, $message);
        $this->writeTestAuditLog('error', $message, $actionType);
    }

    protected function writeTestAuditLog(string $status, string $message, string $actionType): void
    {
        // Wizard test runs write to the dedicated 'admin' log channel
        // (storage/logs/admin.log) rather than the action_logs table.
        // Test messages can echo LDAP server URLs, bind DNs, base DNs,
        // and error text that reflects user-controlled input. None of
        // that should be reachable from any in-app log viewer that
        // reads action_logs until we add the admin/settings logging system.
        // Keeping this in a file the server operator
        // controls is the right blast-radius for now. If we later want
        // an admin-only DB log we can add one without churning the
        // wizard.
        Log::channel('admin')->info('ldap_wizard.test', [
            'step' => $this->currentStep,
            'action_type' => $actionType,
            'status' => $status,
            'server' => $this->ldap_server,
            'user_id' => auth()->id(),
            'message' => $message,
        ]);
    }

    protected function clearTestResult(): void
    {
        $this->testStatus = null;
        $this->testMessage = null;
    }

    public function updated(string $property): void
    {
        // Read-only lock: in demo mode any mutation to a persisted
        // LDAP config field gets reverted to the seeded Setting value
        // before the rest of the updated() logic runs. Server-side
        // enforcement, so a client that fakes wire:model updates
        // around the UI's readonly / disabled attributes still can't
        // get modified creds into a Test Bind / Test Find User call.
        // test_sample_username stays writable so the Look Up preview
        // still works.
        if ($this->isReadOnly && ! in_array($property, self::READ_ONLY_ALLOWED_PROPS, true)) {
            $this->hydrateFromPersisted();

            return;
        }

        // Every string-typed prop that participates in the LDAP
        // handshake or a downstream test surface, grouped by wizard
        // step. Shared between the trim-on-assignment and the
        // clearTestResult() blocks below because both need the same
        // "user typed into a field the test depends on" signal.
        $connectionStringProps = [
            // Step 1: Connection
            'ldap_server',
            'ldap_client_tls_key',
            'ldap_client_tls_cert',
            // Step 2: Authenticate + Scope
            'ldap_uname',
            'ldap_pword',
            'ldap_basedn',
            'ldap_filter',
            'ldap_auth_filter_query',
            // Step 3: Attribute Mapping
            'ldap_username_field',
            'ldap_fname_field',
            'ldap_lname_field',
            'ldap_display_name',
            'ldap_email',
            'ldap_emp_num',
            'ldap_phone_field',
            'ldap_mobile',
            'ldap_jobtitle',
            'ldap_manager',
            'ldap_dept',
            'ldap_address',
            'ldap_city',
            'ldap_state',
            'ldap_zip',
            'ldap_country',
            'ldap_location',
            'ldap_active_flag',
            // Step 4: Sync + Defaults
            'custom_forgot_pass_url',
            // Not persisted; step-4 Look Up preview input
            'test_sample_username',
        ];

        // Trim string values on assignment so pasted-with-whitespace
        // inputs get normalized both in the visible field and in the
        // saved config. Without this a leading space on ldap_server
        // silently fails starts_with:ldap://, and a trailing newline in
        // a bind username produces a mysterious LDAP-side rejection at
        // auth time. Textareas (TLS key / cert) tolerate the trim
        // because PEM parsers accept both terminating-newline and
        // no-terminating-newline forms. ad_domain gets trimmed too but
        // does NOT invalidate the test below; it's not part of the
        // ldap_bind() handshake, only post-connection scoping.
        if (in_array($property, [...$connectionStringProps, 'ad_domain'], true)) {
            $this->{$property} = trim($this->{$property});
        }

        // Any edit to a connection-shape field invalidates the prior
        // network test result (a stale "connected" indicator sitting
        // under a since-edited server URL would be actively misleading).
        // Every connection-participating string prop plus the non-string
        // toggles / IDs that also affect the handshake or downstream
        // test surface.
        $testInvalidatingProps = [
            ...$connectionStringProps,
            'ldap_tls',
            'ldap_server_cert_ignore',
            'ldap_invert_active_flag',
            'ldap_enabled',
            'ldap_pw_sync',
            'ldap_default_group',
        ];

        if (in_array($property, $testInvalidatingProps, true)) {
            $this->clearTestResult();
            // Also clear the step-4 preview table since it references the
            // old values.
            $this->step3TestAttributes = [];
            $this->step3TestDn = '';
        }

        // Clear this field's inline validation error so re-editing the
        // field un-greys the Save button (which keys off canAdvance,
        // which checks the error bag).
        if (in_array($property, [
            'ldap_server',
            'ldap_client_tls_key',
            'ldap_client_tls_cert',
            'ad_domain',
            'ldap_uname',
            'ldap_pword',
            'ldap_basedn',
            'ldap_filter',
            'ldap_auth_filter_query',
            'ldap_username_field',
            'ldap_fname_field',
            'custom_forgot_pass_url',
            'test_sample_username'
        ], true)) {
            $this->resetValidation($property);
        }

        if (!in_array($property, [
            'currentStep',
            'highestStepReached',
            'dirty',
            'testStatus',
            'testMessage'
        ], true)) {
            $this->dirty = true;
        }
    }

    public function render()
    {
        return view('livewire.ldap-settings');
    }
}
