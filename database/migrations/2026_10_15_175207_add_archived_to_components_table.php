<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an `archived` flag to the components table.
     */
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->boolean('archived')->default(0)->after('requestable')->index();
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('archived');
        });
    }
};
