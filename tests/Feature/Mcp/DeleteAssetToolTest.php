<?php

use App\Events\CheckoutableCheckedIn;
use App\Mcp\Servers\SnipeMCPServer;
use App\Mcp\Tools\DeleteAssetTool;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes asset by asset tag', function () {
    $asset = Asset::factory()->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('deletes asset by numeric id', function () {
    $asset = Asset::factory()->create();

    $content = handle(['id' => $asset->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('deletes asset by serial', function () {
    $asset = Asset::factory()->create(['serial' => 'SN-DELETE-001']);

    $content = handle(['serial' => 'SN-DELETE-001'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('returns error when asset not found', function () {
    expect(handle(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError())->toBeTrue();
});

test('checks in asset before deleting when checked out', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    handle(['asset_tag' => $asset->asset_tag]);

    Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($asset) {
        return $event->checkoutable->id === $asset->id;
    });

    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('deletes unassigned asset without firing checkin event', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $asset = Asset::factory()->create();

    handle(['asset_tag' => $asset->asset_tag]);

    Event::assertNotDispatched(CheckoutableCheckedIn::class);
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('response includes asset tag', function () {
    $asset = Asset::factory()->create(['asset_tag' => 'TAG-DEL-001']);

    $content = handle(['asset_tag' => 'TAG-DEL-001'])->getStructuredContent();

    expect($content['asset_tag'])->toEqual('TAG-DEL-001');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted($asset);
});

test('array shape defaults to dry run and does not delete', function () {
    $a = Asset::factory()->create();
    $b = Asset::factory()->create();

    $content = handle(['ids' => [$a->id, $b->id]])->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
    expect($content['total_matched'])->toBe(2);
    expect($content['will_delete'])->toBe(2);
    $this->assertNotSoftDeleted($a);
    $this->assertNotSoftDeleted($b);
});

test('array shape commits when dry run false', function () {
    $a = Asset::factory()->create();
    $b = Asset::factory()->create();

    $content = handle([
        'ids' => [$a->id, $b->id],
        'dry_run' => false,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['dry_run'])->toBeFalse();
    expect($content['deleted_count'])->toBe(2);
    $this->assertSoftDeleted('assets', ['id' => $a->id]);
    $this->assertSoftDeleted('assets', ['id' => $b->id]);
});

test('array shape accepts asset tags', function () {
    $a = Asset::factory()->create(['asset_tag' => 'BULK-A']);
    $b = Asset::factory()->create(['asset_tag' => 'BULK-B']);

    handle([
        'asset_tags' => ['BULK-A', 'BULK-B'],
        'dry_run' => false,
    ]);

    $this->assertSoftDeleted('assets', ['id' => $a->id]);
    $this->assertSoftDeleted('assets', ['id' => $b->id]);
});

test('filter shape defaults to dry run', function () {
    $a = Asset::factory()->create();
    $b = Asset::factory()->create();

    $content = handle([
        'created_before' => now()->addDay()->toDateString(),
    ])->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
    expect($content['total_matched'])->toBeGreaterThanOrEqual(2);
    $this->assertNotSoftDeleted($a);
    $this->assertNotSoftDeleted($b);
});

test('filter shape requires confirm count on commit', function () {
    Asset::factory()->create();

    $response = handle([
        'created_before' => now()->addDay()->toDateString(),
        'dry_run' => false,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('filter shape rejects mismatched confirm count', function () {
    $existingCount = Asset::count();
    Asset::factory()->count(3)->create();

    $response = handle([
        'created_before' => now()->addDay()->toDateString(),
        'dry_run' => false,
        'confirm_count' => $existingCount + 999,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    expect(Asset::count())->toBe($existingCount + 3);
});

test('filter shape commits when confirm count matches', function () {
    // Use a filter narrow enough to only match our seeded rows.
    $tagPrefix = 'BULK-FILT-';
    $assets = Asset::factory()->count(3)->sequence(
        ['asset_tag' => $tagPrefix.'1'],
        ['asset_tag' => $tagPrefix.'2'],
        ['asset_tag' => $tagPrefix.'3'],
    )->create();

    $content = handle([
        'search' => $tagPrefix,
        'dry_run' => false,
        'confirm_count' => 3,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['deleted_count'])->toBe(3);
    foreach ($assets as $asset) {
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }
});

test('filter shape resolves company name to id', function () {
    $company = \App\Models\Company::factory()->create(['name' => 'BulkCo']);
    Asset::factory()->create(['company_id' => $company->id]);
    Asset::factory()->create();

    $content = handle(['company_name' => 'BulkCo'])->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
    expect($content['total_matched'])->toBe(1);
});

test('does not delete assets for an unknown model name', function () {
    config(['app.debug' => false]);
    $asset = Asset::factory()->create();

    $response = SnipeMCPServer::tool(DeleteAssetTool::class, [
        'model_name' => 'Unknown Bulk Delete Model',
        'dry_run' => false,
        'confirm_count' => 1,
    ]);

    $response->assertHasErrors([
        trans('mcp.fk_not_found', [
            'field' => 'model_name',
            'value' => 'Unknown Bulk Delete Model',
        ]),
    ]);
    $this->assertNotSoftDeleted($asset);
});
