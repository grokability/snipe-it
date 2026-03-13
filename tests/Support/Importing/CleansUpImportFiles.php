<?php

namespace Tests\Support\Importing;

use App\Models\Import;
use Illuminate\Support\Facades\Storage;

trait CleansUpImportFiles
{
    public function setUp(): void
    {
        parent::setUp();

        Import::created(function (Import $import) {
            $this->beforeApplicationDestroyed(function () use ($import) {
                Storage::delete('private_uploads/imports/' . $import->file_path);
            });
        });
    }
}
