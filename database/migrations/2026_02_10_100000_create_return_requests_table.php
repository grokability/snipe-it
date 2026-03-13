<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};

