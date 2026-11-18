<?php

use App\Mcp\Tools\CreateDepreciationTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createDepreciations()->create());
});

function createDepreciationTool(array $args = []): ResponseFactory
{
    return (new CreateDepreciationTool)->handle(new Request($args));
}

test('creates depreciation', function () {
    $content = createDepreciationTool(['name' => 'Test Depreciation', 'months' => 36])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('depreciations', [
        'name' => 'Test Depreciation',
        'months' => 36,
    ]);
});

test('response includes id name and months', function () {
    $content = createDepreciationTool(['name' => 'Response Depreciation', 'months' => 24])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Depreciation');
    expect($content['months'])->toEqual(24);
});

test('returns error when name missing', function () {
    expect(createDepreciationTool(['months' => 36])->responses()->first()->isError())->toBeTrue();
});

test('returns error when months missing', function () {
    expect(createDepreciationTool(['name' => 'No Months'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createDepreciationTool(['name' => 'Blocked Depreciation', 'months' => 12])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('depreciations', ['name' => 'Blocked Depreciation']);
});
