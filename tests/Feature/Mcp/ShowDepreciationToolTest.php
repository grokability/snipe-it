<?php

use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewDepreciations()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns depreciation by id', function () {
    $dep = Depreciation::factory()->create();

    $content = handle(['id' => $dep->id])->getStructuredContent();

    expect($content['id'])->toEqual($dep->id);
    expect($content['name'])->toEqual($dep->name);
});

test('returns depreciation by name', function () {
    $dep = Depreciation::factory()->create();

    $content = handle(['name' => $dep->name])->getStructuredContent();

    expect($content['id'])->toEqual($dep->id);
    expect($content['name'])->toEqual($dep->name);
});

test('returns error when no identifier provided', function () {
    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(handle(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $dep = Depreciation::factory()->create();

    expect(handle(['id' => $dep->id])->responses()->first()->isError())->toBeTrue();
});
