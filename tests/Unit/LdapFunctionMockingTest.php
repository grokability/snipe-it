<?php

namespace Tests\Unit;

use App\Models\Ldap;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

#[Group('ldap')]
class LdapFunctionMockingTest extends TestCase
{
    use PHPMock;

    /**
     * A fresh process ensures earlier mocks cannot hide PHP's cached
     * namespace fallback when the first call happens before a mock is enabled.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_ldap_error_can_be_mocked_after_an_unmocked_call(): void
    {
        if (! extension_loaded('ldap')) {
            $this->markTestSkipped('The LDAP extension is required to exercise native function fallback.');
        }

        $connection = \ldap_connect('ldap://localhost');
        Ldap::bindError($connection);

        $this->getFunctionMock('App\\Models', 'ldap_get_option')
            ->expects($this->once())->willReturn(true);
        $this->getFunctionMock('App\\Models', 'ldap_error')
            ->expects($this->once())->willReturn('Mocked LDAP error');

        $error = Ldap::bindError('dummy');

        $this->assertSame('Mocked LDAP error', $error);
    }
}
