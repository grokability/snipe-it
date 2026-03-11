<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 20)->unique();
            $table->unsignedBigInteger('spare_part_id');
            $table->unsignedInteger('requested_quantity');
            $table->decimal('estimated_cost', 20, 2)->nullable();
            $table->text('reason');
            $table->enum('status', ['bekliyor', 'onaylandi', 'siparis_verildi', 'teslim_alindi', 'iptal'])->default('bekliyor');
            $table->unsignedInteger('requested_by');
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('work_order_id')->nullable();
            $table->timestamps();

            $table->foreign('spare_part_id')->references('id')->on('spare_parts')->cascadeOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->foreign('work_order_id')->references('id')->on('work_orders')->nullOnDelete();

            $table->index('status');
            $table->index('spare_part_id');
            $table->index('requested_by');
            $table->index('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
