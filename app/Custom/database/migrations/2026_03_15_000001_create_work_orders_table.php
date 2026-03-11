<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number', 20)->unique();
            $table->unsignedInteger('asset_id');
            $table->unsignedInteger('reported_by');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->enum('priority', ['kritik', 'yuksek', 'normal'])->default('normal');
            $table->enum('status', ['bekliyor', 'atandi', 'devam_ediyor', 'tamamlandi', 'iptal'])->default('bekliyor');
            $table->text('description');
            $table->text('resolution_notes')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->unsignedInteger('time_spent_minutes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('reported_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();

            $table->index('status');
            $table->index('priority');
            $table->index('asset_id');
            $table->index('assigned_to');
            $table->index('reported_by');
            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
