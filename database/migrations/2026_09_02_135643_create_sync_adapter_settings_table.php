<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_adapter_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sync_adapter_instance_id')->index();
            $table->string('config_key', 191);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['sync_adapter_instance_id', 'config_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_adapter_settings');
    }
};
