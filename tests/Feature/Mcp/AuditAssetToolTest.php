<?php

use App\Mcp\Tools\AuditAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->auditAssets()->create());
});

function auditAssetTool(array $args): ResponseFactory
{
    return (new AuditAssetTool)->handle(new Request($args));
}

test('records audit by asset tag', function () {
    $asset = Asset::factory()->create();

    $content = auditAssetTool(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('records audit by numeric id', function () {
    $asset = Asset::factory()->create();

    $content = auditAssetTool(['id' => $asset->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('records audit by serial', function () {
    $asset = Asset::factory()->create(['serial' => 'SN-AUDIT-001']);

    $content = auditAssetTool(['serial' => 'SN-AUDIT-001'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('returns error when asset not found', function () {
    expect(auditAssetTool(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError())->toBeTrue();
});

test('sets last audit date to now', function () {
    $asset = Asset::factory()->create(['last_audit_date' => null]);

    auditAssetTool(['asset_tag' => $asset->asset_tag]);

    expect($asset->fresh()->last_audit_date)->not->toBeNull();
});

test('respects explicit next audit date', function () {
    $asset = Asset::factory()->create();

    auditAssetTool([
        'asset_tag' => $asset->asset_tag,
        'next_audit_date' => '2027-01-15',
    ]);

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'next_audit_date' => '2027-01-15',
    ]);
});

test('updates location when provided', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->create();

    auditAssetTool([
        'asset_tag' => $asset->asset_tag,
        'location_id' => $location->id,
    ]);

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'location_id' => $location->id,
    ]);
});

test('does not change location when not provided', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->create(['location_id' => $location->id]);

    auditAssetTool(['asset_tag' => $asset->asset_tag]);

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'location_id' => $location->id,
    ]);
});

test('response includes audit dates and asset tag', function () {
    $asset = Asset::factory()->create();

    $content = auditAssetTool(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['asset_tag'])->toEqual($asset->asset_tag);
    expect($content)->toHaveKey('last_audit_date');
    expect($content)->toHaveKey('next_audit_date');
    expect($content['last_audit_date'])->not->toBeNull();
});

test('creates audit log entry', function () {
    $asset = Asset::factory()->create();

    auditAssetTool([
        'asset_tag' => $asset->asset_tag,
        'note' => 'MCP audit note',
    ]);

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'audit',
    ]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    $response = auditAssetTool(['asset_tag' => $asset->asset_tag]);

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($asset->fresh()->last_audit_date)->toBeNull();
});
