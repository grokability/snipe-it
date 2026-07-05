<?php

namespace Tests\Unit\Rules;

use App\Rules\CssColor;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CssColorTest extends TestCase
{
    public static function validColorProvider(): array
    {
        return [
            'hex 3-digit' => ['#abc'],
            'hex 6-digit' => ['#3c8dbc'],
            'hex uppercase' => ['#FFFFFF'],
            'hex 4-digit rgba' => ['#abcd'],
            'hex 8-digit rgba' => ['#3c8dbc80'],
            'rgb' => ['rgb(10,20,30)'],
            'rgb with spaces' => ['rgb( 10 , 20 , 30 )'],
            'rgba' => ['rgba(10,20,30,0.5)'],
            'hsl' => ['hsl(120,50%,50%)'],
            'hsla' => ['hsla(120,50%,50%,0.8)'],
        ];
    }

    public static function invalidColorProvider(): array
    {
        return [
            'named color' => ['red'],
            'css injection payload' => ['red; }body{background:url(//evil.com)} .x{color: #'],
            'expression' => ['expression(alert(1))'],
            'url()' => ['url(http://evil.com)'],
            'value with semicolon' => ['#3c8dbc; color: red'],
            'empty string' => [''],
            'arbitrary string' => ['not-a-color'],
            'javascript scheme' => ['javascript:alert(1)'],
        ];
    }

    #[DataProvider('validColorProvider')]
    public function test_validate_passes_for_valid_colors(string $color): void
    {
        $failed = false;
        (new CssColor)->validate('color', $color, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed, "Expected '{$color}' to pass validation but it failed.");
    }

    #[DataProvider('invalidColorProvider')]
    public function test_validate_fails_for_invalid_colors(string $color): void
    {
        $failed = false;
        (new CssColor)->validate('color', $color, function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed, "Expected '{$color}' to fail validation but it passed.");
    }

    #[DataProvider('validColorProvider')]
    public function test_sanitize_returns_value_for_valid_colors(string $color): void
    {
        $this->assertSame($color, CssColor::sanitize($color, '#000000'));
    }

    #[DataProvider('invalidColorProvider')]
    public function test_sanitize_returns_default_for_invalid_colors(string $color): void
    {
        $this->assertSame('#000000', CssColor::sanitize($color, '#000000'));
    }

    public function test_sanitize_returns_default_for_null(): void
    {
        $this->assertSame('#fallback', CssColor::sanitize(null, '#fallback'));
    }
}
