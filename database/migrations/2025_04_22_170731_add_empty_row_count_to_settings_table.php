<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedInteger('label2_empty_row_count')->default(0)->after('label2_fields');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('label2_empty_row_count');
        });
    }
};
