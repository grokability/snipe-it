<?php

use App\Mcp\Tools\DeleteComponentTool;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteComponents()->create());
});

function deleteComponentTool(array $args): ResponseFactory
{
    return (new DeleteComponentTool)->handle(new Request($args));
}

test('deletes component by id', function () {
    $component = Component::factory()->create();

    $content = deleteComponentTool(['id' => $component->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('components', ['id' => $component->id]);
});

test('deletes component by name', function () {
    $component = Component::factory()->create(['name' => 'Delete By Name Component']);

    $content = deleteComponentTool(['name' => 'Delete By Name Component'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('components', ['id' => $component->id]);
});

test('response includes name', function () {
    $component = Component::factory()->create(['name' => 'Named Component']);

    $content = deleteComponentTool(['id' => $component->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named Component');
});

test('returns error when not found', function () {
    expect(deleteComponentTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when component has checkouts', function () {
    $asset = Asset::factory()->create();
    $component = Component::factory()->checkedOutToAsset($asset)->create();

    $response = deleteComponentTool(['id' => $component->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('components', ['id' => $component->id]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $component = Component::factory()->create();

    expect(deleteComponentTool(['id' => $component->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('components', ['id' => $component->id]);
});
