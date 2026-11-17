<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Default 1 preserves the pre-toggle behavior: the modal
            // already treated the note as required.
            $table->boolean('require_adjust_quantity_notes')->nullable()->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'require_adjust_quantity_notes')) {
                $table->dropColumn('require_adjust_quantity_notes');
            }
        });
    }
};
