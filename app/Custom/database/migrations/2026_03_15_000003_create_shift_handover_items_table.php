<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_handover_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_handover_id');
            $table->unsignedInteger('asset_id');
            $table->enum('condition', ['iyi', 'hasarli', 'eksik'])->default('iyi');
            $table->text('notes')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->unsignedBigInteger('work_order_id')->nullable();
            $table->timestamps();

            $table->foreign('shift_handover_id')->references('id')->on('shift_handovers')->cascadeOnDelete();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('work_order_id')->references('id')->on('work_orders')->nullOnDelete();

            $table->index('shift_handover_id');
            $table->index('asset_id');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_handover_items');
    }
};
