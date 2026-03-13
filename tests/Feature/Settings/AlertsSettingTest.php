<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;


class AlertsSettingTest extends TestCase
{
    public function testPermissionRequiredToViewAlertSettings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.alerts.index'))
            ->assertForbidden();
    }

    public function testAdminCCEmailArrayCanBeSaved()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.alerts.save', [
                'alert_email' => 'me@example.com,you@example.com',
                'admin_cc_always' => '1',
            ]))
            ->assertStatus(302)
            ->assertValid('alert_email')
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasNoErrors();
        $this->followRedirects($response)->assertSee('alert-success');
    }

    public function test_can_update_admin_cc_always_to_true()
    {
        $this->settings->disableAdminCCAlways();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.alerts.save', ['admin_cc_always' => '1']));

        $this->assertDatabaseHas('settings', ['admin_cc_always' => '1']);
    }

    public function test_can_update_admin_cc_always_to_false()
    {
        $this->settings->enableAdminCC()->enableAdminCCAlways();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.alerts.save', ['admin_cc_always' => '0']));

        $this->assertDatabaseHas('settings', ['admin_cc_always' => '0']);
    }
}
