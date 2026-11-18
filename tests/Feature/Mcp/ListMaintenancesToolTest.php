<?php

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssets()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns maintenances with pagination', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    Maintenance::factory()->create(['asset_id' => $asset->id]);

    $content = handle()->getStructuredContent();

    expect($content)->toHaveKey('maintenances');
    expect($content)->toHaveKey('total');
    expect($content['total'])->toBeGreaterThanOrEqual(1);
});

test('filters by asset id', function () {
    $asset1 = Asset::factory()->laptopZenbook()->create();
    $asset2 = Asset::factory()->laptopZenbook()->create();

    $maintenance1 = Maintenance::factory()->create(['asset_id' => $asset1->id]);
    Maintenance::factory()->create(['asset_id' => $asset2->id]);

    $content = handle(['asset_id' => $asset1->id])->getStructuredContent();

    expect($content['total'])->toBeGreaterThanOrEqual(1);
    foreach ($content['maintenances'] as $m) {
        // The response emits the polymorphic item_id (same value as
        // the legacy asset_id when item_type is Asset) instead of
        // the stale asset_id key.
        expect($m['item_id'])->toEqual($asset1->id);
    }
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('filters by asset tag', function () {
    $asset = Asset::factory()->laptopZenbook()->create(['asset_tag' => 'FIND-ME']);
    $other = Asset::factory()->laptopZenbook()->create(['asset_tag' => 'OTHER']);
    $matching = Maintenance::factory()->create(['asset_id' => $asset->id]);
    Maintenance::factory()->create(['asset_id' => $other->id]);

    $content = handle(['asset_tag' => 'FIND-ME'])->getStructuredContent();
    $ids = array_column($content['maintenances'], 'id');

    expect($ids)->toContain($matching->id);
    expect($ids)->toHaveCount(1);
});

test('returns error when asset tag not found', function () {
    expect(handle(['asset_tag' => 'NOPE'])->responses()->first()->isError())->toBeTrue();
});

test('status open excludes completed maintenances', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $open = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => null]);
    $closed = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => now()]);

    $content = handle(['status' => 'open'])->getStructuredContent();
    $ids = array_column($content['maintenances'], 'id');

    expect($ids)->toContain($open->id);
    expect($ids)->not->toContain($closed->id);
});

test('status completed excludes open maintenances', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $open = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => null]);
    $closed = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => now()]);

    $content = handle(['status' => 'completed'])->getStructuredContent();
    $ids = array_column($content['maintenances'], 'id');

    expect($ids)->toContain($closed->id);
    expect($ids)->not->toContain($open->id);
});

test('is warranty filter scopes results', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $warranty = Maintenance::factory()->create(['asset_id' => $asset->id, 'is_warranty' => true]);
    $paid = Maintenance::factory()->create(['asset_id' => $asset->id, 'is_warranty' => false]);

    $content = handle(['is_warranty' => true])->getStructuredContent();
    $ids = array_column($content['maintenances'], 'id');

    expect($ids)->toContain($warranty->id);
    expect($ids)->not->toContain($paid->id);
});

test('start date range narrows window', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $inWindow = Maintenance::factory()->create([
        'asset_id' => $asset->id,
        'start_date' => '2026-06-15',
    ]);
    $outOfWindow = Maintenance::factory()->create([
        'asset_id' => $asset->id,
        'start_date' => '2026-01-01',
    ]);

    $content = handle([
        'start_date_from' => '2026-06-01',
        'start_date_to' => '2026-06-30',
    ])->getStructuredContent();
    $ids = array_column($content['maintenances'], 'id');

    expect($ids)->toContain($inWindow->id);
    expect($ids)->not->toContain($outOfWindow->id);
});

test('response includes item fields and completed at', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $maintenance = Maintenance::factory()->create([
        'asset_id' => $asset->id,
        'completed_at' => '2026-05-10 14:00:00',
    ]);

    $content = handle(['limit' => 500])->getStructuredContent();
    $row = collect($content['maintenances'])->firstWhere('id', $maintenance->id);

    expect($row)->not->toBeNull();
    expect($row['item_type'])->toBe('Asset');
    expect($row['item_id'])->toBe($asset->id);
    expect($row['asset_tag'])->toBe($asset->asset_tag);
    expect($row)->toHaveKey('expected_completion_date');
    expect($row)->toHaveKey('completed_at');
    expect($row)->toHaveKey('notes');
});
