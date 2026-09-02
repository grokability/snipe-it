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
        Schema::create('custom_user_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_label')->nullable();
            $table->string('type')->default('sheet');
            $table->text('overrides')->nullable();
            $table->text('config_snapshot')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_user_labels');
    }
};
