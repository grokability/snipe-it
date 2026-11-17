<?php

namespace Tests\Feature\Users;

use App\Console\Commands\LdapSync;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LdapSyncMutexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::store('file')->forget(LdapSync::LDAP_SYNC_LOCK);
    }

    protected function tearDown(): void
    {
        Cache::store('file')->forget(LdapSync::LDAP_SYNC_LOCK);
        parent::tearDown();
    }

    public function test_cli_sync_is_rejected_when_the_lock_is_already_held(): void
    {
        $this->settings->set(['ldap_enabled' => '1']);
        Cache::store('file')->put(LdapSync::LDAP_SYNC_LOCK, true, now()->addMinutes(30));

        $this->artisan('snipeit:ldap-sync')
            ->expectsOutput(trans('general.operation_already_running', ['operation' => trans('general.ldap_sync')]))
            ->assertExitCode(1);
    }

    public function test_cli_sync_in_json_mode_returns_already_running_as_json_payload(): void
    {
        $this->settings->set(['ldap_enabled' => '1']);
        Cache::store('file')->put(LdapSync::LDAP_SYNC_LOCK, true, now()->addMinutes(30));

        $exitCode = Artisan::call('snipeit:ldap-sync', ['--json_summary' => true]);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $decoded = json_decode(trim($output), true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['error']);
        $this->assertSame(
            trans('general.operation_already_running', ['operation' => trans('general.ldap_sync')]),
            $decoded['error_message']
        );
        $this->assertSame([], $decoded['summary']);
    }

    public function test_ui_sync_page_exposes_running_state_to_the_view(): void
    {
        $this->settings->set(['ldap_enabled' => '1']);
        Cache::store('file')->put(LdapSync::LDAP_SYNC_LOCK, true, now()->addMinutes(30));

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('ldap/user'))
            ->assertOk()
            ->assertViewHas('ldapSyncRunning', true)
            ->assertSee(trans('general.operation_already_running', ['operation' => trans('general.ldap_sync')]));
    }

    public function test_ui_sync_page_reports_running_false_when_the_lock_is_absent(): void
    {
        $this->settings->set(['ldap_enabled' => '1']);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('ldap/user'))
            ->assertOk()
            ->assertViewHas('ldapSyncRunning', false);
    }
    
    public function test_bind_failure_returns_failure_exit_code(): void
    {
        $this->settings->set([
            'ldap_enabled' => '1',
            'ldap_server' => '',
        ]);

        $exitCode = Artisan::call('snipeit:ldap-sync');

        $this->assertSame(1, $exitCode, 'A bind-side failure must bubble up as exit code 1 rather than coerce to 0.');
    }
}
