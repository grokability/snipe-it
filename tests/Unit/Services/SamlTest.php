<?php

namespace Tests\Unit\Services;

use App\Services\Saml;
use Tests\TestCase;

class SamlTest extends TestCase
{
    public function test_constructor_builds_service(): void
    {
        // El constructor ensambla toda la configuracion SAML a partir de settings.
        $saml = new Saml;

        $this->assertInstanceOf(Saml::class, $saml);
    }

    public function test_is_enabled_returns_bool(): void
    {
        $this->assertIsBool((new Saml)->isEnabled());
    }

    public function test_get_setting_returns_default_when_missing(): void
    {
        $saml = new Saml;

        $this->assertEquals('fallback', $saml->getSetting('nonexistent_key', 'fallback'));
    }

    public function test_clear_data_does_not_throw(): void
    {
        $saml = new Saml;
        $saml->clearData();

        $this->assertTrue(true);
    }

    public function test_is_authenticated_returns_bool(): void
    {
        $this->assertIsBool((new Saml)->isAuthenticated());
    }
}
