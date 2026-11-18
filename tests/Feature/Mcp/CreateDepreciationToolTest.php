<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createDepreciations()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('creates depreciation', function () {
    $content = handle(['name' => 'Test Depreciation', 'months' => 36])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('depreciations', [
        'name' => 'Test Depreciation',
        'months' => 36,
    ]);
});

test('response includes id name and months', function () {
    $content = handle(['name' => 'Response Depreciation', 'months' => 24])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Depreciation');
    expect($content['months'])->toEqual(24);
});

test('returns error when name missing', function () {
    expect(handle(['months' => 36])->responses()->first()->isError())->toBeTrue();
});

test('returns error when months missing', function () {
    expect(handle(['name' => 'No Months'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle(['name' => 'Blocked Depreciation', 'months' => 12])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('depreciations', ['name' => 'Blocked Depreciation']);
});
