<?php

use App\Mcp\Tools\UpdateStatusLabelTool;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editStatusLabels()->create());
});

function updateStatusLabelTool(array $args = []): ResponseFactory
{
    return (new UpdateStatusLabelTool)->handle(new Request($args));
}

test('updates status label by id', function () {
    $label = Statuslabel::factory()->create(['name' => 'Original Status Label']);

    $content = updateStatusLabelTool([
        'id' => $label->id,
        'new_name' => 'Updated Status Label',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('status_labels', ['id' => $label->id, 'name' => 'Updated Status Label']);
});

test('renames via new name', function () {
    $label = Statuslabel::factory()->create(['name' => 'Old Status Label Name']);

    $content = updateStatusLabelTool([
        'name' => 'Old Status Label Name',
        'new_name' => 'New Status Label Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('New Status Label Name');
});

test('updates type', function () {
    $label = Statuslabel::factory()->rtd()->create(['name' => 'Type Change Label']);

    $content = updateStatusLabelTool([
        'id' => $label->id,
        'type' => 'pending',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['type'])->toEqual('pending');
    $this->assertDatabaseHas('status_labels', [
        'id' => $label->id,
        'pending' => 1,
        'deployable' => 0,
        'archived' => 0,
    ]);
});

test('returns error when not found', function () {
    expect(updateStatusLabelTool(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $label = Statuslabel::factory()->create(['name' => 'Unchanged Status Label']);
    $this->actingAs(User::factory()->create());

    expect(updateStatusLabelTool([
        'id' => $label->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('status_labels', ['id' => $label->id, 'name' => 'Unchanged Status Label']);
});
