<?php

namespace Tests\Feature\Settings;

use App\Livewire\LdapSettings;
use App\Models\Group;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feature coverage for the multi-step LDAP wizard (App\Livewire\LdapSettings).
 *
 * Buckets covered:
 *  - authorization (superadmin gate)
 *  - mount state hydration + password non-round-trip guarantee
 *  - URL/session-backed wizard nav clamping
 *  - per-step syntax validation
 *  - password reuse-persisted logic on step 2
 *  - SSRF filter on step 1 (private IP rejection)
 *  - rate limiting on the test-network path
 *  - TLS pair validation
 *  - step 4 business logic (ldap_enabled forced true, group exists rule)
 *  - disableLdap / finishWizard
 *  - audit log routing to the 'admin' log channel (not action_logs)
 *  - trim-on-updated for whitespace-padded values
 *
 * Not covered here (out of scope for the offline suite):
 *  - real LDAP protocol chatter (bind + search against a live directory)
 */
class LdapWizardTest extends TestCase
{
    private function actAsSuperuser(): User
    {
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);

        return $user;
    }

    private function ensureSetting(array $overrides = []): Setting
    {
        // Reset the Setting singleton cache. Setting::getSettings()
        // memoizes into a public static \$_cache, and any prior test
        // (or the framework boot) that touched Setting::first() before
        // this test's factory ran will otherwise hand back a stale row.
        Setting::$_cache = null;

        $setting = Setting::first() ?? Setting::factory()->create();

        // Setting's \$fillable is restrictive (site_name, email_domain,
        // and a handful of others). LDAP fields are NOT fillable, so
        // fill() / factory create() would silently drop them. forceFill
        // + save writes them directly.
        if ($overrides) {
            $setting->forceFill($overrides)->save();
            $setting = $setting->fresh();
        }

        // Prime the singleton cache with the row we just wrote so any
        // in-test call to Setting::getSettings() picks up our values.
        Setting::$_cache = $setting;

        return $setting;
    }

    // === Authorization =====================================================

    public function test_mount_forbids_non_superadmin(): void
    {
        $this->actingAs(User::factory()->create());
        $this->ensureSetting();

        // Livewire captures the abort(403) from mount() and surfaces it
        // as a 403 status on the testable rather than rethrowing the
        // HttpException. Match the wire test harness's shape, not the
        // raw exception class.
        Livewire::test(LdapSettings::class)
            ->assertStatus(403);
    }

    public function test_mount_allows_superadmin(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->assertStatus(200);
    }

    // === Mount / initial state =============================================

    public function test_mount_hydrates_persisted_settings_into_props(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_enabled' => 1,
            'ldap_server' => 'ldaps://ldap.example.com',
            'is_ad' => 1,
            'ad_domain' => 'example.com',
            'ldap_uname' => 'cn=admin,dc=example,dc=com',
            'ldap_basedn' => 'ou=users,dc=example,dc=com',
            'ldap_username_field' => 'samaccountname',
            'ldap_fname_field' => 'givenname',
        ]);

        Livewire::test(LdapSettings::class)
            ->assertSet('ldap_enabled', true)
            ->assertSet('ldap_server', 'ldaps://ldap.example.com')
            ->assertSet('is_ad', true)
            ->assertSet('ad_domain', 'example.com')
            ->assertSet('ldap_uname', 'cn=admin,dc=example,dc=com')
            ->assertSet('ldap_basedn', 'ou=users,dc=example,dc=com')
            ->assertSet('ldap_username_field', 'samaccountname')
            ->assertSet('ldap_fname_field', 'givenname');
    }

    public function test_mount_never_hydrates_persisted_password(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_pword' => Crypt::encrypt('supersecret'),
            'ldap_uname' => 'cn=admin,dc=example,dc=com',
        ]);

        // Even with an encrypted password on disk, the Livewire prop
        // must be empty so the plaintext never crosses the wire.
        Livewire::test(LdapSettings::class)
            ->assertSet('ldap_pword', '');
    }

    // === Wizard navigation =================================================

    public function test_go_to_step_forward_blocked_past_highest_reached(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->assertSet('currentStep', 1)
            ->call('goToStep', 3)
            ->assertSet('currentStep', 1);
    }

    public function test_go_to_step_backward_allowed(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 3)
            ->set('currentStep', 3)
            ->call('goToStep', 2)
            ->assertSet('currentStep', 2);
    }

    public function test_go_to_step_reverts_dirty_props_to_persisted(): void
    {
        $this->actAsSuperuser();
        // Persisted state has the mapping fields filled in.
        $this->ensureSetting([
            'ldap_username_field' => 'uid',
            'ldap_fname_field' => 'givenname',
            'ldap_email' => 'mail',
        ]);

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 3)
            ->set('currentStep', 3)
            // Simulate the user clearing fields (this flips dirty=true
            // via the updated() hook).
            ->set('ldap_email', '')
            ->set('ldap_fname_field', '')
            ->assertSet('dirty', true)
            // User clicks the stepper to jump to step 1. The confirm
            // dialog is client-side, so the server-side goToStep call
            // is what actually fires.
            ->call('goToStep', 1)
            ->assertSet('currentStep', 1)
            ->assertSet('dirty', false)
            // Cleared fields should be restored from the persisted row
            // so walking back forward and saving does not silently
            // overwrite them with empties.
            ->assertSet('ldap_email', 'mail')
            ->assertSet('ldap_fname_field', 'givenname')
            ->assertSet('ldap_username_field', 'uid');
    }

    public function test_url_step_param_clamps_to_highest_reached_on_mount(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        // Simulate ?step=4 with no session progress. Should clamp to 1.
        Livewire::withQueryParams(['step' => 4])
            ->test(LdapSettings::class)
            ->assertSet('currentStep', 1);
    }

    public function test_mount_lands_on_step_5_when_ldap_already_enabled(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting(['ldap_enabled' => 1]);

        // No URL step param. LDAP is on. Should land on the completion
        // screen (step 5) with the full wizard unlocked.
        Livewire::test(LdapSettings::class)
            ->assertSet('currentStep', 5)
            ->assertSet('highestStepReached', 5);
    }

    public function test_mount_honors_explicit_url_step_when_ldap_enabled(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting(['ldap_enabled' => 1]);

        // Explicit ?step=2 with ldap_enabled=1 should NOT get bumped up
        // to step 5. Return visitors need to be able to jump back to
        // earlier steps to edit config.
        Livewire::withQueryParams(['step' => 2])
            ->test(LdapSettings::class)
            ->assertSet('currentStep', 2)
            ->assertSet('highestStepReached', 5);
    }

    public function test_mount_starts_at_step_1_when_ldap_disabled(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting(['ldap_enabled' => 0]);

        Livewire::test(LdapSettings::class)
            ->assertSet('currentStep', 1)
            ->assertSet('highestStepReached', 1);
    }

    // === Step 1 syntax =====================================================

    public function test_step1_requires_ldap_server(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', '')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_server' => 'required']);
    }

    public function test_step1_ldap_server_must_start_with_ldap_scheme(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'https://example.com')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_server' => 'starts_with']);
    }

    public function test_step1_ad_domain_required_when_is_ad_checked(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://ldap.example.com')
            ->set('is_ad', true)
            ->set('ad_domain', '')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ad_domain']);
    }

    public function test_step1_tls_pair_xor_key_without_cert_blocks_advance(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://ldap.example.com')
            ->set('ldap_client_tls_key', "-----BEGIN PRIVATE KEY-----\nfake\n-----END PRIVATE KEY-----")
            ->set('ldap_client_tls_cert', '')
            // canAdvance false because of the XOR rule, so no click reaches
            // saveStep1 anyway. Assert via canAdvance directly.
            ->assertSet('canAdvance', false);
    }

    public function test_step1_tls_key_junk_pem_rejected(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://ldap.example.com')
            ->set('ldap_client_tls_key', 'not a real pem')
            ->set('ldap_client_tls_cert', 'also not a real pem')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_client_tls_key', 'ldap_client_tls_cert']);
    }

    // === Step 1 SSRF filter ================================================

    public function test_step1_rejects_localhost_as_private_ip(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();
        config(['app.test_allow_private_ips' => false]);

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://localhost')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_server']);
    }

    public function test_step1_rejects_rfc1918_address(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();
        config(['app.test_allow_private_ips' => false]);

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://192.168.1.10')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_server']);
    }

    public function test_step1_rejects_cloud_metadata_address(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();
        config(['app.test_allow_private_ips' => false]);

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://169.254.169.254')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_server']);
    }

    // === Step 1 rate limit =================================================

    public function test_step1_rate_limit_blocks_after_ten_attempts(): void
    {
        $user = $this->actAsSuperuser();
        $this->ensureSetting();

        // Hit the same rate-limit key the wizard uses so we exhaust the
        // budget without needing to spam real save calls.
        $key = 'ldap-test-step1:'.$user->id;
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($key, 60);
        }

        // The 11th test would fail rate-limit. Use a routable-looking
        // public host so the code reaches the rate-limit check (it
        // runs after IP policy for private-IP-safe servers, but
        // rate limit is checked BEFORE the LDAP connect).
        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://ldap.forumsys.com')
            ->call('saveAndAdvance')
            ->assertSet('testStatus', 'error');
    }

    // === Step 2 syntax + password reuse ====================================

    public function test_step2_requires_bind_username_basedn_and_auth_filter(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', '')
            ->set('ldap_pword', '')
            ->set('ldap_basedn', '')
            ->set('ldap_auth_filter_query', '')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_uname', 'ldap_pword', 'ldap_basedn', 'ldap_auth_filter_query']);
    }

    public function test_step2_password_optional_when_uname_unchanged_and_persisted(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_uname' => 'cn=admin,dc=example,dc=com',
            'ldap_pword' => Crypt::encrypt('persistedsecret'),
        ]);

        // Uname matches persisted, pword left blank on the form. The
        // password rule should not fire.
        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', 'cn=admin,dc=example,dc=com')
            ->set('ldap_pword', '')
            ->set('ldap_basedn', 'ou=users,dc=example,dc=com')
            ->set('ldap_auth_filter_query', 'uid=')
            ->call('saveAndAdvance')
            ->assertHasNoErrors(['ldap_pword']);
    }

    public function test_step2_password_required_when_uname_changed(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_uname' => 'cn=oldadmin,dc=example,dc=com',
            'ldap_pword' => Crypt::encrypt('persistedsecret'),
        ]);

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', 'cn=newadmin,dc=example,dc=com')
            ->set('ldap_pword', '')
            ->set('ldap_basedn', 'ou=users,dc=example,dc=com')
            ->set('ldap_auth_filter_query', 'uid=')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_pword']);
    }

    public function test_step2_base_dn_equal_to_bind_dn_rejected(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', 'cn=read-only-admin,dc=example,dc=com')
            ->set('ldap_pword', 'anything')
            ->set('ldap_basedn', 'cn=read-only-admin,dc=example,dc=com')
            ->set('ldap_auth_filter_query', 'uid=')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_basedn']);
    }

    public function test_step2_base_dn_equal_to_bind_dn_normalizes_case_and_whitespace(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        // Different case + varying whitespace around commas should still
        // trip the guard, since directories are case-insensitive on DN
        // comparisons and comma-adjacent whitespace is not significant.
        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', 'cn=admin,dc=example,dc=com')
            ->set('ldap_pword', 'anything')
            ->set('ldap_basedn', 'CN=Admin, DC=Example, DC=Com')
            ->set('ldap_auth_filter_query', 'uid=')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_basedn']);
    }

    public function test_step2_base_dn_that_is_a_parent_of_bind_dn_is_allowed(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_uname' => 'cn=admin,dc=example,dc=com',
            'ldap_pword' => Crypt::encrypt('persistedsecret'),
        ]);

        // The normal, correct relationship: bind DN lives under the base
        // DN. Should pass the base-DN-equals-bind-DN closure.
        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', 'cn=admin,dc=example,dc=com')
            ->set('ldap_pword', '')
            ->set('ldap_basedn', 'dc=example,dc=com')
            ->set('ldap_auth_filter_query', 'uid=')
            ->call('saveAndAdvance')
            ->assertHasNoErrors(['ldap_basedn']);
    }

    public function test_step2_filter_with_leading_paren_rejected(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_uname' => 'cn=admin,dc=example,dc=com',
            'ldap_pword' => Crypt::encrypt('persistedsecret'),
        ]);

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 2)
            ->set('currentStep', 2)
            ->set('ldap_uname', 'cn=admin,dc=example,dc=com')
            ->set('ldap_pword', '')
            ->set('ldap_basedn', 'ou=users,dc=example,dc=com')
            ->set('ldap_filter', '(cn=*)')
            ->set('ldap_auth_filter_query', 'uid=')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_filter']);
    }

    // === Step 3 syntax =====================================================

    public function test_step3_requires_username_and_first_name_mapping(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 3)
            ->set('currentStep', 3)
            ->set('ldap_username_field', '')
            ->set('ldap_fname_field', '')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_username_field', 'ldap_fname_field']);
    }

    public function test_step3_username_field_rejects_camelcase_samaccountname(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 3)
            ->set('currentStep', 3)
            ->set('ldap_username_field', 'sAMAccountName')
            ->set('ldap_fname_field', 'givenname')
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_username_field']);
    }

    // === Step 4 business logic =============================================

    public function test_step4_forces_ldap_enabled_true_on_save(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting(['ldap_enabled' => 0]);

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 4)
            ->set('currentStep', 4)
            ->set('ldap_default_group', null)
            ->set('custom_forgot_pass_url', '')
            ->call('saveAndAdvance');

        $this->assertSame('1', Setting::getSettings()->ldap_enabled);
    }

    public function test_step4_advances_to_completion_step_after_save(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 4)
            ->set('currentStep', 4)
            ->set('ldap_default_group', null)
            ->call('saveAndAdvance')
            ->assertSet('currentStep', 5)
            ->assertSet('highestStepReached', 5);
    }

    public function test_step4_default_group_must_exist_in_permission_groups(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 4)
            ->set('currentStep', 4)
            ->set('ldap_default_group', 999_999)
            ->call('saveAndAdvance')
            ->assertHasErrors(['ldap_default_group']);
    }

    public function test_step4_default_group_accepts_real_group_id(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();
        $group = Group::factory()->create();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 4)
            ->set('currentStep', 4)
            ->set('ldap_default_group', $group->id)
            ->call('saveAndAdvance')
            ->assertHasNoErrors(['ldap_default_group']);

        $this->assertSame($group->id, (int) Setting::getSettings()->ldap_default_group);
    }

    public function test_step4_custom_forgot_pass_url_must_be_valid_url(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('highestStepReached', 4)
            ->set('currentStep', 4)
            ->set('custom_forgot_pass_url', 'not a url')
            ->call('saveAndAdvance')
            ->assertHasErrors(['custom_forgot_pass_url']);
    }

    // === disableLdap =======================================================

    public function test_disable_ldap_flips_flag_and_redirects(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting([
            'ldap_enabled' => 1,
            'ldap_server' => 'ldaps://ldap.example.com',
            'ldap_uname' => 'cn=admin,dc=example,dc=com',
        ]);

        Livewire::test(LdapSettings::class)
            ->call('disableLdap')
            ->assertRedirect(route('settings.index'));

        $fresh = Setting::getSettings();
        $this->assertSame('0', (string) $fresh->ldap_enabled);
        // Other settings preserved so the wizard can re-enable later
        // without the user re-entering everything.
        $this->assertSame('ldaps://ldap.example.com', $fresh->ldap_server);
        $this->assertSame('cn=admin,dc=example,dc=com', $fresh->ldap_uname);
    }

    public function test_disable_ldap_clears_wizard_progress_session_key(): void
    {
        $user = $this->actAsSuperuser();
        $this->ensureSetting(['ldap_enabled' => 1]);

        $key = 'ldap_wizard_highest_step:'.$user->id;
        session()->put($key, 4);

        Livewire::test(LdapSettings::class)
            ->call('disableLdap');

        $this->assertFalse(session()->has($key));
    }

    // === finishWizard ======================================================

    public function test_finish_wizard_clears_progress_and_redirects(): void
    {
        $user = $this->actAsSuperuser();
        $this->ensureSetting();

        $key = 'ldap_wizard_highest_step:'.$user->id;
        session()->put($key, 5);

        Livewire::test(LdapSettings::class)
            ->call('finishWizard')
            ->assertRedirect(route('settings.index'));

        $this->assertFalse(session()->has($key));
    }

    // === Audit log routing =================================================

    public function test_test_run_writes_to_admin_log_channel_not_action_logs(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();
        config(['app.test_allow_private_ips' => false]);

        // Spy the admin channel so we can assert against calls without
        // suppressing them entirely (info() would still hit file otherwise).
        Log::shouldReceive('channel')->with('admin')->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->with('ldap_wizard.test', \Mockery::on(function ($context) {
                return isset($context['step'], $context['action_type'], $context['status'], $context['server'], $context['user_id'], $context['message']);
            }));

        // Trigger a step-1 test that will fail on the SSRF gate, which
        // still routes through recordFieldError -> writeTestAuditLog.
        Livewire::test(LdapSettings::class)
            ->set('ldap_server', 'ldap://localhost')
            ->call('saveAndAdvance');

        // Belt-and-suspenders: nothing should have landed in action_logs
        // for this wizard action (only rows that pre-exist from factories
        // would be here). Assert the action_type wasn't written.
        $this->assertDatabaseMissing('action_logs', [
            'action_type' => 'ldap connection test',
        ]);
    }

    // === Trimming ==========================================================

    public function test_updated_trims_whitespace_padded_values(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->set('ldap_server', '  ldap://ldap.example.com  ')
            ->assertSet('ldap_server', 'ldap://ldap.example.com')
            ->set('ldap_uname', "\tcn=admin,dc=example,dc=com\n")
            ->assertSet('ldap_uname', 'cn=admin,dc=example,dc=com');
    }

    public function test_updated_marks_wizard_dirty(): void
    {
        $this->actAsSuperuser();
        $this->ensureSetting();

        Livewire::test(LdapSettings::class)
            ->assertSet('dirty', false)
            ->set('ldap_server', 'ldap://ldap.example.com')
            ->assertSet('dirty', true);
    }
}
