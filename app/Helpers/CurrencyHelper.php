<?php

namespace App\Helpers;

/**
 * ISO 4217 currency code list and symbol lookups.
 *
 * Codes are locale-invariant and live here, not in lang files. Only
 * widely-recognized symbols are mapped. Codes without a known symbol
 * return null and callers should fall back to the ISO code itself
 * for display.
 */
class CurrencyHelper
{
    /**
     * Every active ISO 4217 code the dropdown offers. Includes codes
     * that have no widely-recognized symbol. The symbol lookup falls
     * back to the code itself for those.
     *
     * @var array<int, string>
     */
    private const CODES = [
        'AED',
        'AFN',
        'ALL',
        'AMD',
        'ANG',
        'AOA',
        'ARS',
        'AUD',
        'AWG',
        'AZN',
        'BAM',
        'BBD',
        'BDT',
        'BGN',
        'BHD',
        'BIF',
        'BMD',
        'BND',
        'BOB',
        'BRL',
        'BSD',
        'BTN',
        'BWP',
        'BYN',
        'BZD',
        'CAD',
        'CDF',
        'CHF',
        'CLP',
        'CNY',
        'COP',
        'CRC',
        'CUC',
        'CUP',
        'CVE',
        'CZK',
        'DJF',
        'DKK',
        'DOP',
        'DZD',
        'EGP',
        'ERN',
        'ETB',
        'EUR',
        'FJD',
        'FKP',
        'GBP',
        'GEL',
        'GHS',
        'GIP',
        'GMD',
        'GNF',
        'GTQ',
        'GYD',
        'HKD',
        'HNL',
        'HTG',
        'HUF',
        'IDR',
        'ILS',
        'INR',
        'IQD',
        'IRR',
        'ISK',
        'JMD',
        'JOD',
        'JPY',
        'KES',
        'KGS',
        'KHR',
        'KMF',
        'KPW',
        'KRW',
        'KWD',
        'KYD',
        'KZT',
        'LAK',
        'LBP',
        'LKR',
        'LRD',
        'LSL',
        'LYD',
        'MAD',
        'MDL',
        'MGA',
        'MKD',
        'MMK',
        'MNT',
        'MOP',
        'MRU',
        'MUR',
        'MVR',
        'MWK',
        'MXN',
        'MYR',
        'MZN',
        'NAD',
        'NGN',
        'NIO',
        'NOK',
        'NPR',
        'NZD',
        'OMR',
        'PAB',
        'PEN',
        'PGK',
        'PHP',
        'PKR',
        'PLN',
        'PYG',
        'QAR',
        'RON',
        'RSD',
        'RUB',
        'RWF',
        'SAR',
        'SBD',
        'SCR',
        'SDG',
        'SEK',
        'SGD',
        'SHP',
        'SLE',
        'SOS',
        'SRD',
        'SSP',
        'STN',
        'SYP',
        'SZL',
        'THB',
        'TJS',
        'TMT',
        'TND',
        'TOP',
        'TRY',
        'TTD',
        'TWD',
        'TZS',
        'UAH',
        'UGX',
        'USD',
        'UYU',
        'UZS',
        'VES',
        'VND',
        'VUV',
        'WST',
        'XAF',
        'XCD',
        'XOF',
        'XPF',
        'YER',
        'ZAR',
        'ZMW',
        'ZWL',
    ];

    /**
     * Code → symbol map. Only widely-recognized single-character or
     * short symbols are included. Obscure codes return null and
     * callers should fall back to the ISO code in display.
     *
     * @var array<string, string>
     */
    private const SYMBOLS = [
        'AED' => 'د.إ',
        'AFN' => '؋',
        'ALL' => 'L',
        'AMD' => '֏',
        'ANG' => 'ƒ',
        'ARS' => '$',
        'AUD' => 'A$',
        'AWG' => 'ƒ',
        'AZN' => '₼',
        'BAM' => 'KM',
        'BBD' => 'Bds$',
        'BDT' => '৳',
        'BGN' => 'лв',
        'BHD' => '.د.ب',
        'BMD' => 'BD$',
        'BND' => 'B$',
        'BOB' => 'Bs.',
        'BRL' => 'R$',
        'BSD' => 'B$',
        'BTN' => 'Nu.',
        'BWP' => 'P',
        'BYN' => 'Br',
        'BZD' => 'BZ$',
        'CAD' => 'C$',
        'CHF' => 'CHF',
        'CLP' => '$',
        'CNY' => '¥',
        'COP' => '$',
        'CRC' => '₡',
        'CUC' => '$',
        'CUP' => '₱',
        'CVE' => '$',
        'CZK' => 'Kč',
        'DKK' => 'kr',
        'DOP' => 'RD$',
        'DZD' => 'د.ج',
        'EGP' => '£',
        'ERN' => 'Nfk',
        'ETB' => 'Br',
        'EUR' => '€',
        'FJD' => 'FJ$',
        'FKP' => '£',
        'GBP' => '£',
        'GEL' => '₾',
        'GHS' => '₵',
        'GIP' => '£',
        'GMD' => 'D',
        'GTQ' => 'Q',
        'GYD' => 'G$',
        'HKD' => 'HK$',
        'HNL' => 'L',
        'HTG' => 'G',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '₪',
        'INR' => '₹',
        'IQD' => 'ع.د',
        'IRR' => '﷼',
        'ISK' => 'kr',
        'JMD' => 'J$',
        'JOD' => 'د.ا',
        'JPY' => '¥',
        'KES' => 'KSh',
        'KGS' => 'с',
        'KHR' => '៛',
        'KPW' => '₩',
        'KRW' => '₩',
        'KWD' => 'د.ك',
        'KYD' => 'CI$',
        'KZT' => '₸',
        'LAK' => '₭',
        'LBP' => 'ل.ل',
        'LKR' => 'Rs',
        'LRD' => 'L$',
        'LSL' => 'L',
        'LYD' => 'ل.د',
        'MAD' => 'د.م.',
        'MDL' => 'L',
        'MKD' => 'ден',
        'MMK' => 'K',
        'MNT' => '₮',
        'MOP' => 'MOP$',
        'MUR' => '₨',
        'MVR' => 'Rf',
        'MWK' => 'MK',
        'MXN' => '$',
        'MYR' => 'RM',
        'MZN' => 'MT',
        'NAD' => 'N$',
        'NGN' => '₦',
        'NIO' => 'C$',
        'NOK' => 'kr',
        'NPR' => '₨',
        'NZD' => 'NZ$',
        'OMR' => 'ر.ع.',
        'PAB' => 'B/.',
        'PEN' => 'S/',
        'PGK' => 'K',
        'PHP' => '₱',
        'PKR' => '₨',
        'PLN' => 'zł',
        'PYG' => '₲',
        'QAR' => 'ر.ق',
        'RON' => 'lei',
        'RSD' => 'дин.',
        'RUB' => '₽',
        'SAR' => 'ر.س',
        'SBD' => 'SI$',
        'SCR' => '₨',
        'SDG' => 'ج.س.',
        'SEK' => 'kr',
        'SGD' => 'S$',
        'SHP' => '£',
        'SOS' => 'Sh',
        'SRD' => 'Sr$',
        'SSP' => 'SS£',
        'SYP' => '£S',
        'SZL' => 'E',
        'THB' => '฿',
        'TJS' => 'ЅМ',
        'TMT' => 'm',
        'TND' => 'د.ت',
        'TOP' => 'T$',
        'TRY' => '₺',
        'TTD' => 'TT$',
        'TWD' => 'NT$',
        'TZS' => 'TSh',
        'UAH' => '₴',
        'UGX' => 'USh',
        'USD' => '$',
        'UYU' => '$U',
        'UZS' => "so'm",
        'VES' => 'Bs.S',
        'VND' => '₫',
        'XAF' => 'FCFA',
        'XCD' => 'EC$',
        'XOF' => 'CFA',
        'XPF' => '₣',
        'YER' => '﷼',
        'ZAR' => 'R',
        'ZMW' => 'ZK',
    ];

    /**
     * Every ISO 4217 code the picker offers, in alphabetical order.
     *
     * @return array<int, string>
     */
    public static function codes(): array
    {
        return self::CODES;
    }

    /**
     * Resolve a currency code's symbol, or null when there's no widely-
     * recognized symbol for that code. Codes are matched case-
     * insensitively against the standard three-letter ISO 4217 form so
     * stored lowercase values still resolve.
     */
    public static function symbol(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return self::SYMBOLS[strtoupper($code)] ?? null;
    }

    /**
     * Shortest display form for a currency. Returns its symbol when
     * available, otherwise the ISO code itself. Useful for inline
     * cost strings where a column of "$" aligns better than "USD".
     */
    public static function displayLabel(?string $code): string
    {
        $code = $code === null ? '' : trim($code);
        if ($code === '') {
            return '';
        }

        return self::symbol($code) ?? strtoupper($code);
    }
}
