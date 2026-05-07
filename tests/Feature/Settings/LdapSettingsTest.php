<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Tests\TestCase;

class LdapSettingsTest extends TestCase
{
    public function test_permission_required_to_view_ldap_settings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.ldap.index'))
            ->assertForbidden();
    }

    public function test_ldap_settings_can_be_saved()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.ldap.save', [
                'ldap_enabled' => 1,
                'ldap_username_field' => 'samaccountname',
                'ldap_filter' => 'uid=',
                'ldap_auth_filter_query' => 'uid=',
                'ldap_uname' => 'SomeUserField',
                'ldap_pword' => 'MyAwesomePassword',
                'ldap_basedn' => 'uid=',
                'ldap_fname_field' => 'SomeFirstnameField',
                'ldap_server' => 'ldaps://ldap.example.com',
                'ldap_invert_active_flag' => 0,
            ]))
            ->assertStatus(302)
            ->assertValid('ldap_enabled')
            ->assertRedirect(route('settings.ldap.index'))
            ->assertSessionHasNoErrors();
        $this->followRedirects($response)->assertSee('alert-success');
    }

    public function test_ldap_settings_are_validated_correctly()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.ldap.index'))
            ->post(route('settings.ldap.save', [
                'ldap_enabled' => 1,
                'ldap_username_field' => 'sAMAccountName',
                'ldap_filter' => '(uid=)',
            ]))
            ->assertStatus(302)
            ->assertRedirect(route('settings.ldap.index'))
            ->assertSessionHasErrors([
                'ldap_username_field',
                'ldap_auth_filter_query',
                'ldap_basedn',
                'ldap_fname_field',
                'ldap_server',
            ]);
        $this->followRedirects($response)->assertSee('alert-danger');
    }
}
