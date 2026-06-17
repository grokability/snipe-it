<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetImporter;
use App\Importer\UserImporter;
use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Ejercita las rutas de ACTUALIZACION del importador (re-importar un registro
 * existente con setUpdating(true)), que recorren ramas distintas de ItemImporter.
 */
class ImporterUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function runImport(string $class, string $csv, bool $updating): void
    {
        $importer = new $class($csv);
        $importer->setCreatedBy(auth()->id());
        $importer->setUpdating($updating);
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();
    }

    public function test_user_import_updates_existing_user(): void
    {
        $csv = "First Name,Last Name,Email,Username,Job Title\nJane,Doe,jane@example.com,jdoe,Engineer\n";
        $this->runImport(UserImporter::class, $csv, false);

        // Re-import con job title distinto y updating activo.
        $csv2 = "First Name,Last Name,Email,Username,Job Title\nJane,Doe,jane@example.com,jdoe,Manager\n";
        $this->runImport(UserImporter::class, $csv2, true);

        $this->assertEquals(1, User::where('username', 'jdoe')->count());
        $this->assertDatabaseHas('users', ['username' => 'jdoe', 'jobtitle' => 'Manager']);
    }

    public function test_asset_import_updates_existing_asset(): void
    {
        Statuslabel::factory()->readyToDeploy()->create();

        $csv = "Asset Tag,Model Name,Category,Manufacturer,Status,Notes\nUPD-1,MBP,Laptops,Apple,Ready to Deploy,first\n";
        $this->runImport(AssetImporter::class, $csv, false);

        $csv2 = "Asset Tag,Model Name,Category,Manufacturer,Status,Notes\nUPD-1,MBP,Laptops,Apple,Ready to Deploy,updated\n";
        $this->runImport(AssetImporter::class, $csv2, true);

        $this->assertEquals(1, Asset::where('asset_tag', 'UPD-1')->count());
    }
}
