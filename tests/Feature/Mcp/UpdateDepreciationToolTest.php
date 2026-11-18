<?php

use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editDepreciations()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('updates depreciation by id', function () {
    $dep = Depreciation::factory()->create(['months' => 12]);

    $content = handle([
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

    $content = handle([
        'id' => $dep->id,
        'months' => 48,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['months'])->toEqual(48);
});

test('renames via new name', function () {
    $dep = Depreciation::factory()->create();

    $content = handle([
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
    expect(handle(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $dep = Depreciation::factory()->create();

    expect(handle(['id' => $dep->id, 'months' => 99])->responses()->first()->isError())->toBeTrue();
});
