<?php

namespace Tests\Feature\Authentication;

use App\Models\Company;
use App\Models\User;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('ldap')]
class LdapLoginCompanyTest extends TestCase
{
    use PHPMock;

    public function test_ldap_login_adds_company_from_mapped_ldap_attribute(): void
    {
        $this->settings->enableAnonymousLdap()->set([
            'ldap_username_field' => 'samaccountname',
            'ldap_fname_field' => 'givenname',
            'ldap_auth_filter_query' => 'samaccountname=',
            'ldap_company' => 'company',
        ]);
        $acme = Company::factory()->create(['name' => 'Acme']);
        $user = User::factory()->withoutCompany()->create(['username' => 'alice', 'activated' => 1]);
        $user->forceFill(['ldap_import' => 1])->saveQuietly();

        $this->getFunctionMock('App\\Models', 'ldap_connect')->expects($this->any())->willReturn('conn');
        $this->getFunctionMock('App\\Models', 'ldap_set_option')->expects($this->any())->willReturn(true);
        $this->getFunctionMock('App\\Models', 'ldap_bind')->expects($this->any())->willReturn(true);
        $this->getFunctionMock('App\\Models', 'ldap_search')->expects($this->any())->willReturn('res');
        $this->getFunctionMock('App\\Models', 'ldap_first_entry')->expects($this->any())->willReturn('entry');
        $this->getFunctionMock('App\\Models', 'ldap_get_attributes')->expects($this->any())->willReturn([
            'samaccountname' => ['count' => 1, 0 => 'alice'],
            'givenname' => ['count' => 1, 0 => 'Alice'],
            'company' => ['count' => 1, 0 => 'Acme'],
        ]);

        $this->post('/login', ['username' => 'alice', 'password' => 'secret']);

        $this->assertAuthenticatedAs($user);
        $this->assertSame([$acme->id], $user->companies()->pluck('companies.id')->all());
    }
}
