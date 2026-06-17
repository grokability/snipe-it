<?php

namespace Tests\Unit\Helpers;

use App\Helpers\IconHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * IconHelper::icon() es un switch que mapea cada tipo a una clase de icono.
 * El data provider extrae todos los `case '...'` del propio fuente para
 * ejercitar cada rama sin tener que listarlas a mano.
 */
class IconHelperTest extends TestCase
{
    public static function iconTypeProvider(): array
    {
        $source = file_get_contents(dirname(__DIR__, 3).'/app/Helpers/IconHelper.php');
        preg_match_all("/case '([^']+)'/", $source, $matches);

        $cases = [];
        foreach (array_unique($matches[1]) as $type) {
            $cases[$type] = [$type];
        }

        return $cases;
    }

    #[DataProvider('iconTypeProvider')]
    public function test_icon_returns_non_empty_class(string $type): void
    {
        $icon = IconHelper::icon($type);

        $this->assertIsString($icon);
        $this->assertNotEmpty($icon);
        $this->assertStringContainsString('fa', $icon);
    }

    public function test_icon_returns_null_for_unknown_type(): void
    {
        // No hay caso por defecto en el switch: un tipo desconocido devuelve null.
        $this->assertNull(IconHelper::icon('totally-unknown-type-xyz'));
    }
}
