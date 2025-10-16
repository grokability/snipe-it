<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $assetmodelfiles = Storage::allFiles('private_uploads/assetmodels');

        foreach ($assetmodelfiles as $file) {
            Storage::writeStream('private_uploads/models/' . basename($file),
                Storage::readStream($file)
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
