<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettingLogicTest extends TestCase
{
    public function test_get_settings_returns_instance(): void
    {
        $this->assertInstanceOf(Setting::class, Setting::getSettings());
    }

    public function test_setup_completed_returns_bool(): void
    {
        $this->assertIsBool(Setting::setupCompleted());
    }

    public function test_lar_ver_returns_string(): void
    {
        $this->assertIsString(Setting::getSettings()->lar_ver());
    }

    public function test_get_default_eula_returns_string_or_null(): void
    {
        $result = Setting::getDefaultEula();
        $this->assertTrue(is_string($result) || is_null($result));
    }

    public static function fileSizeProvider(): array
    {
        return [
            [0],
            [500],
            [1024],
            [1048576],
            [1073741824],
        ];
    }

    #[DataProvider('fileSizeProvider')]
    public function test_file_size_convert_returns_readable_string(int $bytes): void
    {
        $this->assertIsString(Setting::fileSizeConvert($bytes));
    }

    public function test_password_complexity_rules_saving(): void
    {
        $this->assertIsString(Setting::passwordComplexityRulesSaving('update'));
        $this->assertIsString(Setting::passwordComplexityRulesSaving('store'));
    }

    public function test_show_custom_css_returns_string(): void
    {
        $this->assertIsString(Setting::getSettings()->show_custom_css());
    }

    public function test_route_notification_helpers(): void
    {
        $settings = Setting::getSettings();

        // Devuelven el endpoint/correo o null; no deben lanzar excepcion.
        $this->assertTrue(true);
        $settings->routeNotificationForSlack();
        $settings->routeNotificationForMail();
    }

    public function test_get_ldap_settings_returns_collection(): void
    {
        $this->assertInstanceOf(Collection::class, Setting::getLdapSettings());
    }
}
