<?php

namespace Tests\Support;

use phpmock\Mock;
use PHPUnit\Framework\Attributes\BeforeClass;

trait MocksLdap
{
    /**
     * Define LDAP dispatch wrappers before PHP can cache a call to a native
     * function. Do not enable mocks here: each test supplies its own behavior,
     * and calls without an active mock still delegate to the native function.
     */
    #[BeforeClass]
    public static function defineLdapFunctionMocks(): void
    {
        foreach ([
            'ldap_bind',
            'ldap_connect',
            'ldap_count_entries',
            'ldap_error',
            'ldap_escape',
            'ldap_first_entry',
            'ldap_get_attributes',
            'ldap_get_dn',
            'ldap_get_entries',
            'ldap_get_option',
            'ldap_parse_result',
            'ldap_sasl_bind',
            'ldap_search',
            'ldap_set_option',
            'ldap_start_tls',
            'ldap_unbind',
        ] as $ldapFunction) {
            $mock = new Mock('App\\Models', $ldapFunction, static fn (): bool => false);
            $mock->define();
        }
    }
}
