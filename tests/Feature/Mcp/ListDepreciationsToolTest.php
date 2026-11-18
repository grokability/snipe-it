<?php

use App\Mcp\Tools\ListDepreciationsTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewDepreciations()->create());
});

function listDepreciationsTool(array $args = []): ResponseFactory
{
    return (new ListDepreciationsTool)->handle(new Request($args));
}

test('returns depreciations with pagination', function () {
    Depreciation::factory()->count(3)->create();

    $content = listDepreciationsTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('depreciations');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each depreciation has key fields', function () {
    Depreciation::factory()->create();

    $content = listDepreciationsTool()->getStructuredContent();

    expect($content['depreciations'])->not->toBeEmpty();
    $first = $content['depreciations'][0];
    expect($first)->toHaveKey('id');
    expect($first)->toHaveKey('name');
    expect($first)->toHaveKey('months');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listDepreciationsTool()->responses()->first()->isError())->toBeTrue();
});
