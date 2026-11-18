<?php

use App\Mcp\Tools\CreateStatusLabelTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createStatusLabels()->create());
});

function createStatusLabelTool(array $args = []): ResponseFactory
{
    return (new CreateStatusLabelTool)->handle(new Request($args));
}

test('creates deployable status label', function () {
    $content = createStatusLabelTool(['name' => 'Test Deployable', 'type' => 'deployable'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('status_labels', [
        'name' => 'Test Deployable',
        'deployable' => 1,
        'pending' => 0,
        'archived' => 0,
    ]);
});

test('creates pending status label', function () {
    $content = createStatusLabelTool(['name' => 'Test Pending', 'type' => 'pending'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('status_labels', [
        'name' => 'Test Pending',
        'deployable' => 0,
        'pending' => 1,
        'archived' => 0,
    ]);
});

test('response includes id name and type', function () {
    $content = createStatusLabelTool(['name' => 'Response Status Label', 'type' => 'deployable'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Status Label');
    expect($content['type'])->toEqual('deployable');
});

test('returns error when name missing', function () {
    expect(createStatusLabelTool(['type' => 'deployable'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when type missing', function () {
    expect(createStatusLabelTool(['name' => 'No Type Label'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createStatusLabelTool(['name' => 'Blocked Label', 'type' => 'deployable'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('status_labels', ['name' => 'Blocked Label']);
});
