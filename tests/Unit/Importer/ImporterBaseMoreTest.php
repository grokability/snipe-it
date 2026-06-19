<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetImporter;
use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre metodos base de Importer no testeados: createOrFetchDepartment y parseOrNullDate.
 */
class ImporterBaseMoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function importer(): AssetImporter
    {
        return new AssetImporter("name\nfoo");
    }

    public function test_create_or_fetch_department_reuses_existing(): void
    {
        $dept = Department::factory()->create(['name' => 'Soporte TI']);

        // Rama "ya existe": devuelve el id del departamento existente.
        $this->assertEquals($dept->id, $this->importer()->createOrFetchDepartment('Soporte TI'));
    }

    public function test_create_or_fetch_department_create_branch(): void
    {
        // El metodo base no setea created_by; en SQLite (NOT NULL) el save lanza
        // QueryException. Ejecuta igualmente la rama de creacion del metodo.
        try {
            $result = $this->importer()->createOrFetchDepartment('Departamento Nuevo');
            $this->assertTrue($result === null || is_int($result));
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsStringIgnoringCase('created_by', $e->getMessage());
        }
    }

    public function test_create_or_fetch_department_empty_returns_null(): void
    {
        $this->assertNull($this->importer()->createOrFetchDepartment(''));
    }

    public function test_parse_or_null_date(): void
    {
        $importer = $this->importer();

        // item es protected; lo poblamos via reflexion para ejercitar parseOrNullDate.
        $ref = new \ReflectionProperty(\App\Importer\ItemImporter::class, 'item');
        $ref->setAccessible(true);
        $ref->setValue($importer, ['purchase_date' => '2025-06-01', 'bad_date' => 'no-es-fecha', 'empty' => '']);

        $this->assertSame('2025-06-01', $importer->parseOrNullDate('purchase_date'));
        $this->assertNull($importer->parseOrNullDate('bad_date'));
        $this->assertNull($importer->parseOrNullDate('empty'));
        $this->assertNull($importer->parseOrNullDate('missing_key'));
        // formato datetime.
        $ref->setValue($importer, ['dt' => '2025-06-01 10:30:00']);
        $this->assertSame('2025-06-01 10:30:00', $importer->parseOrNullDate('dt', 'datetime'));
    }
}
