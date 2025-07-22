<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Artisan::call("snipeit:clean-checkout-acceptances");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
