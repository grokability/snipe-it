<?php

namespace Tests\Unit\Documents;

use App\Support\Jalali\Jalali;
use PHPUnit\Framework\TestCase;

class JalaliTest extends TestCase
{
    /**
     * Known vectors: Nowruz 2026 and today's session anchor date.
     */
    public function test_known_gregorian_to_jalali_vectors(): void
    {
        $this->assertSame([1405, 1, 1], Jalali::toJalali(2026, 3, 21));
        $this->assertSame([1405, 7, 1], Jalali::toJalali(2026, 9, 23));
        $this->assertSame([1404, 12, 29], Jalali::toJalali(2026, 3, 20));
        $this->assertSame([1403, 1, 1], Jalali::toJalali(2024, 3, 20));
        $this->assertSame([1403, 12, 30], Jalali::toJalali(2025, 3, 20)); // leap day: Esfand 30, 1403
    }

    public function test_jalali_to_gregorian_vectors(): void
    {
        $this->assertSame([2026, 3, 21], Jalali::toGregorian(1405, 1, 1));
        $this->assertSame([2026, 9, 23], Jalali::toGregorian(1405, 7, 1));
    }

    public function test_round_trip_across_leap_boundary(): void
    {
        foreach ([1403, 1404, 1405, 1406] as $jy) {
            for ($jm = 1; $jm <= 12; $jm++) {
                // Last valid day: 31 for months 1-6, 30 for 7-11, 29 for Esfand
                // (30 only exists in leap years — covered by the leap vector test)
                $jd = ($jm < 7) ? 31 : (($jm < 12) ? 30 : 29);
                [$gy, $gm, $gd] = Jalali::toGregorian($jy, $jm, $jd);
                $this->assertSame(
                    [$jy, $jm, $jd],
                    Jalali::toJalali($gy, $gm, $gd),
                    "Round trip failed for {$jy}-{$jm}-{$jd}"
                );
            }
        }
    }

    public function test_leap_year_detection(): void
    {
        $this->assertTrue(Jalali::isLeapJalaliYear(1403));  // 1403 is a leap Jalali year
        $this->assertFalse(Jalali::isLeapJalaliYear(1404));
        $this->assertFalse(Jalali::isLeapJalaliYear(1405));
    }

    public function test_format_uses_jalali_fields(): void
    {
        $this->assertSame('1405/07/01', Jalali::format('2026-09-23'));
        $this->assertSame('1405', Jalali::format('2026-09-23', 'Y'));
        $this->assertSame('Mehr 1405', Jalali::format('2026-09-23', 'F Y'));
        $this->assertSame('14050701', Jalali::format('2026-09-23', 'Ymd'));
    }

    public function test_parse_returns_matching_gregorian(): void
    {
        $this->assertSame('2026-09-23', Jalali::parse('1405/07/01')->format('Y-m-d'));
    }

    public function test_parse_rejects_invalid_input(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Jalali::parse('1405/13/01');
    }
}
