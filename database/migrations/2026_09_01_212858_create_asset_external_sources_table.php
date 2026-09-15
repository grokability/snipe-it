<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Combined identity + inventory table for sync-adapter data
        // about an asset. Carries which adapter created it
        // (source + external_id) plus the last-known network / OS
        // inventory (primary_mac / primary_ip / os / os_version /
        // last_seen). Kept separate from the assets table so
        // vendor-populated data can grow without widening the row
        // and eating custom-field headroom.
        //
        // Cardinality: one row per (source, external_id) pair,
        // enforced by the unique index below. asset_id is indexed
        // but not unique so the same physical device can be linked
        // across multiple adapters without a schema migration (an
        // admin running Fleet AND Kandji sees the same MacBook in
        // both).
        Schema::create('asset_external_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('asset_id')->index();
            $table->string('source', 191);
            $table->string('external_id', 191);
            $table->string('primary_mac', 191)->nullable()->index();
            $table->string('primary_ip', 191)->nullable()->index();
            $table->string('os', 191)->nullable();
            $table->string('os_version', 191)->nullable();
            $table->timestamp('last_seen')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_external_sources');
    }
};
