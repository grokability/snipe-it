<?php

namespace Tests\Feature\Importing\Api;

use App\Models\User;

class GeneralImportTest extends ImportDataTestCase
{
    public function testRequiresExistingImport()
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $this->importFileResponse(['import' => 9999, 'import-type' => 'accessory'])
            ->assertStatusMessageIs('import-errors');
    }
}
