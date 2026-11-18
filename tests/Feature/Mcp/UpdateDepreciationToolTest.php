<?php

use App\Mcp\Tools\UpdateDepreciationTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editDepreciations()->create());
});

function updateDepreciationTool(array $args = []): ResponseFactory
{
    return (new UpdateDepreciationTool)->handle(new Request($args));
}

test('updates depreciation by id', function () {
    $dep = Depreciation::factory()->create(['months' => 12]);

    $content = updateDepreciationTool([
        'id' => $dep->id,
        'months' => 24,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('depreciations', [
        'id' => $dep->id,
        'months' => 24,
    ]);
});

test('updates months', function () {
    $dep = Depreciation::factory()->create(['months' => 36]);

    $content = updateDepreciationTool([
        'id' => $dep->id,
        'months' => 48,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['months'])->toEqual(48);
});

test('renames via new name', function () {
    $dep = Depreciation::factory()->create();

    $content = updateDepreciationTool([
        'id' => $dep->id,
        'new_name' => 'Renamed Depreciation',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('Renamed Depreciation');
    $this->assertDatabaseHas('depreciations', [
        'id' => $dep->id,
        'name' => 'Renamed Depreciation',
    ]);
});

test('returns error when not found', function () {
    expect(updateDepreciationTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $dep = Depreciation::factory()->create();

    expect(updateDepreciationTool(['id' => $dep->id, 'months' => 99])->responses()->first()->isError())->toBeTrue();
});
