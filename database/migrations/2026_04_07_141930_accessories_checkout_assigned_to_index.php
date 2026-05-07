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
        Schema::table('accessories_checkout', function (Blueprint $table) {
            $table->index(['assigned_to','assigned_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessories_checkout', function (Blueprint $table) {
            $table->dropIndex(['assigned_to','assigned_type']);
        });
    }
};
