<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_part_asset_model', function (Blueprint $table) {
            $table->unsignedBigInteger('spare_part_id');
            $table->unsignedInteger('asset_model_id');
            $table->boolean('is_critical')->default(false);
            $table->unsignedInteger('recommended_qty')->default(1);

            $table->primary(['spare_part_id', 'asset_model_id']);

            $table->foreign('spare_part_id')->references('id')->on('spare_parts')->cascadeOnDelete();
            $table->foreign('asset_model_id')->references('id')->on('models')->cascadeOnDelete();

            $table->index('spare_part_id');
            $table->index('asset_model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_part_asset_model');
    }
};
