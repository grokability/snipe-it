<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelperMethodsTest extends TestCase
{
    public function test_parse_float(): void
    {
        $this->assertEquals(1234.56, Helper::ParseFloat('1,234.56'));
    }

    public function test_parse_currency_strips_thousands(): void
    {
        $this->assertEquals(1234.56, Helper::ParseCurrency('1,234.56'));
    }

    public function test_make_slug(): void
    {
        $this->assertEquals('hello_world', Helper::make_slug('hello world'));
    }

    public function test_array_smart_fetch_trims_value(): void
    {
        $this->assertEquals('bob', Helper::array_smart_fetch(['name' => '  bob '], 'name'));
        $this->assertEquals('default', Helper::array_smart_fetch([], 'missing', 'default'));
    }

    public function test_format_standard_api_response(): void
    {
        $resp = Helper::formatStandardApiResponse('success', ['id' => 1], 'ok');

        $this->assertEquals('success', $resp['status']);
        $this->assertEquals(['id' => 1], $resp['payload']);
        $this->assertEquals('ok', $resp['messages']);
    }

    public function test_generate_random_string_length(): void
    {
        $this->assertEquals(16, strlen(Helper::generateRandomString(16)));
    }

    public function test_generate_unencrypted_password_is_string(): void
    {
        $this->assertNotEmpty(Helper::generateUnencryptedPassword());
    }

    public function test_default_chart_colors_returns_color(): void
    {
        $this->assertIsString(Helper::defaultChartColors(0));
        $this->assertIsString(Helper::defaultChartColors(99));
    }

    public function test_adjust_brightness_returns_hex(): void
    {
        $this->assertIsString(Helper::adjustBrightness('#336699', 0.5));
    }

    public static function languageScriptProvider(): array
    {
        return [
            'chinese'  => ['你好', 'isChinese'],
            'japanese' => ['こんにちは', 'isJapanese'],
            'korean'   => ['안녕하세요', 'isKorean'],
        ];
    }

    #[DataProvider('languageScriptProvider')]
    public function test_script_detection_helpers(string $text, string $method): void
    {
        $this->assertTrue((bool) Helper::$method($text));
        $this->assertTrue((bool) Helper::isCjk($text));
    }

    public function test_has_rtl(): void
    {
        $this->assertIsBool(Helper::hasRtl('hello'));
        $this->assertTrue(Helper::hasRtl('مرحبا'));
    }

    public function test_parse_size(): void
    {
        $this->assertEquals(2097152.0, Helper::parse_size('2M'));
    }

    public function test_file_upload_helpers(): void
    {
        $this->assertIsNumeric(Helper::file_upload_max_size());
        $this->assertIsString(Helper::file_upload_max_size_readable());
    }

    public function test_format_filesize_units(): void
    {
        $this->assertIsString(Helper::formatFilesizeUnits(1048576));
    }

    public function test_filetype_icon_returns_class(): void
    {
        $this->assertIsString(Helper::filetype_icon('document.pdf'));
        $this->assertIsString(Helper::filetype_icon('image.jpg'));
    }

    public function test_get_formatted_date_object(): void
    {
        $result = Helper::getFormattedDateObject('2020-01-01 10:00:00', 'datetime', true);

        $this->assertArrayHasKey('datetime', $result);
        $this->assertNull(Helper::getFormattedDateObject('', 'datetime'));
    }

    public function test_predefined_formats_is_array(): void
    {
        $this->assertIsArray(Helper::predefined_formats());
    }

    public function test_barcode_dimensions(): void
    {
        $this->assertNotEmpty(Helper::barcodeDimensions('QRCODE'));
    }

    public function test_unit_conversion(): void
    {
        $this->assertIsNumeric(Helper::getUnitConversionFactor('cm'));
        $this->assertIsNumeric(Helper::convertUnit(100, 'cm', 'm'));
    }

    public function test_locale_mapping_helpers(): void
    {
        $this->assertIsString(Helper::mapLegacyLocale('en'));
        $this->assertIsString(Helper::determineLanguageDirection());
    }

    public function test_is_demo_mode_returns_bool(): void
    {
        $this->assertIsBool(Helper::isDemoMode());
    }

    public function test_format_currency_output(): void
    {
        $this->assertIsString(Helper::formatCurrencyOutput(1234.5));
    }

    public function test_dropdown_lists_are_arrays(): void
    {
        $this->assertIsArray(Helper::statusLabelList());
        $this->assertIsArray(Helper::deployableStatusLabelList());
        $this->assertIsArray(Helper::statusTypeList());
        $this->assertIsArray(Helper::depreciationList());
        $this->assertIsArray(Helper::categoryTypeList());
        $this->assertIsArray(Helper::customFieldsetList());
    }
}
