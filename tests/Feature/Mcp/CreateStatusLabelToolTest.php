<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createStatusLabels()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('creates deployable status label', function () {
    $content = handle(['name' => 'Test Deployable', 'type' => 'deployable'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('status_labels', [
        'name' => 'Test Deployable',
        'deployable' => 1,
        'pending' => 0,
        'archived' => 0,
    ]);
});

test('creates pending status label', function () {
    $content = handle(['name' => 'Test Pending', 'type' => 'pending'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('status_labels', [
        'name' => 'Test Pending',
        'deployable' => 0,
        'pending' => 1,
        'archived' => 0,
    ]);
});

test('response includes id name and type', function () {
    $content = handle(['name' => 'Response Status Label', 'type' => 'deployable'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Status Label');
    expect($content['type'])->toEqual('deployable');
});

test('returns error when name missing', function () {
    expect(handle(['type' => 'deployable'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when type missing', function () {
    expect(handle(['name' => 'No Type Label'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle(['name' => 'Blocked Label', 'type' => 'deployable'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('status_labels', ['name' => 'Blocked Label']);
});
