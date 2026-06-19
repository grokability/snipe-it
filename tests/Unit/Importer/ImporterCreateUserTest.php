<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetImporter;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre Importer::createOrFetchUser, que se dispara durante el import de assets
 * cuando la fila trae datos de checkout a usuario (Full Name / Username / Email).
 */
class ImporterCreateUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
        Statuslabel::factory()->readyToDeploy()->create();
    }

    private function runImport(string $csv): void
    {
        $importer = new AssetImporter($csv);
        $importer->setCreatedBy(auth()->id());
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();
    }

    public function test_import_creates_user_from_full_name(): void
    {
        $csv = "Asset Tag,Model Name,Category,Status,Full Name\n"
             . "IMP-U1,MacBook,Laptops,Ready to Deploy,Carla Mendez\n";

        $before = User::count();
        $this->runImport($csv);

        $this->assertGreaterThan($before, User::count());
        $this->assertDatabaseHas('assets', ['asset_tag' => 'IMP-U1']);
    }

    public function test_import_matches_existing_user_by_username(): void
    {
        $existing = User::factory()->create(['username' => 'jdoe', 'first_name' => 'John', 'last_name' => 'Doe']);

        $csv = "Asset Tag,Model Name,Category,Status,Full Name,Username,Email\n"
             . "IMP-U2,MacBook,Laptops,Ready to Deploy,John Doe,jdoe,jdoe@example.com\n";

        $before = User::count();
        $this->runImport($csv);

        // El usuario existente se reutiliza (no se crea uno nuevo).
        $this->assertSame($before, User::count());
        $this->assertDatabaseHas('assets', ['asset_tag' => 'IMP-U2', 'assigned_to' => $existing->id]);
    }

    public function test_import_checkout_to_location(): void
    {
        $csv = "Asset Tag,Model Name,Category,Status,Checkout Type,Checkout Location\n"
             . "IMP-U3,MacBook,Laptops,Ready to Deploy,location,Bodega Central\n";

        $this->runImport($csv);

        $this->assertDatabaseHas('assets', ['asset_tag' => 'IMP-U3']);
        $this->assertDatabaseHas('locations', ['name' => 'Bodega Central']);
    }
}
