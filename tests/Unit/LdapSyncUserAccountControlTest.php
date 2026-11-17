<?php

namespace Tests\Unit;

use App\Console\Commands\LdapSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regression and conformance coverage for the Active Directory
 * userAccountControl → activated translation (#19999). The old code
 * used a hardcoded whitelist of 12 integer values which silently
 * deactivated legitimate users whose flag combination fell outside
 * that list. The current implementation does a bitwise check per
 * Microsoft's documented semantics.
 *
 * Reference flag bits:
 *   NORMAL_ACCOUNT          0x200    (= 512)
 *   ACCOUNTDISABLED         0x2      (= 2)
 *   LOCKOUT                 0x10     (= 16)
 *   PASSWD_NOTREQD          0x20
 *   SMARTCARD_REQUIRED      0x40000  (= 262144)
 *   DONT_EXPIRE_PASSWORD    0x10000  (= 65536)
 *   NOT_DELEGATED           0x100000 (= 1048576)
 *   DONT_REQ_PREAUTH        0x400000 (= 4194304)
 */
class LdapSyncUserAccountControlTest extends TestCase
{
    public static function activatedCases(): array
    {
        return [
            'plain NORMAL_ACCOUNT (0x200)' => [0x200, true],
            'NORMAL_ACCOUNT | DONT_EXPIRE_PASSWORD' => [0x10200, true],
            'NORMAL_ACCOUNT | SMARTCARD_REQUIRED' => [0x40200, true],
            'NORMAL_ACCOUNT | NOT_DELEGATED' => [0x100200, true],
            'issue #19999: NORMAL_ACCOUNT | SMARTCARD_REQUIRED | NOT_DELEGATED' => [0x140200, true],
            'NORMAL_ACCOUNT | DONT_EXPIRE_PASSWORD | NOT_DELEGATED | SMARTCARD_REQUIRED' => [0x150200, true],
            'NORMAL_ACCOUNT | PASSWD_NOTREQD' => [0x220, true],
        ];
    }

    public static function deactivatedCases(): array
    {
        return [
            'zero (no bits set at all)' => [0, false],
            'ACCOUNTDISABLED alone' => [0x2, false],
            'NORMAL_ACCOUNT | ACCOUNTDISABLED' => [0x202, false],
            'NORMAL_ACCOUNT | DONT_EXPIRE_PASSWORD | ACCOUNTDISABLED' => [0x10202, false],
            'NORMAL_ACCOUNT | LOCKOUT' => [0x210, false],
            'NORMAL_ACCOUNT | ACCOUNTDISABLED | LOCKOUT' => [0x212, false],
            // Service accounts (WORKSTATION_TRUST_ACCOUNT = 0x1000) are
            // not NORMAL_ACCOUNT and therefore not activated as users.
            'WORKSTATION_TRUST_ACCOUNT (service account, not a user)' => [0x1000, false],
            // SMARTCARD_REQUIRED alone without NORMAL_ACCOUNT is not a
            // normal user either and must not activate.
            'SMARTCARD_REQUIRED without NORMAL_ACCOUNT' => [0x40000, false],
        ];
    }

    #[DataProvider('activatedCases')]
    public function test_activated_cases(int $uac, bool $expected): void
    {
        $this->assertSame($expected, LdapSync::isActivatedByUserAccountControl($uac));
    }

    #[DataProvider('deactivatedCases')]
    public function test_deactivated_cases(int $uac, bool $expected): void
    {
        $this->assertSame($expected, LdapSync::isActivatedByUserAccountControl($uac));
    }
}
