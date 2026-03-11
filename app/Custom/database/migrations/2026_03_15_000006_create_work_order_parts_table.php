<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_id');
            $table->unsignedBigInteger('spare_part_id');
            $table->unsignedInteger('quantity_used')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('work_order_id')->references('id')->on('work_orders')->cascadeOnDelete();
            $table->foreign('spare_part_id')->references('id')->on('spare_parts')->cascadeOnDelete();

            $table->index('work_order_id');
            $table->index('spare_part_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_parts');
    }
};
