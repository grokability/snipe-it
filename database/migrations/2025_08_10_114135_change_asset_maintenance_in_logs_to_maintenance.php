<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Actionlog;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Actionlog::where('item_type', 'App\\Models\\AssetMaintenance')->update(['item_type' => 'App\\Models\\Maintenance']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
