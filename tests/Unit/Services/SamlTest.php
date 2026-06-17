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

    public function test_simple_getters_without_saml_response(): void
    {
        $saml = new Saml;

        // Sin respuesta SAML, los getters devuelven sus valores por defecto sin lanzar excepcion.
        $this->assertTrue(is_array($saml->getAttributes()) || is_null($saml->getAttributes()));
        $this->assertTrue(is_array($saml->getAttributesWithFriendlyName()) || is_null($saml->getAttributesWithFriendlyName()));
        $this->assertNull($saml->getNameId());
        $this->assertNull($saml->getNameIdFormat());
        $this->assertNull($saml->getNameIdNameQualifier());
        $this->assertNull($saml->getNameIdSPNameQualifier());
        $this->assertNull($saml->getSessionIndex());
        $this->assertNull($saml->getSessionExpiration());
    }

    public function test_get_auth_throws_when_disabled(): void
    {
        // getAuth() lanza 403 cuando SAML no esta habilitado.
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new Saml)->getAuth();
    }
}
