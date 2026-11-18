<?php

use App\Mcp\Tools\ShowDepreciationTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewDepreciations()->create());
});

function showDepreciationTool(array $args = []): ResponseFactory
{
    return (new ShowDepreciationTool)->handle(new Request($args));
}

test('returns depreciation by id', function () {
    $dep = Depreciation::factory()->create();

    $content = showDepreciationTool(['id' => $dep->id])->getStructuredContent();

    expect($content['id'])->toEqual($dep->id);
    expect($content['name'])->toEqual($dep->name);
});

test('returns depreciation by name', function () {
    $dep = Depreciation::factory()->create();

    $content = showDepreciationTool(['name' => $dep->name])->getStructuredContent();

    expect($content['id'])->toEqual($dep->id);
    expect($content['name'])->toEqual($dep->name);
});

test('returns error when no identifier provided', function () {
    expect(showDepreciationTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showDepreciationTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $dep = Depreciation::factory()->create();

    expect(showDepreciationTool(['id' => $dep->id])->responses()->first()->isError())->toBeTrue();
});
