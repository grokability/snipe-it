<?php

use App\Models\Asset;
use Illuminate\Support\Facades\DB;

test('soft deleting an asset removes its sync adapter identity rows', function () {
    $asset = Asset::factory()->create();

    DB::table('asset_external_sources')->insert([
        'asset_id' => $asset->id,
        'source' => 'fleet',
        'external_id' => 'ext-42',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $asset->delete();

    // Soft-delete leaves the asset row in place with a deleted_at
    // timestamp, but the identity row is gone so the next sync
    // does not trip the unique(source, external_id) constraint.
    $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    expect($asset->fresh()->deleted_at)->not->toBeNull();
    $this->assertDatabaseMissing('asset_external_sources', ['asset_id' => $asset->id]);
});

test('deleting an asset with multiple source rows removes all of them', function () {
    // An asset synced from more than one vendor (Fleet + Landscape,
    // say) has one identity row per source. All get cleaned up.
    $asset = Asset::factory()->create();

    foreach (['fleet' => 'ext-1', 'landscape' => 'lscape-99'] as $source => $externalId) {
        DB::table('asset_external_sources')->insert([
            'asset_id' => $asset->id,
            'source' => $source,
            'external_id' => $externalId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $asset->delete();

    expect(DB::table('asset_external_sources')->where('asset_id', $asset->id)->count())->toBe(0);
});
