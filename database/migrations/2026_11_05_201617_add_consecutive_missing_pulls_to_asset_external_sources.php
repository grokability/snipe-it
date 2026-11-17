<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_external_sources', function (Blueprint $table) {
            $table->unsignedInteger('consecutive_missing_pulls')->default(0)->after('last_seen');
            $table->timestamp('missing_archived_at')->nullable()->after('consecutive_missing_pulls');
        });
    }

    public function down(): void
    {
        Schema::table('asset_external_sources', function (Blueprint $table) {
            $table->dropColumn(['consecutive_missing_pulls', 'missing_archived_at']);
        });
    }
};
