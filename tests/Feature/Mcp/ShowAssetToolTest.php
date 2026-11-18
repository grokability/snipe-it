<?php

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('returns error when asset tag not found', function () {
    $response = handle(['asset_tag' => 'DOES-NOT-EXIST']);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when id not found', function () {
    $response = handle(['id' => 99999]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('finds asset by asset tag', function () {
    $asset = Asset::factory()->create(['asset_tag' => 'TAG-FIND-001']);

    $content = handle(['asset_tag' => 'TAG-FIND-001'])->getStructuredContent();

    expect($content['asset_tag'])->toEqual('TAG-FIND-001');
    expect($content['id'])->toEqual($asset->id);
});

test('finds asset by serial', function () {
    $asset = Asset::factory()->create(['serial' => 'SN-FIND-001']);

    $content = handle(['serial' => 'SN-FIND-001'])->getStructuredContent();

    expect($content['serial'])->toEqual('SN-FIND-001');
    expect($content['id'])->toEqual($asset->id);
});

test('returns error when serial not found', function () {
    $response = handle(['serial' => 'DOES-NOT-EXIST']);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('finds asset by numeric id', function () {
    $asset = Asset::factory()->create();

    $content = handle(['id' => $asset->id])->getStructuredContent();

    expect($content['asset_tag'])->toEqual($asset->asset_tag);
    expect($content['id'])->toEqual($asset->id);
});

test('returns model status and manufacturer details', function () {
    $asset = Asset::factory()->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content)->toHaveKey('model');
    expect($content)->toHaveKey('model_number');
    expect($content)->toHaveKey('category');
    expect($content)->toHaveKey('manufacturer');
    expect($content)->toHaveKey('status');
    expect($content)->toHaveKey('status_type');
});

test('returns date and cost fields', function () {
    $asset = Asset::factory()->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content)->toHaveKey('purchase_date');
    expect($content)->toHaveKey('purchase_cost');
    expect($content)->toHaveKey('warranty_months');
    expect($content)->toHaveKey('created_at');
    expect($content)->toHaveKey('updated_at');
});

test('shows assignment info when checked out to user', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['assigned_to_id'])->toEqual($user->id);
    expect($content['assigned_to_type'])->toEqual('User');
});

test('shows assignment info when checked out to location', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->assignedToLocation($location)->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['assigned_to_id'])->toEqual($location->id);
    expect($content['assigned_to_type'])->toEqual('Location');
});

test('assignment info is null when not checked out', function () {
    $asset = Asset::factory()->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['assigned_to_id'])->toBeNull();
    expect($content['assigned_to_type'])->toBeNull();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError())->toBeTrue();
});

test('custom fields is empty when model has no fieldset', function () {
    $asset = Asset::factory()->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content)->toHaveKey('custom_fields');
    expect($content['custom_fields'])->toBe([]);
});

test('custom fields surfaces cpu and ram from fieldset', function () {
    // Asset model has a fieldset with CPU + RAM fields.
    // The tool should emit them under custom_fields keyed by field name.
    $asset = Asset::factory()->hasMultipleCustomFields()->create();

    $cpuField = \App\Models\CustomField::where('name', 'CPU')->first();
    $ramField = \App\Models\CustomField::where('name', 'RAM')->first();
    $asset->{$cpuField->db_column_name()} = 'Apple M3 Max';
    $asset->{$ramField->db_column_name()} = '64';
    $asset->save();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['custom_fields'])->toHaveKey('CPU');
    expect($content['custom_fields']['CPU']['value'])->toBe('Apple M3 Max');
    expect($content['custom_fields']['CPU']['field'])->toBe($cpuField->db_column_name());

    expect($content['custom_fields'])->toHaveKey('RAM');
    expect($content['custom_fields']['RAM']['value'])->toBe('64');
});
