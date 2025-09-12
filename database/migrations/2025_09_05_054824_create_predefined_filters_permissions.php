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
        Schema::create('predefined_filter_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('predefined_filter_id')
                ->constrained('predefined_filters')
                ->onDelete('cascade');
            
            $table->unsignedBigInteger('permission_group_id');
            $table->unsignedInteger('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predefined_filter_permissions');
    }
};
