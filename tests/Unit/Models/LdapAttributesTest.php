<?php

namespace Tests\Unit\Models;

use App\Models\Ldap;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre los metodos de Ldap que no requieren un servidor LDAP:
 * parseAndMapLdapAttributes, createUserFromLdap e ignoreCertificates.
 */
class LdapAttributesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());

        $this->settings->set([
            'ldap_username_field' => 'samaccountname',
            'ldap_fname_field' => 'givenname',
            'ldap_lname_field' => 'sn',
            'ldap_email' => 'mail',
            'ldap_emp_num' => 'employeenumber',
            'ldap_jobtitle' => 'title',
            'ldap_country' => 'c',
            'ldap_location' => 'l',
            'ldap_dept' => 'department',
            'ldap_manager' => 'manager',
        ]);
    }

    private function sampleAttributes(): array
    {
        return [
            'samaccountname' => ['jdoe'],
            'givenname' => ['Jane'],
            'sn' => ['Doe'],
            'mail' => ['jane.doe@example.com'],
            'employeenumber' => ['E-100'],
            'telephonenumber' => ['555-1234'],
            'title' => ['Engineer'],
            'c' => ['US'],
            'l' => ['HQ'],
            'department' => ['IT'],
            'manager' => ['cn=boss'],
        ];
    }

    public function test_parse_and_map_ldap_attributes(): void
    {
        $item = Ldap::parseAndMapLdapAttributes($this->sampleAttributes());

        $this->assertSame('jdoe', $item['username']);
        $this->assertSame('Jane', $item['firstname']);
        $this->assertSame('Doe', $item['lastname']);
        $this->assertSame('jane.doe@example.com', $item['email']);
        $this->assertSame('E-100', $item['employee_number']);
        $this->assertArrayHasKey('locale', $item);
    }

    public function test_parse_handles_missing_attributes(): void
    {
        // Atributos vacios -> todos los campos quedan en ''.
        $item = Ldap::parseAndMapLdapAttributes([]);

        $this->assertSame('', $item['username']);
        $this->assertSame('', $item['email']);
    }

    public function test_create_user_from_ldap(): void
    {
        $user = Ldap::createUserFromLdap($this->sampleAttributes(), 'secret123');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('jdoe', $user->username);
        $this->assertSame(1, $user->ldap_import);
        $this->assertDatabaseHas('users', ['username' => 'jdoe', 'ldap_import' => 1]);
    }

    public function test_create_user_from_ldap_with_password_sync(): void
    {
        $this->settings->set(['ldap_pw_sync' => '1']);

        $attrs = $this->sampleAttributes();
        $attrs['samaccountname'] = ['psyncuser'];

        $user = Ldap::createUserFromLdap($attrs, 'secret123');

        $this->assertNotEmpty($user->password);
    }

    public function test_create_user_from_ldap_returns_false_without_username(): void
    {
        $attrs = $this->sampleAttributes();
        $attrs['samaccountname'] = [''];

        $this->assertFalse(Ldap::createUserFromLdap($attrs, 'secret'));
    }

    public function test_ignore_certificates(): void
    {
        // Solo ajusta variables de entorno; no debe lanzar excepcion.
        Ldap::ignoreCertificates(true);
        Ldap::ignoreCertificates(false);
        $this->assertTrue(true);
    }
}
