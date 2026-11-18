<?php

use App\Mcp\Tools\DeleteDepreciationTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteDepreciations()->create());
});

function deleteDepreciationTool(array $args = []): ResponseFactory
{
    return (new DeleteDepreciationTool)->handle(new Request($args));
}

test('deletes depreciation by id', function () {
    $dep = Depreciation::factory()->create();

    $content = deleteDepreciationTool(['id' => $dep->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseMissing('depreciations', ['id' => $dep->id]);
});

test('deletes depreciation by name', function () {
    $dep = Depreciation::factory()->create();

    $content = deleteDepreciationTool(['name' => $dep->name])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseMissing('depreciations', ['id' => $dep->id]);
});

test('returns error when not found', function () {
    expect(deleteDepreciationTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $dep = Depreciation::factory()->create();

    expect(deleteDepreciationTool(['id' => $dep->id])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('depreciations', ['id' => $dep->id]);
});
