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
        Schema::create('maintenance_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('asset_id');
            $table->unsignedInteger('work_order_id')->nullable();
            $table->unsignedInteger('maintenance_schedule_id')->nullable();
            $table->unsignedInteger('predefined_kit_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['preventive', 'corrective', 'inspection', 'emergency', 'other'])->default('preventive');
            $table->dateTime('performed_at');
            $table->unsignedInteger('performed_by');
            $table->integer('duration')->nullable(); // in minutes
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('work_performed')->nullable();
            $table->text('parts_used')->nullable();
            $table->json('components_replaced')->nullable();
            $table->json('consumables_used')->nullable();
            $table->enum('outcome', ['success', 'partial', 'failed', 'deferred'])->default('success');
            $table->text('notes')->nullable();
            $table->string('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('set null');
            $table->foreign('maintenance_schedule_id')->references('id')->on('maintenance_schedules')->onDelete('set null');
            $table->foreign('predefined_kit_id')->references('id')->on('kits')->onDelete('set null');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_history');
    }
};
