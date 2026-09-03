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
        // Cardinality: one row per (source, external_id) pair
        // enforced by the unique index below. asset_id is only
        // indexed, not unique, so future adoption / multi-source
        // features (linking one physical device across two adapters,
        // migrating an existing Snipe-IT asset into a new MDM
        // without losing history) can add rows without a schema
        // migration. The Asset::externalSource() relation stays
        // hasOne while the sync path only writes one row per asset.
        // It flips to hasMany when we add the adoption UI.
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
            // Who created this source row. NULL for CLI-triggered
            // sync runs (no auth context) and for background /
            // scheduled runs. Populated with auth()->id() when the
            // sync fires from the interactive Sync Now button.
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            // Prevents duplicate sync rows from re-syncing the same
            // vendor-side device. The (source, external_id) pair is
            // the identity key SyncHostFromAdapter uses to decide
            // upsert vs create.
            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_external_sources');
    }
};
