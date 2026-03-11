<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->enum('shift_type', ['06-14', '14-22', '22-06']);
            $table->date('shift_date');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('outgoing_user_id');
            $table->unsignedInteger('incoming_user_id');
            $table->text('outgoing_signature')->nullable();
            $table->text('incoming_signature')->nullable();
            $table->text('outgoing_notes')->nullable();
            $table->text('incoming_notes')->nullable();
            $table->enum('status', ['bekliyor', 'tamamlandi'])->default('bekliyor');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
            $table->foreign('outgoing_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('incoming_user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index('location_id');
            $table->index('shift_date');
            $table->index('status');
            $table->index(['location_id', 'shift_date', 'shift_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};
