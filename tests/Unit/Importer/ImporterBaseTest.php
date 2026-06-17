<?php

namespace Tests\Unit\Importer;

use App\Importer\UserImporter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre los metodos helper de la clase base App\Importer\Importer a traves de
 * una instancia concreta (UserImporter), usando un CSV en memoria.
 */
class ImporterBaseTest extends TestCase
{
    private function importer(string $csv = "first_name,last_name\nJane,Doe"): UserImporter
    {
        return new UserImporter($csv);
    }

    public static function humanBooleanProvider(): array
    {
        return [
            ['yes', 1],
            ['y', 1],
            ['true', 1],
            ['1', 1],
            ['no', 0],
            ['false', 0],
            ['0', 0],
            ['garbage', 0],
        ];
    }

    #[DataProvider('humanBooleanProvider')]
    public function test_fetch_human_boolean(string $input, int $expected): void
    {
        $this->assertSame($expected, $this->importer()->fetchHumanBoolean($input));
    }

    public function test_find_csv_match_returns_value(): void
    {
        $importer = $this->importer();
        // Clave no mapeada -> lookupCustomKey la devuelve tal cual y se busca en la fila.
        $row = ['zzz_custom' => '  Jane  '];

        $this->assertEquals('Jane', $importer->findCsvMatch($row, 'zzz_custom'));
    }

    public function test_find_csv_match_returns_default_when_missing(): void
    {
        $importer = $this->importer();

        $this->assertEquals('fallback', $importer->findCsvMatch([], 'zzz_missing', 'fallback'));
    }

    public function test_setters_are_fluent_and_apply(): void
    {
        $importer = $this->importer();

        // Los setters no devuelven $this pero deben aplicar sin error.
        $importer->setCreatedBy(1);
        $importer->setUpdating(true);
        $importer->setShouldNotify(false);
        $importer->setUsernameFormat('firstname.lastname');
        $importer->setFieldMappings(['first_name' => 'First Name']);

        $this->assertTrue(true);
    }

    public function test_lookup_custom_key_falls_back_to_key(): void
    {
        $importer = $this->importer();

        // Una clave no mapeada se devuelve tal cual.
        $this->assertEquals('unmapped_key', $importer->lookupCustomKey('unmapped_key'));
    }
}
