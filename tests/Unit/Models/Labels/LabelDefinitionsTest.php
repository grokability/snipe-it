<?php

namespace Tests\Unit\Models\Labels;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Instancia todas las definiciones de etiquetas (Sheets de Avery y Tapes de
 * Brother/Dymo/Generic) y ejercita sus getters sin parametros. Cada clase es
 * basicamente un conjunto de getters de dimensiones, asi que esto cubre el grueso.
 */
class LabelDefinitionsTest extends TestCase
{
    public static function labelClassProvider(): array
    {
        $base = dirname(__DIR__, 4).'/app/Models/Labels';
        $files = array_merge(
            glob($base.'/Sheets/Avery/*.php') ?: [],
            glob($base.'/Tapes/*/*.php') ?: [],
        );

        $cases = [];
        foreach ($files as $file) {
            // Derivar el FQCN a partir de la ruta.
            $relative = str_replace([$base, '/', '.php'], ['', '\\', ''], $file);
            $class = 'App\\Models\\Labels'.$relative;
            $cases[$class] = [$class];
        }

        return $cases;
    }

    #[DataProvider('labelClassProvider')]
    public function test_label_definition_getters(string $class): void
    {
        $this->assertTrue(class_exists($class), "No existe la clase {$class}");

        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            $this->markTestSkipped("{$class} es abstracta");
        }

        $instance = new $class;
        $this->assertIsObject($instance);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfRequiredParameters() > 0 || $method->isStatic()) {
                continue;
            }
            if (! str_starts_with($method->getName(), 'get')) {
                continue;
            }

            // Llamar el getter; no debe lanzar excepcion.
            $instance->{$method->getName()}();
        }

        $this->assertTrue(true);
    }
}
