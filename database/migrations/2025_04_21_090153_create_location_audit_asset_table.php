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
        Schema::create('location_audit_asset', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('location_audit_id');
            $table->unsignedInteger('asset_id');
            $table->boolean('present')->default(true);
        
            $table->foreign('location_audit_id')
                  ->references('id')->on('location_audits')
                  ->onDelete('cascade');
        
            $table->foreign('asset_id')
                  ->references('id')->on('assets')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_audit_asset');
    }
};
