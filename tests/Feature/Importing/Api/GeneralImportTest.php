<?php

namespace Tests\Feature\Importing\Api;

use App\Models\User;

class GeneralImportTest extends ImportDataTestCase
{
    public function test_requires_existing_import()
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $this->importFileResponse(['import' => 9999, 'import-type' => 'accessory'])
            ->assertStatusMessageIs('import-errors');
    }
}
