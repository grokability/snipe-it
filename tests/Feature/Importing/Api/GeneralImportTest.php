<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Import;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\UsersImportFileBuilder;

class GeneralImportTest extends ImportDataTestCase
{
    use CleansUpImportFiles;

    public function test_requires_existing_import()
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $this->importFileResponse(['import' => 9999, 'import-type' => 'accessory'])
            ->assertStatusMessageIs('import-errors');
    }

    #[Test]
    public function processing_another_users_import_does_not_overwrite_created_by(): void
    {
        $originalOwner = User::factory()->superuser()->create();
        $otherUser = User::factory()->superuser()->create();

        $import = Import::factory()->users()->create([
            'file_path' => UsersImportFileBuilder::new()->saveToImportsDirectory(),
            'created_by' => $originalOwner->id,
        ]);

        $this->actingAsForApi($otherUser);
        $this->importFileResponse(['import' => $import->id, 'import-type' => 'user'])->assertOk();

        $this->assertEquals($originalOwner->id, $import->refresh()->created_by);
    }
}
