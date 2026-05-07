<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        //Artisan::call('snipeit:clean-checkout-acceptances');
	// Commenting this out to prevent crashing due to a missing deleted_at clause
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
