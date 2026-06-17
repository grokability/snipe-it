<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelperMoreTest extends TestCase
{
    public static function modelNameProvider(): array
    {
        return [
            ['asset', 'App\\Models\\Asset'],
            ['user', 'App\\Models\\User'],
            ['App\\Models\\Asset', 'App\\Models\\Asset'],
        ];
    }

    #[DataProvider('modelNameProvider')]
    public function test_normalize_full_model_name(string $input, string $expected): void
    {
        $this->assertEquals($expected, Helper::normalizeFullModelName($input));
    }

    public function test_deprecation_check_returns_array(): void
    {
        $this->assertIsArray(Helper::deprecationCheck());
    }

    public function test_parse_escaped_markdown_returns_string(): void
    {
        $this->assertIsString(Helper::parseEscapedMarkedown('**bold** text'));
        $this->assertIsString(Helper::parseEscapedMarkedownInline('_italic_'));
    }

    public function test_map_back_to_legacy_locale(): void
    {
        $this->assertIsString(Helper::mapBackToLegacyLocale('en-US'));
    }

    public function test_selected_permissions_array_with_empty(): void
    {
        $this->assertIsArray(Helper::selectedPermissionsArray(null));
        $this->assertIsArray(Helper::selectedPermissionsArray('{}'));
    }

    public function test_format_currency_output_is_string(): void
    {
        $this->assertIsString(Helper::formatCurrencyOutput(0));
        $this->assertIsString(Helper::formatCurrencyOutput(9999.99));
    }

    public function test_setting_urls_returns_array(): void
    {
        $this->assertIsArray(Helper::SettingUrls());
    }
}
