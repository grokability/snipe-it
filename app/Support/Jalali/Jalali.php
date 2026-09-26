<?php

namespace App\Support\Jalali;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * The single Jalali (Persian/Hijri-Shamsi) conversion point for the app (Phase 1 spec §18).
 *
 * Pure PHP, no external dependency (self-contained standard algorithm, the same
 * integer method used by morilog/jalali and jdf). Canonical storage stays
 * Gregorian — this class is presentation-only.
 */
class Jalali
{
    public const MONTHS = [
        1 => 'Farvardin', 2 => 'Ordibehesht', 3 => 'Khordad',
        4 => 'Tir', 5 => 'Mordad', 6 => 'Shahrivar',
        7 => 'Mehr', 8 => 'Aban', 9 => 'Azar',
        10 => 'Dey', 11 => 'Bahman', 12 => 'Esfand',
    ];

    /** @var int[] days before each Gregorian month (non-leap) */
    private const G_DM = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    /**
     * Gregorian -> Jalali. Returns [year, month, day].
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400)
            + $gd + self::G_DM[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    /**
     * Jalali -> Gregorian. Returns [year, month, day].
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + (intdiv($jy, 33) * 8) + intdiv(($jy % 33) + 3, 4)
            + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $days--;
            $gy += 100 * intdiv($days, 36524);
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;
        $leap = (($gy % 4 === 0) && ($gy % 100 !== 0)) || ($gy % 400 === 0);
        $salA = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $gm = 0;
        while ($gm < 13 && $gd > $salA[$gm]) {
            $gd -= $salA[$gm];
            $gm++;
        }

        return [$gy, $gm, $gd];
    }

    public static function isLeapJalaliYear(int $jy): bool
    {
        // A Jalali year is leap when Farvardin 1 of the next year falls 366
        // days after Farvardin 1 of this year (Esfand then has 30 days).
        [$gy1, $gm1, $gd1] = self::toGregorian($jy + 1, 1, 1);
        [$gy0, $gm0, $gd0] = self::toGregorian($jy, 1, 1);

        $days = Carbon::create($gy1, $gm1, $gd1)->startOfDay()->diffInDays(Carbon::create($gy0, $gm0, $gd0)->startOfDay());

        return abs($days) === 366.0;
    }

    /**
     * Format a date in the Jalali calendar.
     *
     * Supported: Y (1405), y (05), m (07), n (7), d (01), j (1), F (Mehr), M (Mehr short = same).
     * Anything else passes through untouched.
     *
     * @param  Carbon|\DateTimeInterface|string  $date
     */
    public static function format($date, string $format = 'Y/m/d'): string
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        [$jy, $jm, $jd] = self::toJalali((int) $carbon->format('Y'), (int) $carbon->format('n'), (int) $carbon->format('j'));

        $replacements = [
            'Y' => (string) $jy,
            'y' => str_pad((string) ($jy % 100), 2, '0', STR_PAD_LEFT),
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'F' => self::MONTHS[$jm],
            'M' => mb_substr(self::MONTHS[$jm], 0, 3),
        ];

        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $char = $format[$i];
            $out .= array_key_exists($char, $replacements) ? $replacements[$char] : $char;
        }

        return $out;
    }

    /**
     * Convert a Jalali date string (e.g. 1405/07/01) to a Carbon instance.
     */
    public static function parse(string $jalaliDate, string $separator = '/'): Carbon
    {
        $parts = array_map('intval', explode($separator, trim($jalaliDate)));
        if (count($parts) !== 3 || $parts[1] < 1 || $parts[1] > 12 || $parts[2] < 1 || $parts[2] > 31) {
            throw new InvalidArgumentException("Invalid Jalali date: {$jalaliDate}");
        }

        [$gy, $gm, $gd] = self::toGregorian($parts[0], $parts[1], $parts[2]);

        return Carbon::create($gy, $gm, $gd);
    }
}
