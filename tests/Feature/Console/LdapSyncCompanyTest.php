<?php

namespace Tests\Feature\Console;

use App\Models\Company;
use App\Models\User;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('ldap')]
class LdapSyncCompanyTest extends TestCase
{
    use PHPMock;

    public function test_sync_adds_company_from_mapped_ldap_attribute(): void
    {
        $this->settings->enableLdap()->set([
            'ldap_username_field' => 'samaccountname',
            'ldap_fname_field' => 'givenname',
            'ldap_company' => 'company',
        ]);
        $acme = Company::factory()->create(['name' => 'Acme']);

        $this->getFunctionMock('App\\Models', 'ldap_connect')->expects($this->any())->willReturn('conn');
        $this->getFunctionMock('App\\Models', 'ldap_set_option')->expects($this->any())->willReturn(true);
        $this->getFunctionMock('App\\Models', 'ldap_bind')->expects($this->any())->willReturn(true);
        $this->getFunctionMock('App\\Models', 'ldap_search')->expects($this->any())->willReturn('res');
        $this->getFunctionMock('App\\Models', 'ldap_parse_result')->expects($this->any())->willReturn(true);
        $this->getFunctionMock('App\\Models', 'ldap_get_entries')->expects($this->any())->willReturn([
            'count' => 1,
            [
                'samaccountname' => ['count' => 1, 0 => 'alice'],
                'givenname' => ['count' => 1, 0 => 'Alice'],
                'company' => ['count' => 1, 0 => 'ACME'],
            ],
        ]);

        $this->artisan('snipeit:ldap-sync', ['--json_summary' => true])->assertSuccessful();

        $alice = User::where('username', 'alice')->firstOrFail();
        $this->assertSame([$acme->id], $alice->companies()->pluck('companies.id')->all());
    }
}
