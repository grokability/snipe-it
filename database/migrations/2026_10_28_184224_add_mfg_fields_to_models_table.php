<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->string('mfg_marketing_name')->nullable()->after('name');
            $table->date('mfg_release_date')->nullable()->after('mfg_marketing_name');
            $table->date('mfg_discontinued_date')->nullable()->after('mfg_release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn(['mfg_marketing_name', 'mfg_release_date', 'mfg_discontinued_date']);
        });
    }
};
