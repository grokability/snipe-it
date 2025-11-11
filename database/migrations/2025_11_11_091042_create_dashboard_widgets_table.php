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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('widget_id', 50); // e.g., 'assets', 'licenses', 'workorders_summary', etc.
            $table->boolean('is_visible')->default(true);
            $table->integer('grid_x')->default(0); // Grid X position
            $table->integer('grid_y')->default(0); // Grid Y position
            $table->integer('grid_width')->default(2); // Grid width (1-12)
            $table->integer('grid_height')->default(1); // Grid height
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint: one widget per user
            $table->unique(['user_id', 'widget_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
