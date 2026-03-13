<?php

namespace Tests\Feature\Importing\Api;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

abstract class ImportDataTestCase extends TestCase
{
    protected function importFileResponse(array $parameters = []): TestResponse
    {
        return $this->postJson(route('api.imports.importFile', $parameters), $parameters);
    }
}
