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
        Schema::create('location_audits', function (Blueprint $table) {
            $table->increments('id');                 // INT UNSIGNED AI
            $table->unsignedInteger('location_id');   // <— match locations.id
            $table->unsignedInteger('user_id');       // <— match users.id
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('location_id')
                ->references('id')->on('locations')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_audits');
    }
};
