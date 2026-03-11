<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_part_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spare_part_id');
            $table->enum('movement_type', ['giris', 'cikis', 'sayim', 'iade']);
            $table->integer('quantity');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedInteger('performed_by');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('spare_part_id')->references('id')->on('spare_parts')->cascadeOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->cascadeOnDelete();

            $table->index('spare_part_id');
            $table->index('movement_type');
            $table->index('performed_by');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_part_movements');
    }
};
